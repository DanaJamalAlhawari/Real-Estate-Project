<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_id')
                ->constrained('properties')
                ->restrictOnDelete();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->restrictOnDelete();

            $table->foreignId('tenant_id')
                ->constrained('real_estate_users')
                ->restrictOnDelete();

            $table->date('start_date');

            $table->date('end_date');

            $table->unsignedInteger('duration');

            $table->enum('duration_unit', [
                'daily',
                'weekly',
                'monthly',
                'yearly',
            ]) ->nullable();

            $table->decimal('total_price', 12, 2);

            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
                'completed',
            ])->default('pending');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};