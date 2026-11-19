<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->unique()
                ->constrained('bookings')
                ->restrictOnDelete();

            $table->foreignId('agent_id')
                ->constrained('real_estate_users')
                ->restrictOnDelete();

            $table->decimal('commission_rate', 5, 2);

            $table->decimal('commission_amount', 12, 2);

            $table->enum('status', [
                'pending',
                'paid',
                'cancelled',
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};