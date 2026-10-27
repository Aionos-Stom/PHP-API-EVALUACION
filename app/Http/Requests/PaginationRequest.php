<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class PaginationRequest extends StrictRequest
{
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1', 'max:1000000'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', 15);
    }
}
