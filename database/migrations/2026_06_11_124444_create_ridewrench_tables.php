<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('strava_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->unsignedBigInteger('athlete_id')->unique();
            $table->string('athlete_name', 190);

            $table->text('access_token');
            $table->text('refresh_token');
            $table->unsignedBigInteger('expires_at');

            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_full_synced_at')->nullable();
            $table->text('last_sync_error')->nullable();

            $table->timestamps();

            $table->index('athlete_id');
        });

        Schema::create('bikes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('strava_gear_id', 100);
            $table->string('name', 190);
            $table->string('type', 40)->default('other');
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(['user_id', 'strava_gear_id']);
            $table->index('user_id');
            $table->index(['user_id', 'active']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bike_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedBigInteger('strava_activity_id');
            $table->string('strava_gear_id', 100)->nullable();

            $table->string('name', 255);
            $table->string('sport_type', 80)->nullable();

            $table->double('distance_m')->default(0);
            $table->unsignedInteger('moving_time')->default(0);

            $table->timestamp('started_at');
            $table->timestamp('synced_at');

            $table->timestamps();

            $table->unique(['user_id', 'strava_activity_id']);
            $table->index('user_id');
            $table->index('bike_id');
            $table->index('started_at');
        });

        Schema::create('maintenance_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bike_id')->constrained()->cascadeOnDelete();

            $table->string('name', 190);
            $table->string('rule_kind', 40)->default('combined');

            $table->double('distance_km')->nullable();
            $table->unsignedInteger('interval_days')->nullable();

            $table->boolean('email_enabled')->default(true);
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index('user_id');
            $table->index('bike_id');
            $table->index(['user_id', 'active']);
            $table->index(['bike_id', 'active']);
        });

        Schema::create('maintenance_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bike_id')->constrained()->cascadeOnDelete();

            $table->foreignId('rule_id')->constrained('maintenance_rules')->cascadeOnDelete();

            $table->timestamp('performed_at');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('bike_id');
            $table->index('rule_id');
            $table->index('performed_at');
        });

        Schema::create('maintenance_alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bike_id')->constrained()->cascadeOnDelete();

            $table->foreignId('rule_id')->constrained('maintenance_rules')->cascadeOnDelete();

            $table->string('status', 40)->default('open');
            $table->text('due_reason');
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'rule_id', 'status']);
            $table->index('user_id');
            $table->index('bike_id');
            $table->index('rule_id');
            $table->index('status');
        });

        Schema::create('strava_webhook_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedBigInteger('athlete_id')->nullable();
            $table->string('object_type', 50);
            $table->string('aspect_type', 50);
            $table->string('object_id', 100)->nullable();
            $table->unsignedBigInteger('event_time')->nullable();

            $table->json('payload')->nullable();

            $table->string('status', 30)->default('received');
            $table->text('error')->nullable();

            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('athlete_id');
            $table->index('status');
            $table->index('received_at');
        });

        Schema::create('cron_runs', function (Blueprint $table) {
            $table->id();

            $table->string('status', 30)->default('running');

            $table->unsignedInteger('users_checked')->default(0);
            $table->unsignedInteger('failed_tasks')->default(0);
            $table->unsignedInteger('synced_activities')->default(0);
            $table->unsignedInteger('emails_sent')->default(0);

            $table->text('error')->nullable();

            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cron_runs');
        Schema::dropIfExists('strava_webhook_events');
        Schema::dropIfExists('maintenance_alerts');
        Schema::dropIfExists('maintenance_events');
        Schema::dropIfExists('maintenance_rules');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('bikes');
        Schema::dropIfExists('strava_accounts');
    }
};
