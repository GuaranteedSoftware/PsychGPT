<?php

namespace App\Services\Ecommerce\Square;

use App\Contracts\Services\Ecommerce\Concerns\ProcessesCheckout;
use App\Contracts\Services\Ecommerce\Dao\Customer as EcommerceCustomer;
use App\Contracts\Services\Ecommerce\EcommerceService;
use App\Contracts\Services\Ecommerce\Events\CustomerCreated;
use App\Exceptions\Services\Ecommerce\InvalidCartException;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\InvalidStateException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use App\Models\User;
use App\Services\Ecommerce\Square\Dao\Customer;
use App\Services\Ecommerce\Square\Dao\Plan;
use App\Services\Ecommerce\Square\Dao\Product;
use App\Services\Ecommerce\Square\Dao\Subscription;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Square\Authentication\BearerAuthCredentialsBuilder;
use Square\Environment;
use Square\Models\Address;
use Square\Models\CheckoutOptions;
use Square\Models\CreatePaymentLinkRequest;
use Square\Models\CreatePaymentLinkResponse;
use Square\Models\Money;
use Square\Models\PrePopulatedData;
use Square\Models\QuickPay;
use Square\SquareClient;
use Square\SquareClientBuilder;
use Square\Utils\WebhooksHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wrapper class to the Square API used to implement common
 * inventory, customer, order, and payment management
 */
class SquareService implements EcommerceService
{
    use ProcessesCheckout {
        ProcessesCheckout::processCheckoutSuccess as defaultProcessCheckoutSuccess;
    }

    /**
     * @var SquareClient used for querying the Square API
     */
    private static SquareClient $client;

    /**
     * Gets the client used for accessing the Square API
     *
     * @return SquareClient used for access the Square API
     */
    public static function client(): SquareClient
    {
        return SquareService::$client ?? SquareService::$client = SquareClientBuilder::init()
            ->bearerAuthCredentials(
                BearerAuthCredentialsBuilder::init(
                    config('services.square.access_token'),
                )
            )
            ->environment(App::isProduction() ? Environment::PRODUCTION : Environment::SANDBOX)
            ->build();
    }

    /**
     * Retrieves the unique ID for this e-commerce service.
     *
     * @return string A unique identifying ID that must match the DB key used for this service
     */
    public static function id(): string
    {
        return 'square';
    }

    /**
     * Gets all the products which are available for purchase on the front page
     *
     * @param array $filters An associative array of filter hints for the products.
     *
     * @return Product[] An associative array of product that are populated with their meta-data that
     *                   are to be displayed on the plan selection page. The keys to the array are the
     *                   normalized product names that can also serve as CSS classes.  Products will
     *                   be sorted in the default order that they are to be displayed.
     */
    public static function getFrontPageProducts(array $filters = []): array
    {
        return [];
    }

    /**
     * Gets all the plans which are available for purchase on the front page
     *
     * @param array $filters An associative array of filter hints for the plans.
     *
     * @return Plan[] An associative array of plans that are populated with their meta-data that
     *                are to be displayed on the plan selection page. The keys to the array are the
     *                normalized plan names that can also serve as CSS classes.  Plans will be sorted
     *                in the default order that they are to be displayed.
     *
     */
    public static function getFrontPagePlans(array $filters = []): array
    {
        return [];
    }

    /**
     * Processes an order on the e-commerce service
     *
     * @param array $cart all cart data that is necessary to process the order
     *
     * @return Redirector|RedirectResponse the redirection after checkout processing is completed
     */
    public static function checkout(array $cart): Redirector|RedirectResponse
    {
        $apiResponse = self::client()->getCheckoutApi()->createPaymentLink($cart['cart']);

        /** @var CreatePaymentLinkResponse $response */
        if ($apiResponse->isSuccess()) {
            $response = $apiResponse->getResult();
        } else {
            $response = $apiResponse->getErrors();
        }

        return redirect($response->getPaymentLink()->getUrl());
    }

    /**
     * Execution of any post-processing routines after checkout success
     * This could include associating an ecommerce service ID for a customer
     * or session to the current owner
     *
     * @param Request $request the request sent by the e-commerce service which
     *                         may contain post-processing information
     *
     * @return Redirector|RedirectResponse the redirection to an appropriate post payment page
     */
    public static function processCheckoutSuccess(Request $request): RedirectResponse|Redirector
    {
        if (!$request->input('orderId')) {
            return self::processCheckoutFailure($request);
        }

        return self::defaultProcessCheckoutSuccess($request);
    }

