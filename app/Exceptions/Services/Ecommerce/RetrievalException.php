<?php

namespace App\Exceptions\Services\Ecommerce;

use Exception;

/**
 * Base class for exceptions that signify any failed attempt to retrieve
 * information from an e-commerce service.  It generally will wrap other
 * exceptions.
 */
class RetrievalException extends Exception
{
    /**
     * Appends
     *
     * @param string $message the wrapper error message
     * @param int $code error code as a digit
     * @param ?Exception $previous the original exception wrapped by this exception
     */
    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null)
    {
        if ($previous) {
            $message = $message . "\n" . $previous->getMessage() . "\n" . $previous->getTraceAsString();
        }

        parent::__construct($message, $code, $previous);
    }
}
