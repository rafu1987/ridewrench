<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'bike_id', 'strava_activity_id', 'name', 'sport_type', 'distance_km', 'moving_time', 'started_at'])]
class Activity extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bike(): BelongsTo
    {
        return $this->belongsTo(Bike::class);
    }

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'moving_time' => 'integer',
            'started_at' => 'datetime',
        ];
    }
}
