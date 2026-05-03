<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportMb52Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'arquivo' => 'required|mimes:xlsx,xls|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'arquivo.required' => 'Você deve selecionar um arquivo.',
            'arquivo.mimes' => 'Apenas arquivos .xlsx ou .xls são permitidos.',
            'arquivo.max' => 'O tamanho máximo permitido é 2MB.',
        ];
    }
}
