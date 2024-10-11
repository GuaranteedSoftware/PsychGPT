<?php

namespace App\Services\Ecommerce\Stripe;

use App\Contracts\Services\Ecommerce\Concerns\ProcessesCheckout;
use App\Contracts\Services\Ecommerce\Dao\Customer as EcommerceCustomer;
use App\Contracts\Services\Ecommerce\EcommerceService;
use App\Contracts\Services\Ecommerce\Events\CheckoutSuccess;
use App\Contracts\Services\Ecommerce\Events\CustomerCreated;
use App\Exceptions\Services\Ecommerce\InvalidCartException;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use App\Models\User;
use App\Services\Ecommerce\Stripe\Dao\Customer;
use App\Services\Ecommerce\Stripe\Dao\Plan;
use App\Services\Ecommerce\Stripe\Dao\Price;
use App\Services\Ecommerce\Stripe\Dao\Product;
use App\Services\Ecommerce\Stripe\Dao\Subscription;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe as StripeApi;
use Stripe\WebhookSignature as StripeWebhookSignature;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wrapper class to the Stripe API used to implement common
 * inventory, customer, order, and payment management
 */
class StripeService implements EcommerceService
{
    use ProcessesCheckout;

    /**
     * The base namespace for the Stripe SDK classes used by this service
     *
     * @var string
     */
    public const SDK_NAMESPACE = '\\Stripe\\';

    /**
     * Connects to the Stripe API
     */
    public function __construct()
    {
        StripeApi::setApiKey(config('services.stripe.api_secret'));
    }

    /**
     * Retrieves the unique ID for this e-commerce service.
     *
     * @return string A unique identifying ID that must match the DB key used for this service
     */
    public static function id(): string
    {
        return 'stripe';
    }

    /**
     * Gets all the products which are available for purchase on the front page
     *
     * @param array $filters The search criteria and formatting hints used to filter products.
     *                       Details can be read at {@link https://stripe.com/docs/api/products/search}
     *                       The possible parameters are:
     *                          query (required) - uses {@link https://stripe.com/docs/search#search-query-language}
     *                          limit - the maximum number of objects to return
     *                          page - A cursor for pagination across multiple pages of results
     *
     * @return Product[] An associative array of product that are populated with their meta-data that
     *                   are to be displayed on the plan selection page. The keys to the array are the
     *                   normalized product names that can also serve as CSS classes.  Products will
     *                   be sorted in the default order that they are to be displayed.
     *
     * @throws RetrievalException when there is any failure in retrieving the products
     */
    public static function getFrontPageProducts(
        array $filters = ['query' => "active:'true' AND metadata['display-on-front-page']:'yes'"]
    ): array {
        try {
            return Product::fetch($filters);
        } catch (Exception $exception) {
            throw new RetrievalException(message: "Enable to retrieve product.", previous: $exception);
        }
    }

    /**
     * Gets all the plans which are available for purchase on the front page
     *
     * @param array $filters The search criteria and formatting hints used to filter products.
     *                       Details can be read at {@link https://stripe.com/docs/api/products/search}
     *                       The possible parameters are:
     *                          query (required) - uses {@link https://stripe.com/docs/search#search-query-language}
     *                          limit - the maximum number of objects to return
     *                          page - A cursor for pagination across multiple pages of results
     *
     * @return Plan[] An associative array of plans that are populated with their meta-data that
     *                are to be displayed on the plan selection page. The keys to the array are the
     *                normalized plan names that can also serve as CSS classes.  Plans will be sorted
     *                in the default order that they are to be displayed.
     *
     * @throws RetrievalException when there is any failure in retrieving the plans
     */
    static public function getFrontPagePlans(array $filters = []): array
    {
        $signupType = Session::get('signup_type', '');

        try {
            return Plan::fetch(
                $filters + ['query' => "active:'true' AND metadata['display-on-plans-page$signupType']:'yes'"]
            );
        } catch (Exception $exception) {
            throw new RetrievalException(message: "Unable to retrieve plan.", previous: $exception);
        }
    }

    /**
     * Process the checkout session from stripe
     *
     * @param array $cart all cart data that is necessary to process the order
     *
     * @return Redirector|RedirectResponse the redirection after Stripe's checkout processing
     *
     * @throws ApiErrorException if the {@see \Stripe\ApiRequestor::request} fails
     */
    public static function checkout(array $cart): Redirector|RedirectResponse
    {
        $stripeSession = StripeSession::create($cart);

        return redirect($stripeSession->url);
    }

