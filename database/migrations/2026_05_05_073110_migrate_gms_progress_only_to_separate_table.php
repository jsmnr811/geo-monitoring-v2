<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing data from users to gms_user_preferences
        DB::statement('
            INSERT INTO gms_user_preferences (user_id, progress_only, created_at, updated_at)
            SELECT id, gms_progress_only, NOW(), NOW() FROM users WHERE gms_progress_only IS NOT NULL
        ');

        // Drop the column from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gms_progress_only');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('gms_progress_only')->default(false);
        });

        // Migrate data back (optional, for rollback)
        DB::statement('
            UPDATE users SET gms_progress_only = (
                SELECT progress_only FROM gms_user_preferences WHERE gms_user_preferences.user_id = users.id
            ) WHERE id IN (SELECT user_id FROM gms_user_preferences)
        ');
    }
};
