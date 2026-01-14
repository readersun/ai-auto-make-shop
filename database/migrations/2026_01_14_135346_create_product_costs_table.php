<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('product_unit_price', 10, 0)->nullable();
            $table->decimal('international_shipping', 10, 0)->default(0);
            $table->decimal('customs_duty', 10, 0)->default(0);
            $table->decimal('domestic_shipping', 10, 0)->default(0);
            $table->decimal('risk_cost', 10, 0)->default(0);
            $table->decimal('total_cost', 10, 0);
            $table->text('cost_notes')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_url')->nullable();
            $table->timestamps();

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_costs');
    }
};
