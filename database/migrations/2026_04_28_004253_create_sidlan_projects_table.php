<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidlan_projects', function (Blueprint $table) {
            $table->id();

            // API field: id (IB-347)
            $table->string('sp_index')->unique();

            $table->string('sp_id');

            $table->text('project_name')->nullable();
            $table->string('project_type')->nullable();

            $table->string('fund_source')->nullable();
            $table->string('cluster')->nullable();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();

            $table->decimal('indicative_cost', 15, 2)->nullable();
            $table->decimal('cost_during_validation', 15, 2)->nullable();

            $table->string('stage')->nullable();
            $table->string('status')->nullable();

            $table->date('date_validated')->nullable();

            $table->string('contractor_supplier')->nullable();

            $table->decimal('latitude', 15, 10)->nullable();
            $table->decimal('longitude', 15, 10)->nullable();

            $table->string('encoder')->nullable();

            $table->string('component')->nullable();

            // API timestamp (string format, not normalized)
            $table->string('timestamp')->nullable();

            $table->json('raw_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidlan_projects');
    }
};
