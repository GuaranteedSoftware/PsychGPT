<?php

namespace App\Exceptions\Services\Ecommerce;

/**
 * Thrown when an attempt is made to create a DAO in an invalid state
 * such as lacking the required data values, or having a data value not of the proper type
 */
class InvalidDaoException extends InvalidStateException
{
}
