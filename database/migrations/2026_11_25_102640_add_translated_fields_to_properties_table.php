<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $table->string('title_ar')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('city_ar')->nullable();
            $table->string('area_ar')->nullable();
            $table->string('address_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('description_en')->nullable();
            $table->string('city_en')->nullable();
            $table->string('area_en')->nullable();
            $table->string('address_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $table->dropColumn([
                'title_ar',
                'description_ar',
                'city_ar',
                'area_ar',
                'address_ar',
                'title_en',
                'description_en',
                'city_en',
                'area_en',
                'address_en',
            ]);
        });
    }
};
