<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gms_albums', function (Blueprint $table) {
            $table->id();

            $table->string('sp_id')->index();
            $table->string('sp_index')->nullable();

            $table->string('album')->nullable();
            $table->text('description')->nullable();
            $table->date('report_date')->nullable();
            $table->string('content')->nullable();
            $table->string('item_of_work')->nullable();

            $table->integer('geotag_count')->nullable();
            $table->text('cover_photo')->nullable();

            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->index(['sp_id', 'album']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gms_albums');
    }
};
