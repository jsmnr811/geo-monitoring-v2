<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidlan_packages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sidlan_project_id')
                ->constrained('sidlan_projects')
                ->cascadeOnDelete();

            $table->string('package_name')->nullable();
            $table->text('details')->nullable();

            $table->decimal('package_cost', 15, 2)->nullable();

            $table->string('procurement_mode')->nullable();

            $table->string('pras_file')->nullable();
            $table->date('publication_closing_date')->nullable();

            $table->text('link_to_files')->nullable();

            $table->date('target_date_completion')->nullable();

            $table->date('contract_duration_from')->nullable();
            $table->date('contract_duration_to')->nullable();

            $table->string('contractor_supplier')->nullable();

            $table->decimal('financial_capacity', 15, 2)->nullable();
            $table->decimal('bidded_amount', 15, 2)->nullable();
            $table->decimal('awarded_cost', 15, 2)->nullable();

            $table->string('status')->nullable();

            $table->string('encoder')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidlan_packages');
    }
};
