<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidlan_annexes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sidlan_project_id')
                ->constrained('sidlan_projects')
                ->cascadeOnDelete();

            $table->text('sp_description')->nullable();
            $table->text('sp_objective')->nullable();

            $table->decimal('cost_during_validation', 15, 2)->nullable();
            $table->decimal('estimated_project_cost', 15, 2)->nullable();
            $table->decimal('cost_rpab_approved', 15, 2)->nullable();
            $table->decimal('cost_nol_1', 15, 2)->nullable();

            $table->date('date_validated')->nullable();

            $table->string('validation_status')->nullable();
            $table->text('validation_remarks')->nullable();

            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit_measure')->nullable();
            $table->decimal('linear_meter', 10, 2)->nullable();

            $table->date('contract_duration_from')->nullable();
            $table->date('contract_duration_to')->nullable();

            $table->string('construction_duration')->nullable();
            $table->string('validation_report')->nullable();

            $table->date('target_start_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();

            $table->decimal('latitude', 15, 10)->nullable();
            $table->decimal('longitude', 15, 10)->nullable();

            $table->string('encoder')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidlan_annexes');
    }
};
