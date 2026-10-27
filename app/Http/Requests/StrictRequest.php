<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class StrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ApiBoundary ya autenticó al cliente antes del enrutamiento.
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $unknown = array_diff(array_keys($this->all()), array_keys($this->rules()));
            if ($unknown !== []) {
                $validator->errors()->add('request', 'La solicitud contiene campos no permitidos.');
            }
            if (! $this->isMethod('GET') && $this->query->count() !== 0) {
                $validator->errors()->add('request', 'Envía los campos exclusivamente en el cuerpo JSON.');
            }
        });
    }

    protected function moneyRules(): array
    {
        return ['bail', 'required', 'numeric', 'regex:/\A(?:0|[1-9][0-9]{0,9})(?:\.[0-9]{1,2})?\z/'];
    }
}
