<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'Price' => $this->Price,
            'currency_id' => $this->currency_id,
            'tax_cost' => $this->tax_cost,
            'manufacturing_cost' => $this->manufacturing_cost,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
        ];
    }
}
