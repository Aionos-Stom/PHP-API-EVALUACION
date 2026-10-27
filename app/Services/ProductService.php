<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Facades\DB;

final class ProductService
{
    public function create(array $attributes): Product
    {
        return DB::transaction(fn (): Product => Product::query()->create($attributes)->load('currency'));
    }

    public function update(Product $product, array $attributes): Product
    {
        return DB::transaction(function () use ($product, $attributes): Product {
            $current = Product::query()->lockForUpdate()->findOrFail($product->getKey());
            $current->update($attributes);

            return $current->load('currency');
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            Product::query()->lockForUpdate()->findOrFail($product->getKey())->delete();
        });
    }

    public function addPrice(Product $product, array $attributes): ProductPrice
    {
        return DB::transaction(function () use ($product, $attributes): ProductPrice {
            $current = Product::query()->lockForUpdate()->findOrFail($product->getKey());
            abort_if($current->prices()->where('currency_id', $attributes['currency_id'])->exists(), 409);

            return $current->prices()->create($attributes)->load('currency');
        });
    }
}
