<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('real_estate_users')
                ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->boolean('active')->default(1);

            $table->string('emergency_contact')->nullable();
            $table->string('student_id')->nullable();

            $table->boolean('is_phone_verified')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_profiles');
    }
};