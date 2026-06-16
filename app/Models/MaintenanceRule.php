<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'bike_id', 'name', 'rule_kind', 'distance_km', 'interval_days', 'email_enabled', 'active'])]
class MaintenanceRule extends Model
{
    use HasFactory;

    public const KIND_DISTANCE = 'distance';
    public const KIND_TIME = 'time';
    public const KIND_DISTANCE_OR_TIME = 'distance_or_time';

    protected $casts = [
        'distance_km' => 'float',
        'interval_days' => 'integer',
        'email_enabled' => 'boolean',
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bike(): BelongsTo
    {
        return $this->belongsTo(Bike::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MaintenanceEvent::class, 'rule_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(MaintenanceAlert::class, 'rule_id');
    }
}
