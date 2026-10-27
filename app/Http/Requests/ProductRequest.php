<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class ProductRequest extends StrictRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'Price' => $this->moneyRules(),
            'currency_id' => ['bail', 'required', 'integer', 'min:1', 'exists:currencies,id'],
            'tax_cost' => $this->moneyRules(),
            'manufacturing_cost' => $this->moneyRules(),
        ];
    }
}
