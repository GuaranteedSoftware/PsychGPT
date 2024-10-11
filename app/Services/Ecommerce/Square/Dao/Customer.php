<?php

namespace App\Services\Ecommerce\Square\Dao;

use App\Contracts\Services\Ecommerce\Dao\BaseDao;
use App\Contracts\Services\Ecommerce\Dao\Customer as EcommerceCustomer;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\InvalidStateException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use App\Services\Ecommerce\Square\SquareService;
use Square\Models\Customer as SquareCustomer;
use Square\Models\CustomerFilter;
use Square\Models\CustomerQuery;
use Square\Models\CustomerTextFilter;
use Square\Models\SearchCustomersRequest;

/**
 * Square implementation of our e-commerce customer abstraction
 */
class Customer extends EcommerceCustomer
{
    /**
     * Fetches a collection of API objects from Square matching the search criteria
     *
     * @param array $filters The search criteria and formatting hints used to filter customers.
     *                       Details for customer searching can be read at
     *                       {@link https://developer.squareup.com/docs/customers-api/use-the-api/search-customers}
     *                       We will support exact searches for 'phone' and 'email'
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return BaseDao[] array containing all DAOs created from the Square API entities that match the filter criteria
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if a Square customer that is fetched does not have all the required
     *                               info to make a local {@see Customer}
     * @throws RetrievalException if there is a problem querying Square
     */
    public static function fetch(array $filters = [], mixed ...$extraParameters): array
    {
        $customerFilter = new CustomerFilter();

        if ($filters['email'] ?? false) {
            $emailAddress = new CustomerTextFilter();
            $emailAddress->setExact($filters['email']);
            $customerFilter->setEmailAddress($emailAddress);
        }

        if ($filters['phone'] ?? false) {
            $phoneNumber = new CustomerTextFilter();
            $phoneNumber->setExact($filters['phone']);
            $customerFilter->setPhoneNumber($phoneNumber);
        }

        $customerQuery = new CustomerQuery();
        $customerQuery->setFilter($customerFilter);
        $searchCustomerRequest = new SearchCustomersRequest();
        $searchCustomerRequest->setQuery($customerQuery);

        $response = SquareService::client()->getCustomersApi()->searchCustomers($searchCustomerRequest);

        if ($response->isError()) {
            throw new RetrievalException('Unable to communicate successfully with Square.');
        }

        $daos = [];
        foreach ($response->getResult()->getCustomers() ?? [] as $customer) {
            $daos[] = static::createFromExternal($customer);
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
     * @throws InvalidStateException if the Square customer that is fetched does not have all the required
     *                               info to make a local {@see Customer}
     * @throws RetrievalException if there is a problem querying Square
     */
    public static function find(string|int $id, mixed ...$extraParameters): ?BaseDao
    {
        $response = SquareService::client()->getCustomersApi()->retrieveCustomer($id);

        if ($response->isError()) {
            throw new RetrievalException('Unable to communicate successfully with Square.');
        }

        $customer = $response->getResult()->getCustomer();

        return $customer ? static::createFromExternal($customer) : null;
    }

    /**
     * Given a {@see SquareCustomer}, converts it to our customer abstraction
     *
     * @param SquareCustomer $squareCustomer is a Stripe SDK customer to convert
     *
     * @return Customer created from a Square SDK customer
     *
     * @throws InvalidDaoException if an attempt is made to create a customer without all the proper data
     * @throws InvalidInputException if the $squareCustomer is not a {@see SquareCustomer}
     */
    protected static function createFromExternal(mixed $squareCustomer): Customer
    {
        if (!($squareCustomer instanceof SquareCustomer)) {
            throw new InvalidInputException(
                'The external entity must be a ' . SquareCustomer::class
                . '. A ' . $squareCustomer::class . ' was passed instead.'
            );
        }

        $customerData = [
            'id' => $squareCustomer->getId(),
            'name' => $squareCustomer->getGivenName() . ' ' . $squareCustomer->getFamilyName(),
            'phone' => $squareCustomer->getPhoneNumber(),
            'email' => $squareCustomer->getEmailAddress(),
        ];

        return new Customer($customerData);
    }
}