    /**
     * Process webhook from the Square API
     *
     * @param Request $request the request sent by the webhook
     *
     * @return HttpResponse that is sent to the service after processing the webhook
     */
    public static function processWebhook(Request $request): HttpResponse
    {
        try {
            if (!self::verifyWebhook($request)) {
                return response('Unverified Request', Response::HTTP_FORBIDDEN);
            }

            $data = $request->data['object'];

            switch ($request->type) {
                case "customer.created":
                    CustomerCreated::dispatch(
                        self::makeCustomer(
                            [
                                'id' => $data['customer']['id'],
                                'email' => $data['customer']['email_address'],
                            ]
                        ),
                        new SquareService()
                    );
                    break;
                // TODO: implement all other expected webhooks
                case "invoice.payment_made":
                case "invoice.scheduled_charge_failed":
                case "subscription.created":
                case "subscription.updated":
                case "invoice.created":
                case "invoice.published":
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
     * @throws RetrievalException when there is any failure in retrieving the corresponding plan product from Square
     * @throws InvalidInputException if the Square {@see Plan} data does not match the DAO definition
     * @throws InvalidStateException if the Square plan that is found does not have all the required info to
     *                               make a local {@see Plan}
     */
    public static function getCartFromRequest(Request $request): array
    {
        $subscriptionPlanCatalogId = $request->input('cart-product-id', config('service.square.monthly_cart_product_id'));

        $monthlyPlan = Plan::find($subscriptionPlanCatalogId);

        $monthlyPrice = new Money();
        $monthlyPrice->setAmount($monthlyPlan->get('price'));
        $monthlyPrice->setCurrency('USD');

        $quickPayForMonthlyPlan = new QuickPay(
            $monthlyPlan->get('name'),
            $monthlyPrice,
            $monthlyPlan->get('location_id')
        );

        $checkoutOptions = new CheckoutOptions();
        $checkoutOptions->setSubscriptionPlanId($subscriptionPlanCatalogId);
        $checkoutOptions->setRedirectUrl(route('ecommerce.order.success'));

        $prePopulateData = new PrePopulatedData();
        $buyerAddress = new Address();
        $name = trim($request->user()->name);

        $buyerAddress->setFirstName(explode(' ', $name)[0] ?? '');
        $buyerAddress->setLastName(explode(' ', $name, 2)[1] ?? '');

        $prePopulateData->setBuyerEmail($request->user()->email);
        $prePopulateData->setBuyerAddress($buyerAddress);

        $cart = new CreatePaymentLinkRequest();
        $cart->setQuickPay($quickPayForMonthlyPlan);
        $cart->setIdempotencyKey(session()->getId());
        $cart->setCheckoutOptions($checkoutOptions);
        $cart->setPrePopulatedData($prePopulateData);

        return ['cart' => $cart];
    }

    /**
     * Retrieves an active subscription for a customer
     *
     * @param Customer $customer for which, we will check for a subscription
     *
     * @return ?Subscription active subscription if it exists, otherwise null
     *
     * @throws RetrievalException if there is a problem with reading subscription information from Square
     */
    public static function getActiveSubscriptionFor(EcommerceCustomer $customer): ?Subscription
    {
        return Subscription::getActiveSubscriptionFor($customer);
    }

    /**
     * Factory method for making of customer of the Square DAO type.  This does not automatically
     * persist the made customer
     *
     * @param array $data the data associated with the customer used to create it
     *
     * @return Customer a Square customer DAO containing the information from the passed data
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
     * @throws RetrievalException will be thrown until this feature is implemented
     */
    public static function getBillingPortalURL(User $user): string
    {
        throw new RetrievalException('Customer Portal URL is not supported for Square by this app');
    }

    /**
     * Verifies that a webhook request came from Square
     *
     * @param Request $request sent to the app to be verified as a trusted webhook from the Square
     *
     * @return bool true if the webhook is from Square, otherwise false
     *
     * @throws Exception If the signature key or notification URL is null or empty.
     */
    private static function verifyWebhook(Request $request): bool
    {
        return WebhooksHelper::isValidWebhookEventSignature(
            $request->getContent(),
            $request->header('X-Square-HmacSha256-Signature'),
            config('services.square.webhook_signature_key'),
            $request->url()
        );
    }
}
