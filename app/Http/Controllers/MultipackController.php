<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Auth;



class MultipackController extends Controller
{
    public function create()
    {
        return view('multipack.create');
    }

    public function store(Request $request)
    {
        $data = [];

        foreach ($request->sku as $index => $sku) {
            $data[] = [
                'sku' => $sku,
                'descricao' => $request->descricao[$index],
                'fator_embalagem' => $request->fator_embalagem[$index],
                'created_at' => now(),
            ];
        }
    

        DB::table('_tb_materiais_multipack')->insert($data);
        
        $descricaoLog = '[CADASTRO] - ' . Auth::user()->nome . ' cadastrou os seguintes SKUs:';
        
        foreach ($data as $item) {
            $descricaoLog .= ' [SKU: ' . $item['sku'] . ', Fator: ' . $item['fator_embalagem'] . ']';
        }
    
        LogHelper::registrar('Cadastro Multipack', $descricaoLog);
        

        return redirect()->back()->with('success', 'Multipacks cadastrados com sucesso!');
    }
}
