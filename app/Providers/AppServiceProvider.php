<?php

namespace App\Providers;

use App\Contracts\Services\Ecommerce\Dao\Customer;
use App\Contracts\Services\Ecommerce\Dao\Plan;
use App\Contracts\Services\Ecommerce\Dao\Product;
use App\Contracts\Services\Ecommerce\Dao\Subscription;
use App\Contracts\Services\Ecommerce\EcommerceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $ecommerceServiceName = config('app.ecommerce_service');

        $this->app->singleton(
            EcommerceService::class,
            "App\Services\Ecommerce\\{$ecommerceServiceName}\\{$ecommerceServiceName}Service"
        );

        $this->app->bind(
            Customer::class,
            "App\Services\Ecommerce\\{$ecommerceServiceName}\Dao\Customer"
        );

        $this->app->bind(
            Product::class,
            "App\Services\Ecommerce\\{$ecommerceServiceName}\Dao\Product"
        );

        $this->app->bind(
            Plan::class,
            "App\Services\Ecommerce\\{$ecommerceServiceName}\Dao\Plan"
        );

        $this->app->bind(
            Subscription::class,
            "App\Services\Ecommerce\\{$ecommerceServiceName}\Dao\Subscription"
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
