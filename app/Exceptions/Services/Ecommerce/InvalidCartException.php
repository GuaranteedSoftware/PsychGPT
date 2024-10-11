<?php

namespace App\Exceptions\Services\Ecommerce;

/**
 * Thrown when an attempt is made to create an order with insufficient cart data
 */
class InvalidCartException extends InvalidStateException
{
}
