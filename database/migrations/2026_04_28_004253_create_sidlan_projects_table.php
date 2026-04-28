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
        Schema::create('sidlan_projects', function (Blueprint $table) {
            $table->id();

            $table->string('sp_index')->unique();
            $table->string('sp_id')->index();

            $table->text('project_name')->nullable();
            $table->string('project_type')->nullable();

            $table->string('component')->nullable()->index();
            $table->string('stage')->nullable()->index();
            $table->string('status')->nullable();

            $table->string('fund_source')->nullable();
            $table->string('cluster')->nullable();
            $table->string('region')->nullable();
            $table->string('province')->nullable()->index();
            $table->string('municipality')->nullable()->index();

            $table->decimal('indicative_cost', 15, 2)->nullable();
            $table->decimal('cost_during_validation', 15, 2)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->date('date_validated')->nullable(); // FIXED
            $table->timestamp('api_timestamp')->nullable();

            $table->string('encoder')->nullable();

            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->index(['component', 'stage']); // OPTIMIZATION
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidlan_projects');
    }
};
