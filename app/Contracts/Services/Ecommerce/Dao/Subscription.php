<?php

namespace App\Contracts\Services\Ecommerce\Dao;

use Illuminate\Support\Facades\Validator;

/**
 * A data access object for an e-commerce subscription
 */
abstract class Subscription extends BaseDao
{
    /**
     * A set of rules compliant with the {@see Validator} facade applied to {@see BaseDao::data}
     * @var array
     */
    protected const DATA_VALIDATION_RULES = [
        'customer-id' => 'required|string',
        'subscription-id' => 'required|string',
        'status' => 'required|string',
        'expires-on' => 'required',
    ];

    /**
     * Fetch an active subscription for provided customer
     *
     * @param Customer $customer for which, we will fetch the subscription
     *
     * @return ?Subscription an active or trail subscription for the given customer
     */
    abstract public static function getActiveSubscriptionFor(Customer $customer): ?Subscription;
}
