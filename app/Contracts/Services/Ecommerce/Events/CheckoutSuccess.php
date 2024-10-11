<?php

namespace App\Contracts\Services\Ecommerce\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

/**
 * Event dispatched by {@see \App\Services\Ecommerce\Stripe\StripeService::processCheckoutSuccess}
 * after the user is redirected back from payment processor successfully
 * Should only be used for temporary measures, source of truth should be the
 */
class CheckoutSuccess
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param Request $request HTTP Request object of the successful redirection from checkout back to the site
     *
     * @return void
     */
    public function __construct(public Request $request)
    {
        //
    }
}
