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
        Schema::create('sidlan_packages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sidlan_project_id')
                ->constrained('sidlan_projects')
                ->cascadeOnDelete();

            $table->text('package_name')->nullable();
            $table->longText('details')->nullable();

            $table->decimal('package_cost', 15, 2)->nullable();

            $table->string('procurement_mode')->nullable();
            $table->string('status')->nullable();

            $table->date('target_date_completion')->nullable();

            $table->string('contractor_supplier')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidlan_packages');
    }
};
