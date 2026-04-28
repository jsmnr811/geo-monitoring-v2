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
        Schema::create('sidlan_annexes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sidlan_project_id')
                ->constrained('sidlan_projects')
                ->cascadeOnDelete();

            $table->text('description')->nullable();
            $table->longText('objective')->nullable();

            $table->decimal('estimated_project_cost', 15, 2)->nullable();
            $table->decimal('approved_cost', 15, 2)->nullable();

            $table->string('validation_status')->nullable();

            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit_measure')->nullable();

            $table->date('target_start_date')->nullable();
            $table->date('target_completion_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidlan_annexes');
    }
};
