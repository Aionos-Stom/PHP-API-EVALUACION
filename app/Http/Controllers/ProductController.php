<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductController
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()->with('currency')->orderBy('id')->paginate($request->perPage())
                ->appends($request->validated()),
        );
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        return (new ProductResource($product))->response()->setStatusCode(201)
            ->header('Location', '/products/'.$product->getKey());
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('currency'));
    }

    public function update(ProductRequest $request, Product $product): ProductResource
    {
        return new ProductResource($this->products->update($product, $request->validated()));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->products->delete($product);

        return response()->json(['message' => 'Producto eliminado.']);
    }
}
