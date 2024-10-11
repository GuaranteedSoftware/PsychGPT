<?php

namespace App\Http\Middleware;

use App\Contracts\Services\Ecommerce\Events\CheckoutSuccess;
use App\Helpers\PsychGPT;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cache;
use Laravel\Jetstream\Team;

/**
 * Handler for {@see CheckoutSuccess} event, executed when the customer is successfully redirected back
 * from the payment processor
 */
class HasActivePlan
{
    /**
     * Constant representing the cache suffix used to store data related to active plans.
     *
     * @var string CACHE_SUFFIX
     */
    public const CACHE_SUFFIX = '_has_active_plan';

    /**
     * Handle an incoming request.
     *
     * @param Request $request expects an incoming HTTP request object as its first argument.
     * @param Closure(Request): (Response|RedirectResponse) $next closure used as middleware to pass control
     * to the next handler in the request pipeline
     *
     * @return Application|RedirectResponse|Redirector|mixed
     *      in some cases, the function might return an instance of the Application class, which typically represents
     *      the Laravel application itself.
     *      RedirectResponse: used to perform HTTP redirects in Laravel.
     *      Redirector: Similar to RedirectResponse, this suggests that the function could return a Redirector instance,
     *      which is used for generating redirects in Laravel.
     *      mixed: it can potentially return other types as well.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();
        $hasCachedActiveSubscription = Cache::get($this->getCacheKey($user));

        if (!$hasCachedActiveSubscription) {
            if ((bool) $this->getTeamWithActivePlanFor($user)) {
                Cache::put($this->getCacheKey($user), true, now()->addHour());
            } else {
                # No active subscription
                return redirect(
                    route(
                        'ecommerce.process.order',
                        ['cart-product-id' => config('services.' . PsychGPT::ecommerceService()::id() . '.monthly_cart_product_id')]
                    )
                );
            }
        }

        return $next($request);
    }

    /**
     * Generates cache-key for the provided owner under which the active plan status is temporarily stored
     *
     * @param User|Authenticatable $user to generate cache key for
     *
     * @return string cache key
     */
    public static function getCacheKey(User|Authenticatable $user): string
    {
        return $user->id . self::CACHE_SUFFIX;
    }

    /**
     * Determines if a given user is associated with a team with an active plan
     *
     * @param User|Authenticatable $user The user, whether owner or non-owner, for whom we are seeking a team with an active plan
     *
     * @return ?Team returns the first team of the user that is found with an active plan. The user may be a part of
     *               multiple teams that have active plans, but only one is returned.  No guarantee is made as to which
     *               team is returned.  If no such team exists, then null will be returned.
     */
    private function getTeamWithActivePlanFor(User|Authenticatable $user): ?Team {
        foreach ($user->allTeams() as $team) {
            if ($team->owner->hasActivePlan()) {
                return $team;
            }
        }

        return null;
    }
}
