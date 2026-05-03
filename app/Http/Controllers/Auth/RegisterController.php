<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): RedirectResponse|JsonResponse
    {
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
}