<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_tasks', function (Blueprint $table) {
            $table->string('pricing_mode')->default('fixed');
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quote_tasks', function (Blueprint $table) {
            $table->dropColumn(['pricing_mode', 'quantity', 'unit', 'unit_price']);
        });
    }
};
