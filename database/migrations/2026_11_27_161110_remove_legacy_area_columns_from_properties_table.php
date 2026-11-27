<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['area']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'area',
                'area_ar',
                'area_en',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('area')->nullable()->index();
            $table->string('area_ar')->nullable();
            $table->string('area_en')->nullable();
        });
    }
};