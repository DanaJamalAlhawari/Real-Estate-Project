<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agent_id')
                ->constrained('real_estate_users')
                ->restrictOnDelete();

            $table->string('title');

            $table->text('description');

            $table->enum('category', [
                'student_apartment',
                'apartment',
                'vacation',
            ]);

            $table->decimal('price', 10, 2)->nullable();

            $table->string('city')->index();

            $table->string('area')->nullable()->index();

            $table->string('address');

            $table->decimal('latitude', 10, 8)->nullable();

            $table->decimal('longitude', 11, 8)->nullable();

            $table->unsignedTinyInteger('bedrooms')->default(0);

            $table->unsignedTinyInteger('bathrooms')->default(0);

            $table->enum('status', [
                'available',
                'partially_rented',
                'rented',
                'hidden',
            ])->default('available');

            $table->string('ownership_deed_url');

            $table->enum('verification_status', [
                'unverified',
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('real_estate_users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};