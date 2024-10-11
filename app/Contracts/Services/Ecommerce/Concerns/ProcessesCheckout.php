<?php

namespace App\Contracts\Services\Ecommerce\Concerns;

use App\Contracts\Services\Ecommerce\Events\CheckoutSuccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

/**
 * Default post checkout processing
 */
trait ProcessesCheckout
{
    /**
     * Execution of any post-processing routines after checkout success
     *
     * The route can be overridden via 'services.ecommerce.checkout.success.route' defaulting to 'home'
     * The success message can be overridden via 'services.ecommerce.checkout.success.message'
     *
     * @param Request $request the request sent by the e-commerce service to confirm the checkout's success
     *
     * @return Redirector|RedirectResponse the redirection to an appropriate post payment page
     */
    public static function processCheckoutSuccess(Request $request): Redirector|RedirectResponse
    {
        CheckoutSuccess::dispatch($request);
        $route = config('services.ecommerce.checkout.success.route', 'home');
        $message = config('services.ecommerce.checkout.success.message', __('Order processed successfully.'));

        return redirect()->route($route)->with('flashMessage', $message);
    }

    /**
     * Execution of any post-processing routines after checkout failure
     *
     * The route can be overridden via 'services.ecommerce.checkout.failure.route' defaulting to 'register'
     * The success message can be overridden via 'services.ecommerce.checkout.failure.message'
     *
     * @param Request $request the request sent by the e-commerce service to confirm the checkout's failure
     *
     * @return Redirector|RedirectResponse the redirection to an appropriate failed payment page
     */
    public static function processCheckoutFailure(Request $request): Redirector|RedirectResponse
    {
        $route = config('services.ecommerce.checkout.failure.route', 'register');
        $message = config('services.ecommerce.checkout.failure.message', __('Unable to process order. Please try again.'));

        return redirect()->route($route)->withErrors(['payment' => $message]);
    }
}
