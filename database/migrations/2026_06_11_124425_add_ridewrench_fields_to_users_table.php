<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('language', 10)->default('en')->after('password');
            $table->boolean('email_reminders_enabled')->default(true)->after('language');
            $table->boolean('is_admin')->default(false)->after('email_reminders_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['language', 'email_reminders_enabled', 'is_admin']);
        });
    }
};
