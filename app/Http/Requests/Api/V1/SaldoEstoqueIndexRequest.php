<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaldoEstoqueIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => [
                'sometimes',
                'string',
                Rule::in(['id', 'quantidade', 'created_at', 'updated_at', 'sku', 'descricao', 'posicao', 'unidade_id']),
            ],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'sku' => ['sometimes', 'string', 'max:60'],
            'material' => ['sometimes', 'integer', 'min:1'],
            'descricao' => ['sometimes', 'string', 'max:255'],
            'unidade' => ['sometimes', 'integer', 'min:1'],
            'posicao' => ['sometimes', 'string', 'max:100'],
            'min_qtd' => ['sometimes', 'integer', 'min:0'],
            'max_qtd' => ['sometimes', 'integer', 'min:0', 'gte:min_qtd'],
            'updated_from' => ['sometimes', 'date'],
            'updated_to' => ['sometimes', 'date', 'after_or_equal:updated_from'],
        ];
    }
}
