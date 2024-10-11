<?php

namespace App\Contracts\Services\Ecommerce\Dao;

use Illuminate\Support\Facades\Validator;

/**
 * A data access object for an e-commerce plan
 */
abstract class Plan extends BaseDao
{
    /**
     * A set of rules compliant with the {@see Validator} facade applied to {@see BaseDao::data}
     */
    protected const DATA_VALIDATION_RULES = [
        'id' => 'nullable|string',
        'name' => 'required|string',
        'price' => 'required|numeric',
    ];
}
