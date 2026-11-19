<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            

            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();

            $table->foreignId('tenant_id')
                ->constrained('real_estate_users')
                ->restrictOnDelete();

            $table->foreignId('agent_id')
                ->constrained('real_estate_users')
                ->restrictOnDelete();

            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();
             $table->unique(['property_id', 'tenant_id', 'agent_id']);

            

            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};