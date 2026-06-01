<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): RedirectResponse|JsonResponse|View
    {
        if ($this->publicRegistrationAllowed($request)) {
            return view('auth.register');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Cadastro publico desabilitado.',
                'data' => (object) [],
                'meta' => (object) [],
            ], 403);
        }

        return redirect()->route('login')->with('error', 'Cadastro publico desabilitado.');
    }

    public function register(Request $request): RedirectResponse|JsonResponse
    {
        if ($this->publicRegistrationAllowed($request)) {
            $data = $request->validate([
                'nome' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email:rfc', 'max:100', 'unique:_tb_usuarios,email'],
                'password' => ['required', 'confirmed', Password::min(8)],
                'unidade_id' => ['required', 'integer', 'exists:_tb_unidades,id'],
            ]);

            User::create([
                'nome' => strtoupper($data['nome']),
                'email' => strtolower($data['email']),
                'password' => Hash::make($data['password']),
                'unidade_id' => $data['unidade_id'],
                'tipo' => 'operador',
                'status' => 'ativo',
                'nivel' => 'Operador',
            ]);

            return redirect()->route('login')->with('success', 'Cadastro realizado.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Cadastro publico desabilitado.',
                'data' => (object) [],
                'meta' => (object) [],
            ], 403);
        }

        return redirect()->route('login')->with('error', 'Cadastro publico desabilitado.');
    }

    private function publicRegistrationAllowed(Request $request): bool
    {
        if ($request->user() && strtolower((string) $request->user()->tipo) === 'admin') {
            return true;
        }

        return app()->environment(['local', 'development']);
    }
}
