<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class ProductPriceRequest extends StrictRequest
{
    public function rules(): array
    {
        return [
            'currency_id' => ['bail', 'required', 'integer', 'min:1', 'exists:currencies,id'],
            'price' => $this->moneyRules(),
        ];
    }
}
