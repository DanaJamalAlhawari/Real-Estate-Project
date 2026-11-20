<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')
                ->constrained('real_estate_users')
                ->restrictOnDelete();
            $table->decimal('percentage', 5, 2);
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::table('commissions', function (Blueprint $table) {
            
            $table->foreignId('payment_id')->nullable()->unique()->after('id')
                ->constrained('payments')->restrictOnDelete();
            $table->decimal('agent_net_amount', 12, 2)->nullable()->after('commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
            $table->dropColumn('agent_net_amount');
        });

        Schema::dropIfExists('commission_rates');
    }
};
