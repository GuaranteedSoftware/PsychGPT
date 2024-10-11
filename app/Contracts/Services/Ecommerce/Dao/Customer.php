<?php

namespace App\Contracts\Services\Ecommerce\Dao;

/**
 * A data access object for the e-commerce service's customer
 */
abstract class Customer extends BaseDao
{
    /**
     * A set of rules compliant with the {@see Validator} facade applied to {@see BaseDao::data}
     */
    protected const DATA_VALIDATION_RULES = [
        'id' => 'nullable|string',
        'email' => 'required|string',
        'name' => 'nullable|string',
        'first-name' => 'nullable|string',
        'last-name' => 'nullable|string',
        'phone' => 'nullable|string',
    ];
}
