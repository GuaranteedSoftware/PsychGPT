<?php

namespace App\Http\Responses;

use App\Helpers\PsychGPT;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as Response;

/**
 * Class RegisterResponse handles register response after user signup via JetStream register
 */
class RegisterResponse implements Response
{
    /**
     * Fires after successful registration
     *
     * @param Request $request from the registration success route
     *
     * @return RedirectResponse instance to the process payment order
     */
    public function toResponse($request)
    {
        return redirect(
            route(
                'ecommerce.process.order',
                ['cart-product-id' => config('services.' . PsychGPT::ecommerceService()::id() . '.monthly_cart_product_id')]
            )
        );
    }
}
