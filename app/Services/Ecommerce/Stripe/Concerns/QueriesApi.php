<?php

namespace App\Services\Ecommerce\Stripe\Concerns;

use App\Contracts\Services\Ecommerce\Dao\BaseDao;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\InvalidStateException;
use App\Services\Ecommerce\Stripe\StripeService;
use Stripe\Exception\ApiErrorException;
use Stripe\Product as StripeProduct;
use Stripe\Subscription as StripeSubscription;
use Stripe\SearchResult as StripeSearchResult;

trait QueriesApi
{
    /**
     * Fetches a collection of API objects from Stripe matching the search criteria
     *
     * @param array $filters The search criteria and formatting hints used to filter products.
     *                       Details for products can be read at {@link https://stripe.com/docs/api/products/search}
     *                       Where the possible parameters are:
     *                          query (required) - uses {@link https://stripe.com/docs/search#search-query-language}
     *                          limit - the maximum number of objects to return
     *                          page - A cursor for pagination across multiple pages of results
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return BaseDao[] array containing all DAOs created from the Stripe API entities that match the filter criteria
     *
     * @throws ApiErrorException if the {@see \Stripe\ApiRequestor::request} fails
     * @throws InvalidInputException if the Stripe entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if this class is not a {@see BaseDao}
     */
    public static function fetch(array $filters = ['query' => "active:'true'"], mixed ...$extraParameters): array
    {
        self::verifyIsBaseDao();

        $sdkClassName = self::getStripeSdkClassName();

        /** @var StripeSearchResult $stripeEntities */
        $stripeEntities = match ($sdkClassName) {
            # override plans, we'll use products and price API instead
            StripeService::SDK_NAMESPACE . 'Plan' => app(StripeProduct::class)->search($filters),
            StripeService::SDK_NAMESPACE . 'Subscription' => app(StripeSubscription::class)->all($filters),
            default => $sdkClassName::search($filters)
        };

        $daos = [];
        foreach ($stripeEntities as $stripeEntity) {
            $daos[] = static::createFromExternal($stripeEntity);
        }

        return $daos;
    }

    /**
     * Gets a native representation of an entity from the Stripe API
     *
     * @param string|int $id The ID of the object to retrieve
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return ?BaseDao The found entity
     *
     * @throws ApiErrorException if the {@see \Stripe\ApiRequestor::request} fails
     * @throws InvalidInputException if the Stripe entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if this class is not a {@see BaseDao}
     */
    public static function find(string|int $id, mixed ...$extraParameters): ?BaseDao
    {
        self::verifyIsBaseDao();

        $sdkClassName = self::getStripeSdkClassName();

        return static::createFromExternal(
            match ($sdkClassName) {
                # override plans, we'll use products and price API instead
                StripeService::SDK_NAMESPACE . 'Plan' => StripeProduct::retrieve($id),
                default => $sdkClassName::retrieve($id)
            }
        );
    }

    /**
     * Gets the Stripe SDK class name correlated to this DAO
     *
     * @return string supported values are the namespaced Price|Product|Plan
     */
    private static function getStripeSdkClassName(): string
    {
        $classNameParts = explode('\\', get_called_class());
        return StripeService::SDK_NAMESPACE . end($classNameParts);
    }

    /**
     * Verifies that this class is a {@see BaseDao}
     *
     * @throws InvalidStateException if this class is not a {@see BaseDao}
     */
    private static function verifyIsBaseDao(): void
    {
        if (!is_subclass_of(self::class,BaseDao::class)) {
            throw new InvalidStateException(
                self::class . ' must extend ' . BaseDao::class . ' to query the Stripe API'
            );
        }
    }
}
