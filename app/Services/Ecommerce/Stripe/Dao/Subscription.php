<?php

namespace App\Services\Ecommerce\Stripe\Dao;

use App\Contracts\Services\Ecommerce\Dao\Customer as EcommerceCustomer;
use App\Contracts\Services\Ecommerce\Dao\Subscription as EcommerceSubscription;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use App\Services\Ecommerce\Stripe\Concerns\QueriesApi;
use Exception;
use Stripe\Subscription as StripeSubscription;

/**
 * A Stripe implementation of our e-commerce subscription abstraction
 */
class Subscription extends EcommerceSubscription
{
    use QueriesApi;

    /**
     * Given a {@see \Stripe\Subscription}, converts it to our subscription abstraction
     *
     * @param StripeSubscription $stripeSubscription is a Stripe SDK customer to convert
     *
     * @return Subscription created from a Stripe SDK subscription
     *
     * @throws InvalidDaoException if an attempt is made to create a subscription without all the proper data
     * @throws InvalidInputException if the $stripeSubscription is not a {@see \Stripe\Subscription}
     */
    protected static function createFromExternal(mixed $stripeSubscription): Subscription
    {
        if (!($stripeSubscription instanceof StripeSubscription)) {
            throw new InvalidInputException(
                'The external entity must be a ' . StripeSubscription::class
                . '. A ' . $stripeSubscription::class . ' was passed instead.'
            );
        }

        return new Subscription([
            'customer-id' => $stripeSubscription->customer,
            'subscription-id' => $stripeSubscription->id,
            'status' => strtolower($stripeSubscription->status),
            'expires-on' => $stripeSubscription->current_period_end,
        ]);
    }

    /**
     * Fetch subscription for provided customer
     *
     * @param Customer $customer for which, we will fetch Stripe subscription
     *
     * @return ?Subscription an active or trail subscription for the given customer
     *
     * @throws RetrievalException
     */
    public static function getActiveSubscriptionFor(EcommerceCustomer $customer): ?Subscription
    {
        if (!$customer->get('id')) {
            return null;
        }

        try {
            $subscriptions = Subscription::fetch(
                ['customer' => $customer->get('id'), 'status' => StripeSubscription::STATUS_ACTIVE]
            );

            if (!$subscriptions) {
                $subscriptions = Subscription::fetch(
                    ['customer' => $customer->get('id'), 'status' => StripeSubscription::STATUS_TRIALING]
                );
            }
        } catch (Exception $exception) {
            throw new RetrievalException(message: "Enable to retrieve price.", previous: $exception);
        }

        return $subscriptions[0] ?? null;
    }
}
