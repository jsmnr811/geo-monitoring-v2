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
        Schema::create('data_quality_justifications', function (Blueprint $table) {
            $table->id();
            $table->string('sp_id');
            $table->string('issue_type');
            $table->text('justification_text');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->index(['sp_id', 'issue_type']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_quality_justifications');
    }
};
