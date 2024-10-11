<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EcommerceAccount is a user's associated account with an ecommerce payment service
 *
 * @package App\Models
 */
class EcommerceAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'remote_id',
        'service_name',
    ];

    /**
     * Sets timestamps (created_at, updated_at) to false
     * for prevent inserting timestamps when do mass assignment with Model
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Gets the user that owns this account
     *
     * @return BelongsTo a {@see Owner}
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
