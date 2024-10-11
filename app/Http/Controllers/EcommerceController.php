<?php

namespace App\Http\Controllers;

use App\Exceptions\Services\Ecommerce\InvalidCartException;
use App\Helpers\PsychGPT;
use Exception;
use Illuminate\Http\Request;
use App\Contracts\Services\Ecommerce\EcommerceService;
use Illuminate\Routing\Redirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;

/**
 * Class EcommerceController handles checkout flow and webhooks from an
 * e-commerce service
 *
 * @package App\Http\Controllers
 */
class EcommerceController extends Controller
{
    /**
     * Resolves dependency injected objects and sets them as class instance variables
     *
     * @param Request $request sent by the e-commerce service
     * @param EcommerceService $ecommerceService 3rd-party e-commerce service used for managing customers and orders
     */
    final public function __construct(
        protected Request $request,
        protected EcommerceService $ecommerceService
    ) {
    }

    /**
     * Collects order information and processes it through an e-commerce service
     *
     * @return Redirector|RedirectResponse a redirection to the 3rd-party e-commerce checkout page
     *
     * @throws InvalidCartException if the request does not contain all required cart data
     */
    public function processOrder(): Redirector|RedirectResponse
    {
        return $this->ecommerceService::checkout(
            $this->ecommerceService::getCartFromRequest($this->request)
        );
    }

    /**
     * Displays the dashboard with an order success message to the current authenticated owner
     * after and order has completed successfully.  Any post-success functionality is performed
     * here prior to forwarding
     *
     * @return RedirectResponse to the logged-in owner's dashboard with an order success message
     */
    public function orderSuccess(): RedirectResponse
    {
        $this->ecommerceService::processCheckoutSuccess($this->request);

        return redirect()->route('home')->with('flashMessage', __('Transaction completed successfully.'));
    }

    /**
     * Endpoint used for returning a owner to the plan selection page with an error message
     * upon exiting the e-commerce service's order flow without completing the order
     *
     * @return RedirectResponse to the sign-up select plan page with an order error message for the logged-in owner
     */
    public function orderFailure(): RedirectResponse
    {
        $this->ecommerceService::processCheckoutFailure($this->request);

        return redirect()->route('register')
            ->withErrors(['payment' => __('Unable to process order. Please try again.')]);
    }

    /**
     * Public end-point for processing webhook
     *
     * @return HttpResponse that is sent to the ecommerce service after processing the webhook
     */
    public function processWebhook(): HttpResponse
    {
        return $this->ecommerceService::processWebhook($this->request);
    }

    /**
     * Redirects the owner to the billing portal
     *
     * @param Request $request The request object sent by the client
     *
     * @return RedirectResponse Returns a redirect response to the billing portal
     */
    public function redirectToBillingPortal(Request $request): RedirectResponse
    {
        try {
            $billingPortalURL = PsychGPT::ecommerceService()->getBillingPortalURL($request->user());
            return redirect($billingPortalURL);
        } catch (Exception $e) {
            logger()->error($e);
            //@todo bugsnag
        }
        return redirect()->back()->with('error', 'Unable to redirect to Billing portal');
    }
}
