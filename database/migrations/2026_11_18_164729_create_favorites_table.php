<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->constrained('real_estate_users')
                ->cascadeOnDelete();

            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->primary(['tenant_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};