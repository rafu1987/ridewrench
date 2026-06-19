<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_events', function (Blueprint $table): void {
            $table->double('distance_km')->nullable()->after('note');
            $table->integer('elapsed_days')->nullable()->after('distance_km');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_events', function (Blueprint $table): void {
            $table->dropColumn(['distance_km', 'elapsed_days']);
        });
    }
};