    /**
     * Process webhook from the Stripe API
     *
     * @param Request $request the request sent by the webhook
     *
     * @return HttpResponse that is sent to the service after processing the webhook
     */
    public static function processWebhook(Request $request): HttpResponse
    {
        try {
            self::verifyWebhook($request);

            $data = $request->data['object'];

            switch ($request->type) {
                case "customer.created":
                    CustomerCreated::dispatch(
                        new Customer($data),
                        new StripeService()
                    );
                    break;
                // TODO: implement all other expected webhooks
                case "invoice.payment_succeeded":
                case "invoice.payment_failed":
                case "customer.subscription.created":
                case "customer.subscription.updated":
                case "invoice.created":
                case "invoice.finalized":
            }

        } catch (Exception $exception) {
            return response($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response(''); # 200 OK
    }

    /**
     * Given a request, converts it to a cart array compatible with the e-commerce service
     *
     * @param Request $request containing the required cart details in order to create an order
     *
     * @return array a key/value pairing of all cart data.  This array can be converted to a JSON object
     *
     * @throws InvalidCartException if the request provided does not have all the required cart data
     * @throws RetrievalException when there is any failure in retrieving the corresponding plan product from Stripe
     */
    public static function getCartFromRequest(Request $request): array
    {
        if (!($stripePriceId = $request->input('cart-product-id', config('service.stripe.monthly_cart_product_id')))) {
            throw new InvalidCartException('Missing the Stripe price ID');
        }

        try {
            /** @var Price $price */
            $price = Price::find($stripePriceId);
        } catch (Exception $exception) {
            throw new RetrievalException(message: "Enable to retrieve plan.", previous: $exception);
        }

        /** @var User $user */
        $user = $request->user();
        $cart = [
            'mode' => 'subscription',
            'line_items' => [
                [
                    'price' => $price->get('id'),
                    'quantity' => 1,
                ],
            ],
            'phone_number_collection' => ['enabled' => true],
            'success_url' => route('ecommerce.order.success'),
            'cancel_url' => route('ecommerce.order.failure'),
        ];

        if ($numberOfTrialDays = (int) $price->get('trial-period-days')) {
            $cart['subscription_data'] = [
                'trial_period_days' => $numberOfTrialDays,
            ];
        }

        if ($user->ecommerceAccount?->service_name == StripeService::id()
            && $user->ecommerceAccount?->remote_id) {
            $cart['customer'] = $user->ecommerceAccount->remote_id;
        } else {
            $cart['customer_email'] = $user->email;
        }

        return $cart;
    }

    /**
     * Retrieves an active subscription for a customer
     *
     * @param Customer $customer for which, we will check for a subscription
     *
     * @return ?Subscription active subscription if it exists, otherwise null
     *
     * @throws RetrievalException if there is a problem with reading subscription information from Stripe
     */
    public static function getActiveSubscriptionFor(EcommerceCustomer $customer): ?Subscription
    {
        return Subscription::getActiveSubscriptionFor($customer);
    }

    /**
     * Verifies that a webhook request has come from Stripe
     *
     * @param Request $request sent to the app to be verified as a trusted webhook from the Stripe
     *
     * @return void
     *
     * @throws SignatureVerificationException if the signature verification fails for any reason
     */
    private static function verifyWebhook(Request $request): void
    {
        StripeWebhookSignature::verifyHeader(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook.secret'),
            config('services.stripe.webhook.tolerance'),
        );
    }

    /**
     * Factory method for making of customer of the Stripe DAO type.  This does not automatically
     * persist the made customer
     *
     * @param array $data the data associated with the customer used to create it
     *
     * @return Customer a Stripe customer DAO with the provided email address
     *
     * @throws InvalidDaoException if the data provided to create the customer is invalid
     */
    public static function makeCustomer(array $data): Customer
    {
        return new Customer($data);
    }

    /**
     * Creates a billing portal session for the given customer.
     *
     * @param User $user the customer who is requesting to manage their subscription and billing details
     *
     * @return string The URL to the authenticated billing portal session
     *
     * @throws ApiErrorException If there is an error while creating the billing portal session.
     */
    public static function getBillingPortalURL(User $user): string
    {
        /** @var \App\Models\EcommerceAccount $ecommerceAccount */
        $ecommerceAccount = $user->ecommerceAccount;
        $billingPortalSession = \Stripe\BillingPortal\Session::create([
            'customer' => $ecommerceAccount->remote_id,
            'return_url' => route('account.subscriptions'),
        ]);

        return $billingPortalSession->url;
    }
}
