<?php

namespace App\Services\Ecommerce\Square\Dao;

use App\Contracts\Services\Ecommerce\Dao\BaseDao;
use App\Contracts\Services\Ecommerce\Dao\Plan as EcommercePlan;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\InvalidStateException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use Square\Models\CatalogObject as SquareSubscriptionPlan;

/**
 * A Stripe implementation of our e-commerce plan abstraction
 */
class Plan extends EcommercePlan
{
    /**
     * Fetches a collection of API objects from this e-commerce service, matching the search criteria
     *
     * @param array $filters The search criteria and formatting hints used to filter catalog objects.
     *                       Details for catalog object searching can be read at
     *                       {@link https://developer.squareup.com/docs/catalog-api/search-catalog-objects}
     *                       We will support searches for 'object_types' and 'name'
     * @param array $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type.  An array with the integer 'limit' is supported
     *                               which limits the number of results that are returned
     *
     * @return Product[] array containing all DAOs created from the Square API entities that match the filter criteria
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if a Square product that is fetched does not have all the required info to
     *                               make a local {@see Plan}
     * @throws RetrievalException if there is a problem querying Square
     */
    public static function fetch(array $filters = [], mixed ...$extraParameters): array
    {
        $filters['object_types'] = ['SUBSCRIPTION_PLAN_VARIATION'];

        $squareSubscriptionPlans = Product::fetchAs(Product::AS_SQUARE_OBJECT, $filters, $extraParameters);

        foreach ($squareSubscriptionPlans as $squarePlan) {
            $daos[] = static::createFromExternal($squarePlan);
        }

        return $daos;
    }

    /**
     * Finds a plan via the Square API
     *
     * @param string|int $id The ID of the object to retrieve
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return ?BaseDao The found entity
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if the Square product that is found does not have all the required info to
     *                               make a local {@see Plan}
     * @throws RetrievalException if there is a problem querying Square
     */
    public static function find(string|int $id, mixed ...$extraParameters): ?BaseDao
    {
        return static::createFromExternal(
            Product::findAs(Product::AS_SQUARE_OBJECT, $id, $extraParameters)
        );
    }

    /**
     * Given a {@see SquareSubscriptionPlan}, converts it to our plan abstraction
     *
     * @param SquareSubscriptionPlan $squareSubscriptionPlan is a Stripe SDK product to convert to a product DAO
     *
     * @return Plan is a generalized contracted object created using the external representation as the reference
     *
     * @throws InvalidDaoException if an attempt is made to create a plan without all the required data
     * @throws InvalidInputException if the $squareProduct is not a {@see SquareProduct}
     */
    protected static function createFromExternal(mixed $squareSubscriptionPlan): Plan
    {
        if (!($squareSubscriptionPlan instanceof SquareSubscriptionPlan)) {
            throw new InvalidInputException(
                'The external entity must be a ' . SquareSubscriptionPlan::class
                . '. A ' . $squareSubscriptionPlan::class . ' was passed instead.'
            );
        }

        $planData = [
            'id' => $squareSubscriptionPlan->getId(),
            'name' => $squareSubscriptionPlan->getSubscriptionPlanVariationData()->getName(),
            'price' => $squareSubscriptionPlan->getSubscriptionPlanVariationData()->getPhases()[0]->getPricing()->getPriceMoney()->getAmount(),
            'location_id' => config('services.square.location_id'),
        ];

        return new Plan($planData);
    }
}
