<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportMb51Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'arquivo' => 'required|file|mimes:xlsx,xls|max:2048',
        ];
    }
}
