<?php

namespace App\Contracts\Services\Ecommerce\Events;

use App\Contracts\Services\Ecommerce\Dao\Customer;
use App\Contracts\Services\Ecommerce\EcommerceService;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event that signifies that a new customer was created at the e-commerce service that needs to be
 * mapped to our ClientGenie app
 */
class CustomerCreated
{
    use Dispatchable;

    /**
     * Wraps data objects to our event
     *
     * @param Customer $customer the ecommerce representation of a user
     * @param EcommerceService $ecommerceService the ecommerce service where the customer's account lives
     */
    public function __construct(public Customer $customer, public EcommerceService $ecommerceService)
    {
        //
    }
}
