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
        Schema::create('product_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('seo_title');
            $table->text('header_copy');
            $table->json('key_features');
            $table->text('recommendation');
            $table->text('usage_guide')->nullable();
            $table->text('precautions')->nullable();
            $table->text('shipping_info')->nullable();
            $table->text('exchange_return_info')->nullable();
            $table->longText('full_description')->nullable();
            $table->timestamps();

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_contents');
    }
};
