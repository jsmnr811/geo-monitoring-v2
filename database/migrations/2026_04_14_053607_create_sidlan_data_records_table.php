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
        Schema::create('sidlan_data_records', function (Blueprint $table) {
            $table->id();
            $table->string('sp_id')->unique(); // Unique identifier for subproject
            $table->string('project_name')->nullable();
            $table->string('stage')->nullable(); // Construction, Completed
            $table->string('component')->nullable(); // I-BUILD
            $table->json('data'); // Store complete SIDLAN data as JSON
            $table->json('filtered_data')->nullable(); // Store filtered/processed data
            $table->timestamp('data_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['stage', 'component']);
            $table->index('data_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidlan_data_records');
    }
};
