<?php

use App\Http\Controllers\EcommerceController;
use Illuminate\Support\Facades\Route;

/**
 * This file registers routes from all expected 3rd-party webhooks
 */

Route::post('process-webhook', [EcommerceController::class, 'processWebhook'])
    ->name('ecommerce.process.webhook');
