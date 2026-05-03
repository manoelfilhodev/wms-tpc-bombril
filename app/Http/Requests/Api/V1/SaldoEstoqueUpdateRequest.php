<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SaldoEstoqueUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantidade' => ['sometimes', 'integer', 'min:0'],
            'data_entrada' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->hasAny(['quantidade', 'data_entrada'])) {
                $validator->errors()->add('payload', 'Informe ao menos um campo para atualizacao.');
            }
        });
    }
}
