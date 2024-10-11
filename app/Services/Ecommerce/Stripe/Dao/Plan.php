<?php

namespace App\Services\Ecommerce\Stripe\Dao;

use App\Contracts\Services\Ecommerce\Dao\Plan as EcommercePlan;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\InvalidStateException;
use App\Services\Ecommerce\Stripe\Concerns\QueriesApi;
use Illuminate\Support\Facades\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Product as StripeProduct;

/**
 * A Stripe implementation of our e-commerce plan abstraction
 */
class Plan extends EcommercePlan
{
    use QueriesApi;

    /**
     * Given a {@see \Stripe\Product}, converts it to our plan abstraction
     *
     * @param StripeProduct $stripeProduct is a Stripe SDK product to convert to a plan DAO
     *
     * @return Plan created from a Stripe SDK product
     *
     * @throws ApiErrorException if the {@see \Stripe\ApiRequestor::request} fails
     * @throws InvalidDaoException if an attempt is made to create a plan without all the required data
     * @throws InvalidInputException if the plan does not have all the necessary data in the required format
     * @throws InvalidStateException if this plan DAO is not a {@see BaseDao}
     */
    static public function createFromExternal(mixed $stripeProduct): Plan
    {
        if (!($stripeProduct instanceof StripeProduct)) {
            throw new InvalidInputException(
                'The external entity must be a ' . StripeProduct::class
                    . '. A ' . $stripeProduct::class . ' was passed instead.'
            );
        }

        $planData = [
            'id' => $stripeProduct->id,
            'name' => $stripeProduct->name,
            'slug' => $stripeProduct->metadata['slug'],
            'monthly-savings' => (float)$stripeProduct->metadata['monthly-savings'],
            'monthly-revenue' => (float)$stripeProduct->metadata['monthly-revenue'],
            'team-accounts-limit' => !is_null($v = $stripeProduct->metadata['team-accounts-limit']) ? (int)$v : null,
            'facebook-groups-limit' => !is_null($v = $stripeProduct->metadata['facebook-groups-limit']) ? (int)$v : null,
            'moderators-limit' => !is_null($v = $stripeProduct->metadata['moderators-limit']) ? (int)$v : null,
            'activation-call-is-included' => in_array(
                $stripeProduct->metadata['activation-call-is-included'], ['true', 'True', 'yes', 'Yes', '1']
            ),
        ];

        $prices = Price::fetch(['query' => "active:'true' AND product:'{$stripeProduct->id}'"]);
        $signupType = Session::get('signup_type', ''); //-special-offer

        foreach($prices as $price) {
            if ($price->get('interval') === 'year') {
                if (!$price->get('nickname')) {
                    $planData['regular-yearly-price'] = $price->get('amount');
                }

                if ($price->get('nickname') === $signupType) {
                    $planData['yearly-price-id'] = $price->get('id');
                    $planData['yearly-price'] = $price->get('amount');
                    $planData['yearly-price-per-month'] = (float)($planData['yearly-price'] / 12);
                }
            }

            if ($price->get('interval') === 'month') {
                if (!$price->get('nickname')) {
                    $planData['regular-monthly-price'] = $price->get('amount');
                }

                if ($price->get('nickname') === $signupType) {
                    $planData['monthly-price-id'] = $price->get('id');
                    $planData['monthly-price'] = $price->get('amount');
                }
            }
            $planData['currency'] = $price->get('currency');
        }

        return new Plan($planData);
    }
}
