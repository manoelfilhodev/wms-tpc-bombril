<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StoreApontamentoPaleteStretchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $paleteRules = ['required', 'string', 'max:120'];

        if (Schema::hasTable('_tb_apontamentos_paletes_stretch')) {
            $paleteRules[] = Rule::unique('_tb_apontamentos_paletes_stretch', 'palete_codigo')
                ->where('status', 'APONTADO')
                ->whereNull('deleted_at');
        }

        return [
            'palete_codigo' => $paleteRules,
            'observacao' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'palete_codigo.required' => 'Informe ou leia o codigo do palete.',
            'palete_codigo.unique' => 'Este palete ja possui apontamento de stretch ativo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'palete_codigo' => mb_strtoupper(trim((string) $this->input('palete_codigo'))),
            'observacao' => $this->filled('observacao')
                ? trim((string) $this->input('observacao'))
                : null,
        ]);
    }
}
