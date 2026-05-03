<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContagemPaleteController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()
            ->route('dashboard')
            ->with('warning', 'Modulo de contagem de paletes em implantacao.');
    }
}