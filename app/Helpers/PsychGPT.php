<?php

namespace App\Helpers;

use App\Contracts\Services\Ecommerce\EcommerceService;

/**
 * Wrapper class that adds IDE identifiable types to service container created objects
 */
class PsychGPT
{
    /**
     * Gets a simple binded {@see EcommerceService}
     *
     * @return EcommerceService as defined in {@see AppServiceProvider}
     */
    public static function ecommerceService(): EcommerceService
    {
        return app(EcommerceService::class);
    }
}
