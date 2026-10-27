<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('symbol', 16);
            $table->decimal('exchange_rate', 16, 6);
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('description', 2000);
            $table->decimal('Price', 12, 2);
            $table->unsignedInteger('currency_id');
            $table->decimal('tax_cost', 12, 2);
            $table->decimal('manufacturing_cost', 12, 2);
            $table->foreign('currency_id')->references('id')->on('currencies')->restrictOnDelete();
            $table->index('currency_id');
        });
        Schema::create('product_prices', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('currency_id');
            $table->decimal('price', 12, 2);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('currency_id')->references('id')->on('currencies')->restrictOnDelete();
            $table->unique(['product_id', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('currencies');
    }
};
