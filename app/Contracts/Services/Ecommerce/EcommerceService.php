<?php

namespace App\Contracts\Services\Ecommerce;

use App\Contracts\Services\Ecommerce\Dao\Customer;
use App\Contracts\Services\Ecommerce\Dao\Plan;
use App\Contracts\Services\Ecommerce\Dao\Product;
use App\Contracts\Services\Ecommerce\Dao\Subscription;
use App\Exceptions\Services\Ecommerce\InvalidCartException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Redirector;

/**
 * An abstraction on all inventory, customer, order, and payment management.
 * Its purpose is to allow for easy change of e-commerce processing if such
 * a need arises in the future.  This will be created as a singleton.
 */
interface EcommerceService
{
    /**
     * Retrieves the unique ID for this e-commerce service.
     *
     * @return string A unique identifying ID that must match the DB key used for this service
     */
    public static function id(): string;

    /**
     * Gets all the products which are available for purchase on the front page
     *
     * @param array $filters An associative array of filter hints for the products.
     *
     * @return Product[] An associative array of product that are populated with their meta-data that
     *                   are to be displayed on the plan selection page. The keys to the array are the
     *                   normalized product names that can also serve as CSS classes.  Products will
     *                   be sorted in the default order that they are to be displayed.
     *
     * @throws RetrievalException when there is any failure in retrieving the products
     */
    public static function getFrontPageProducts(array $filters = []): array;

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
     * @throws RetrievalException when there is any failure in retrieving the plans
     */
    public static function getFrontPagePlans(array $filters = []): array;

    /**
     * Processes an order on the e-commerce service
     *
     * @param array $cart all cart data that is necessary to process the order
     *
     * @return Redirector|RedirectResponse the redirection after checkout processing is completed
     */
    public static function checkout(array $cart): Redirector|RedirectResponse;

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
    public static function processCheckoutSuccess(Request $request): Redirector|RedirectResponse;

    /**
     * Execution of any post-processing routines after checkout failure
     *
     * @param Request $request the request sent by the e-commerce service which
     *                         may contain post-processing information
     *
     * @return Redirector|RedirectResponse the redirection to an appropriate failed payment page
     */
    public static function processCheckoutFailure(Request $request): Redirector|RedirectResponse;

    /**
     * Processes any webhook from the e-commerce service
     *
     * @param Request $request the request sent by the webhook
     *
     * @return HttpResponse that is sent to the service after processing the webhook
     */
    public static function processWebhook(Request $request): HttpResponse;

    /**
     * Given a standard ClientGenie order request, converts it to a cart array compatible with the e-commerce service
     *
     * @param Request $request  containing the required cart details in order to create an ecommerce order
     *
     * @return array a key/value pairing of all cart data.  This array is typically able to be converted
     *               to a JSON object that is ready mode for the e-commerce service. However, in some cases,
     *               where the cart is in a form not compatible with an array or JSON, the convention is
     *               return an array with a single key, 'cart', containing the expected cart entity.
     *
     * @throws InvalidCartException if the request provided does not have all the required cart data
     */
    public static function getCartFromRequest(Request $request): array;

    /**
     * Retrieves an active subscription for a customer
     *
     * @param Customer $customer for which, we will check for a subscription
     *
     * @return ?Subscription active subscription if it exists, otherwise null
     */
    public static function getActiveSubscriptionFor(Customer $customer): ?Subscription;

    /**
     * Factory method for making of customer of this e-commerce type.  This does not automatically
     * persist the made customer
     *
     * @param array $data the data associated with the customer used to create it
     *
     * @return Customer in the form of this e-commerce service
     */
    public static function makeCustomer(array $data): Customer;

    /**
     * Retrieves the billing portal URL for a given owner
     *
     * @param User $user the owner for whom to retrieve the billing portal URL
     *
     * @return string the URL of the billing portal for the given owner
     */
    public static function getBillingPortalURL(User $user): string;
}
