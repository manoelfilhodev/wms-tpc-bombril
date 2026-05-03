<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApontamentoPaleteStretchApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'palete_codigo' => ['required', 'string', 'max:120'],
            'origem' => ['nullable', Rule::in(['APP', 'API'])],
            'device_id' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:30'],
            'client_uuid' => ['required', 'uuid'],
            'apontado_em_app' => ['nullable', 'date'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'palete_codigo.required' => 'Informe o codigo do palete.',
            'client_uuid.required' => 'Informe o UUID da operacao.',
            'client_uuid.uuid' => 'O UUID da operacao deve ser valido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'palete_codigo' => mb_strtoupper(trim((string) $this->input('palete_codigo'))),
            'origem' => $this->filled('origem')
                ? mb_strtoupper(trim((string) $this->input('origem')))
                : 'APP',
            'device_id' => $this->filled('device_id')
                ? trim((string) $this->input('device_id'))
                : null,
            'app_version' => $this->filled('app_version')
                ? trim((string) $this->input('app_version'))
                : null,
            'observacao' => $this->filled('observacao')
                ? trim((string) $this->input('observacao'))
                : null,
        ]);
    }
}
