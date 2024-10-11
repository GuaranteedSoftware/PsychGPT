<?php

namespace App\Listeners;

use App\Contracts\Services\Ecommerce\Events\CheckoutSuccess;
use App\Http\Middleware\HasActivePlan;
use Illuminate\Support\Facades\Cache;

/**
 * Handler for {@see CheckoutSuccess} event, executed when the customer is successfully redirected back
 * from the payment processor
 */
class RegisterTemporaryPlan
{
    /**
     * Registers the current user as having a plan for 5 minutes to improve user experience
     * while we wait for the webhook to confirm the subscription
     *
     * @param  CheckoutSuccess $event instance containing the checkout success request
     *
     * @return void
     */
    public function handle(CheckoutSuccess $event): void
    {
        Cache::set(
            HasActivePlan::getCacheKey($event->request->user()),
            true,
            now()->addMinutes(5)
        );
    }
}
