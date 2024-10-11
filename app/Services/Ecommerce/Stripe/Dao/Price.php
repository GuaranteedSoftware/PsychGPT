<?php

namespace App\Services\Ecommerce\Stripe\Dao;

use App\Contracts\Services\Ecommerce\Dao\BaseDao;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Services\Ecommerce\Stripe\Concerns\QueriesApi;
use Stripe\Price as StripePrice;

/**
 * A helper DAO for the Stripe Product DAO to retrieve its price component
 */
class Price extends BaseDao
{
    use QueriesApi;

    /**
     * A set of rules compliant with the {@see Validator} facade applied to {@see BaseDao::data}
     */
    protected const DATA_VALIDATION_RULES = [
        'id' => 'nullable|string',
        'interval' => 'nullable|string',
        'amount' => 'required|numeric',
        'trial-period-days' => 'nullable|numeric',
        'currency' => 'required|string',
        'nickname' => 'nullable|string',
    ];

    /**
     * Given a {@see \Stripe\Price}, converts it to our price abstraction
     *
     * @param StripePrice $stripePrice is a Stripe SDK price to convert
     *
     * @return Price is a generalized contracted object created using the external Stripe Price SDK representation as the reference
     *
     * @throws InvalidDaoException if an attempt is made to create a product without all the required data
     * @throws InvalidInputException if the $stripePrice is not a {@see \Stripe\Price}
     */
    protected static function createFromExternal(mixed $stripePrice): Price
    {
        if (!($stripePrice instanceof StripePrice)) {
            throw new InvalidInputException(
                'The external entity must be a ' . StripePrice::class
                    . '. A ' . $stripePrice::class . ' was passed instead.'
            );
        }

        $priceData = [
            'id' => $stripePrice->id,
            'interval' => $stripePrice->recurring->interval ?? null,
            'amount' => (float)($stripePrice->unit_amount / 100),
            'trial-period-days' => (int)$stripePrice->metadata['trial_period_days'],
            'currency' => $stripePrice->currency,
            'nickname' => $stripePrice->nickname ?? '',
        ];

        return new Price($priceData);
    }
}
