<?php

namespace App\Services\Ecommerce\Stripe\Dao;

use App\Contracts\Services\Ecommerce\Dao\Product as EcommerceProduct;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use App\Services\Ecommerce\Stripe\Concerns\QueriesApi;
use Exception;
use Stripe\Product as StripeProduct;

/**
 * A Stripe implementation of our e-commerce product abstraction
 */
class Product extends EcommerceProduct
{
    use QueriesApi;

    /**
     * Given a {@see \Stripe\Product}, converts it to our product abstraction
     *
     * @param StripeProduct $stripeProduct is a Stripe SDK product to convert to a product DAO
     *
     * @return Product is a generalized contracted object created using the external representation as the reference
     *
     * @throws InvalidDaoException if an attempt is made to create a product without all the required data
     * @throws InvalidInputException if the $stripeProduct is not a {@see \Stripe\Product}
     * @throws RetrievalException when there is any failure in retrieving a product price
     */
    protected static function createFromExternal(mixed $stripeProduct): Product
    {
        if (!($stripeProduct instanceof StripeProduct)) {
            throw new InvalidInputException(
                'The external entity must be a ' . StripeProduct::class
                    . '. A ' . $stripeProduct::class . ' was passed instead.'
            );
        }

        try {
            $price = Price::find($stripeProduct->default_price);
        } catch (Exception $exception) {
            throw new RetrievalException(message: "Enable to retrieve price.", previous: $exception);
        }

        $planData = [
            'id' => $stripeProduct->id,
            'name' => $stripeProduct->name,
            'price' => $price->get('amount'),
        ];

        return new Product($planData);
    }
}
