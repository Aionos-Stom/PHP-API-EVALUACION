<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\ProductPriceRequest;
use App\Http\Resources\ProductPriceResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductPriceController
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function index(PaginationRequest $request, Product $product): AnonymousResourceCollection
    {
        return ProductPriceResource::collection(
            $product->prices()->with('currency')->orderBy('id')->paginate($request->perPage())
                ->appends($request->validated()),
        );
    }

    public function store(ProductPriceRequest $request, Product $product): JsonResponse
    {
        return (new ProductPriceResource($this->products->addPrice($product, $request->validated())))
            ->response()->setStatusCode(201);
    }
}
