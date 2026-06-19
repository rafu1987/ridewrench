<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'bike_id', 'rule_id', 'performed_at', 'note', 'distance_km', 'elapsed_days'])]
class MaintenanceEvent extends Model
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

    public function maintenanceRule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRule::class, 'rule_id');
    }

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'distance_km' => 'float',
            'elapsed_days' => 'integer',
        ];
    }
}