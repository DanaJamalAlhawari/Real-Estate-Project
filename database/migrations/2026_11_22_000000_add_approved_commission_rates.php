<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['properties', 'bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->decimal('approved_commission_rate', 5, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['properties', 'bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('approved_commission_rate');
            });
        }
    }
};
