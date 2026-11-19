<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_profiles', function (Blueprint $table) {
            $table->id();

        $table->foreignId('user_id') ->unique()->constrained('real_estate_users') ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');

            $table->string('agency_name')->nullable();
            $table->string('license_number')->nullable();
            $table->text('id_document_url')->nullable();

            $table->enum('verification_status', [
                'unverified',
                'pending',
                'verified',
                'rejected',
            ])->default('unverified');

            $table->text('admin_notes')->nullable();

            $table->boolean('is_phone_verified')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_profiles');
    }
};