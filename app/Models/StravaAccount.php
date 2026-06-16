<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        'user_id',
        'athlete_id',
        'athlete_name',
        'access_token',
        'refresh_token',
        'expires_at',
        'last_synced_at',
        'last_full_synced_at',
        'last_sync_error',
    ]),
]
class StravaAccount extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'last_full_synced_at' => 'datetime',
        ];
    }
}
