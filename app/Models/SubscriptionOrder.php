<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionOrder extends Model
{
    protected $connection = 'mysql';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_PROVISIONING = 'provisioning';

    public const STATUS_ACTIVATED = 'activated';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'uuid',
        'provider',
        'external_id',
        'customer_email',
        'amount',
        'currency',
        'status',
        'activation_token_hash',
        'paid_at',
        'activation_expires_at',
        'activated_at',
        'empresa_id',
        'provider_payload',
    ];

    protected $hidden = [
        'activation_token_hash',
        'provider_payload',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'activation_expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'provider_payload' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function canBeActivated(): bool
    {
        return $this->status === self::STATUS_PAID
            && $this->activated_at === null
            && $this->activation_expires_at?->isFuture();
    }
}
