<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidlan_progress', function (Blueprint $table) {
            $table->id();

            $table->string('sp_index')->index();

            $table->date('actual_start_date')->nullable();
            $table->date('target_completion_date')->nullable();

            $table->json('accomplishment_dates')->nullable();
            $table->json('progress_report')->nullable();

            $table->timestamps();

            $table->unique('sp_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidlan_progress');
    }
};
