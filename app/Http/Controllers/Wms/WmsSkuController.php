<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Wms\WmsSku;
use Illuminate\Http\Request;

class WmsSkuController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca'));

        $skus = WmsSku::query()
            ->when($busca !== '', function ($query) use ($busca): void {
                $query->where(function ($q) use ($busca): void {
                    $q->where('sku', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%")
                        ->orWhere('classe_peso', 'like', "%{$busca}%")
                        ->orWhere('classe_cubagem', 'like', "%{$busca}%")
                        ->orWhere('curva_abc', 'like', "%{$busca}%");
                });
            })
            ->orderBy('sku')
            ->paginate(25)
            ->withQueryString();

        return view('wms.skus.index', compact('skus', 'busca'));
    }
}
