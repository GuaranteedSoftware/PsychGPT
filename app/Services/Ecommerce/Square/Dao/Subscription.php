<?php

namespace App\Services\Ecommerce\Square\Dao;

use App\Contracts\Services\Ecommerce\Dao\BaseDao;
use App\Contracts\Services\Ecommerce\Dao\Customer as EcommerceCustomer;
use App\Contracts\Services\Ecommerce\Dao\Subscription as EcommerceSubscription;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\InvalidStateException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use App\Services\Ecommerce\Square\SquareService;
use Exception;
use Square\Models\SearchSubscriptionsFilter;
use Square\Models\SearchSubscriptionsQuery;
use Square\Models\SearchSubscriptionsRequest;
use Square\Models\Subscription as SquareSubscription;

/**
 * A Square implementation of our e-commerce subscription abstraction
 */
class Subscription extends EcommerceSubscription
{
    /**
     * Fetches a collection of API objects from Square matching the search criteria
     *
     * @param array $filters The search criteria and formatting hints used to filter subscriptions.
     *                       Details for subscription searching can be read at
     *                       {@link https://developer.squareup.com/reference/square/subscriptions-api/search-subscriptions}
     *                       We will support searches for 'customer_ids' and 'sources_names' which is the name of the
     *                       Square application
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return Subscription[] array containing all DAOs created from the Square API entities that match the filter criteria
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if a Square subscription that is fetched does not have all the required
     *                               info to make a local {@see Subscription}
     * @throws RetrievalException if there is a problem querying Square
     */
    public static function fetch(array $filters = [], mixed ...$extraParameters): array
    {
        $customerIds = $filters['customer_ids'] ?? [];
        $sourceNames = $filters['sources_names'] ?? [];

        $searchSubscriptionsFilter = new SearchSubscriptionsFilter();
        if ($customerIds) {
            $searchSubscriptionsFilter->setCustomerIds($customerIds);
        }

        if ($sourceNames) {
            $searchSubscriptionsFilter->setSourceNames($sourceNames);
        }

        $searchSubscriptionsQuery = new SearchSubscriptionsQuery();
        $searchSubscriptionsQuery->setFilter($searchSubscriptionsFilter);

        $searchSubscriptionsRequest = new SearchSubscriptionsRequest();
        $searchSubscriptionsRequest->setQuery($searchSubscriptionsQuery);

        $response = SquareService::client()->getSubscriptionsApi()->searchSubscriptions($searchSubscriptionsRequest);

        if ($response->isError()) {
            throw new RetrievalException('Unable to communicate successfully with Square.');
        }

        $daos = [];
        foreach ($response->getResult()->getSubscriptions() ?? [] as $subscription) {
            $daos[] = static::createFromExternal($subscription);
        }

        return $daos;
    }

    /**
     * Gets a native representation of an entity from the Square API
     *
     * @param string|int $id The ID of the object to retrieve
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return ?BaseDao The found entity
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if the Square subscription that is fetched does not have all the required
     *                               info to make a local {@see Subscription}
     * @throws RetrievalException if there is a problem querying Square
     */
    public static function find(string|int $id, mixed ...$extraParameters): ?BaseDao
    {
        $response = SquareService::client()->getSubscriptionsApi()->retrieveSubscription($id);

        if ($response->isError()) {
            throw new RetrievalException('Unable to communicate successfully with Square.');
        }

        $subscription = $response->getResult()->getSubscription();

        return $subscription ? static::createFromExternal($subscription) : null;
    }

    /**
     * Given a {@see SquareSubscription}, converts it to our subscription abstraction
     *
     * @param SquareSubscription $squareSubscription is a Square SDK subscription to convert
     *
     * @return Subscription created from a Square SDK subscription
     *
     * @throws InvalidDaoException if an attempt is made to create a subscription without all the proper data
     * @throws InvalidInputException if the $squareSubscription is not a {@see SquareSubscription}
     */
    protected static function createFromExternal(mixed $squareSubscription): Subscription
    {
        if (!($squareSubscription instanceof SquareSubscription)) {
            throw new InvalidInputException(
                'The external entity must be a ' . SquareSubscription::class
                . '. A ' . $squareSubscription::class . ' was passed instead.'
            );
        }

        return new Subscription([
            'customer-id' => $squareSubscription->getCustomerId(),
            'subscription-id' => $squareSubscription->getId(),
            'status' => strtolower($squareSubscription->getStatus()),
            'expires-on' => $squareSubscription->getChargedThroughDate(),
        ]);
    }

    /**
     * Fetch subscription for provided customer
     *
     * @param Customer $customer for which, we will fetch the subscription
     *
     * @return ?Subscription an active or trail subscription for the given customer
     *
     * @throws RetrievalException if there is a problem communicating with Square
     */
    public static function getActiveSubscriptionFor(EcommerceCustomer $customer): ?Subscription
    {
        if (!$customer->get('id')) {
            return null;
        }

        try {
            $subscriptions = Subscription::fetch(
                ['customer_ids' => [$customer->get('id')]]
            );

            foreach ($subscriptions as $subscription) {
                if ($subscription->get('status') === 'active') {
                    return $subscription;
                }
            }
        } catch (Exception $exception) {
            throw new RetrievalException(message: "Unable to communicate successfully with Square.", previous: $exception);
        }

        return null;
    }
}
