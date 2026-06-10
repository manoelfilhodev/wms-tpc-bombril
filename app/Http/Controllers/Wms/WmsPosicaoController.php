<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Wms\WmsPosicao;
use Illuminate\Http\Request;

class WmsPosicaoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca'));

        $posicoes = WmsPosicao::query()
            ->when($busca !== '', function ($query) use ($busca): void {
                $query->where(function ($q) use ($busca): void {
                    $q->where('rua', 'like', "%{$busca}%")
                        ->orWhere('posicao', 'like', "%{$busca}%")
                        ->orWhere('endereco', 'like', "%{$busca}%")
                        ->orWhere('bloco', 'like', "%{$busca}%")
                        ->orWhere('lado', 'like', "%{$busca}%")
                        ->orWhere('status', 'like', "%{$busca}%");
                });
            })
            ->orderBy('rua')
            ->orderBy('posicao')
            ->paginate(25)
            ->withQueryString();

        return view('wms.posicoes.index', compact('posicoes', 'busca'));
    }
}
