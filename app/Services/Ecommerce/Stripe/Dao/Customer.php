<?php

namespace App\Services\Ecommerce\Stripe\Dao;

use App\Contracts\Services\Ecommerce\Dao\Customer as EcommerceCustomer;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Services\Ecommerce\Stripe\Concerns\QueriesApi;
use Stripe\Customer as StripeCustomer;

/**
 * A Stripe implementation of our e-commerce customer abstraction
 */
class Customer extends EcommerceCustomer
{
    use QueriesApi;

    /**
     * Given a {@see \Stripe\Customer}, converts it to our customer abstraction
     *
     * @param StripeCustomer $stripeCustomer is a Stripe SDK customer to convert
     *
     * @return Customer created from a Stripe SDK customer
     *
     * @throws InvalidDaoException if an attempt is made to create a customer without all the proper data
     * @throws InvalidInputException if the $stripeCustomer is not a {@see \Stripe\Customer}
     */
    protected static function createFromExternal(mixed $stripeCustomer): Customer
    {
        if (!($stripeCustomer instanceof StripeCustomer)) {
            throw new InvalidInputException(
                'The external entity must be a ' . StripeCustomer::class
                    . '. A ' . $stripeCustomer::class . ' was passed instead.'
            );
        }

        $customerData = [
            'id' => $stripeCustomer->id,
            'name' => $stripeCustomer->name,
            'phone' => $stripeCustomer->phone,
            'email' => $stripeCustomer->email,
        ];

        return new Customer($customerData);
    }
}
