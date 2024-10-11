<?php

namespace App\Models;

use App\Helpers\PsychGPT;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * The ecommerce account associated with this user
     *
     * @return HasOne {@see EcommerceAccount}
     */
    public function ecommerceAccount(): HasOne
    {
        return $this->hasOne(EcommerceAccount::class);
    }

    /**
     * Determines if the user has an active plan
     *
     * @return bool true if this user has an active plan, otherwise false
     */
    public function hasActivePlan(): bool
    {
        $meAsCustomer = PsychGPT::ecommerceService()->makeCustomer(
            [
                'id' => $this->ecommerceAccount?->remote_id,
                'email' => $this->email,
            ]
        );

        return (bool)PsychGPT::ecommerceService()->getActiveSubscriptionFor($meAsCustomer);
    }
}
