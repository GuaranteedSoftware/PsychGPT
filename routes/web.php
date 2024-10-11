<?php

use App\Http\Middleware\HasActivePlan;
use App\Livewire\ListNotes;
use App\Http\Controllers\EcommerceController;
use Illuminate\Support\Facades\Route;
use App\Livewire\PsychGpt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::middleware([HasActivePlan::class])->group(function () {
        Route::get('/', PsychGpt::class)->name('home');
        Route::get('/note-generator', PsychGpt::class)->name('create-note');
        Route::get('/note-generator/{id}', PsychGpt::class)->name('update-note');
        Route::get('/list-notes', ListNotes::class)->name('list-notes');
    });

    Route::get('process-order', [EcommerceController::class, 'processOrder'])
        ->name('ecommerce.process.order');
    Route::get('order-success', [EcommerceController::class, 'orderSuccess'])
        ->name('ecommerce.order.success');
    Route::get('order-failure', [EcommerceController::class, 'orderFailure'])
        ->name('ecommerce.order.failure');
});
