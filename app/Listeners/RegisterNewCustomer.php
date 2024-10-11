<?php

namespace App\Listeners;

use App\Contracts\Services\Ecommerce\Events\CustomerCreated;
use App\Models\EcommerceAccount;
use App\Models\User;

/**
 * Maps a newly created e-commerce customer to our ClientGenie app customers
 */
class RegisterNewCustomer
{
    /**
     * Creates {@see EcommerceAccount} for the {@see Owner} if doesn't exist,
     * if exist does not do anything
     *
     * @param CustomerCreated $customerCreatedEvent fired on ecommerce service created customer
     *
     * @return void
     */
    public function handle(CustomerCreated $customerCreatedEvent): void
    {
        $customer = $customerCreatedEvent->customer;

        $user = User::query()->where('email', $customer->get('email'))->firstOrFail();

        EcommerceAccount::unguard(); # can unguard because owner id is derived from the email from a trusted application
        EcommerceAccount::query()->firstOrCreate([
            'user_id' => $user->id,
            'remote_id' => $customer->get('id'),
            'service_name' => $customerCreatedEvent->ecommerceService::id(),
        ]);
        EcommerceAccount::reguard();
    }
}
