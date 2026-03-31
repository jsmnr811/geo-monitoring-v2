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
        Schema::table('users', function (Blueprint $table) {
            $table->string('geo_mapping_user_id')->nullable()->unique()->after('id');
            $table->string('geo_mapping_name')->nullable()->after('geo_mapping_user_id');
            $table->string('geo_mapping_office')->nullable()->after('geo_mapping_name');
            $table->string('geo_mapping_position')->nullable()->after('geo_mapping_office');
            $table->text('geo_mapping_access_token')->nullable()->after('geo_mapping_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'geo_mapping_user_id',
                'geo_mapping_name',
                'geo_mapping_office',
                'geo_mapping_position',
                'geo_mapping_access_token',
            ]);
        });
    }
};
