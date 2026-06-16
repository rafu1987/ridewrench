<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['status', 'users_checked', 'failed_tasks', 'synced_activities', 'emails_sent', 'started_at', 'finished_at', 'error'])]
class CronRun extends Model
{
    use HasFactory;

    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected function casts(): array
    {
        return [
            'users_checked' => 'integer',
            'failed_tasks' => 'integer',
            'synced_activities' => 'integer',
            'emails_sent' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
