<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutoCadastroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'size:64'],
            'nome' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:100', 'unique:_tb_usuarios,email'],
            'senha' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token de convite obrigatorio.',
            'token.size' => 'Token de convite invalido.',
            'nome.required' => 'Informe o nome.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail valido.',
            'email.unique' => 'Ja existe usuario com este e-mail.',
            'senha.required' => 'Informe a senha.',
            'senha.min' => 'A senha deve ter pelo menos :min caracteres.',
        ];
    }
}
