<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\KitEtiqueta;
use App\Models\KitMontagem;


class KitEtiquetaController extends Controller
{
    /**
     * Visualizar todas as etiquetas de um kit
     */
public function index()
{
    $kits = KitMontagem::orderBy('created_at', 'desc')->get();
    return view('kit.etiquetas.index', compact('kits'));
}

public function visualizar($id)
{
    $kit = KitMontagem::findOrFail($id);

    $etiquetas = DB::table('_tb_apontamentos_kits')
        ->where('codigo_material', $kit->codigo_material)
        ->orderBy('id', 'desc')
        ->get();

    return view('kit.etiquetas.visualizar', compact('kit', 'etiquetas'));
}






    /**
     * Imprimir todas as etiquetas
     */
public function imprimirTudo($kit_id)
{
    $kit = KitMontagem::findOrFail($kit_id);

    $apontamentos = DB::table('_tb_apontamentos_kits as ak')
    ->join('_tb_materiais as m', 'm.sku', '=', 'ak.codigo_material')
    ->select(
        'ak.id',
        'ak.codigo_material',
        'ak.quantidade',
        'ak.palete_uid',
        'm.sku',
        'm.descricao',
        'm.ean'
    )
    ->where('ak.palete_uid', 'like', "KIT{$kit->id}-%")
    ->get();


return view('kit.etiquetas.imprimir_tudo', [
    'kit' => $kit,
    'apontamentos' => $apontamentos
]);


    if ($apontamentos->isEmpty()) {
        return back()->with('error', 'Nenhum apontamento encontrado para este kit.');
    }

    $zpls = [];
    $zplDir = storage_path("app/etiquetas/kits/{$kit_id}");

    foreach ($apontamentos as $apontamento) {
        $zplFile = "{$zplDir}/kit_{$apontamento->id}.zpl";

        if (file_exists($zplFile)) {
            $zpls[] = file_get_contents($zplFile);
        }
    }

    return view('kit.etiquetas.imprimir_tudo', [
    'kit' => $kit,
    'apontamentos' => $apontamentos
]);
}





    /**
     * Reimprimir uma etiqueta específica
     */
public function reimprimir($id)
{
    // Busca apontamento
    $etiqueta = DB::table('_tb_apontamentos_kits')->where('id', $id)->first();
    if (!$etiqueta) {
        return abort(404, 'Etiqueta não encontrada no banco!');
    }

    // Busca o kit relacionado pelo código_material
    $kit = DB::table('_tb_kit_montagem')
        ->where('codigo_material', $etiqueta->codigo_material)
        ->orderBy('id', 'desc') // pega o último programado desse material
        ->first();

    if (!$kit) {
        return abort(404, 'Kit relacionado não encontrado!');
    }

    // Caminho do PNG salvo
    $pngPath = storage_path("app/etiquetas_png/kits/{$kit->id}/kit_{$etiqueta->id}.png");

    if (!file_exists($pngPath)) {
        return abort(404, "PNG não encontrado em: {$pngPath}");
    }

    // Retorna a imagem no navegador
    return response()->file($pngPath, [
        'Content-Type' => 'image/png'
    ]);
}




    /**
     * Montar o ZPL com base nos dados da etiqueta
     */
    private function montarZpl($etiqueta)
    {
        return "^XA
^FO50,50^ADN,36,20^FD KIT {$etiqueta->id_kit} ^FS
^FO50,100^ADN,30,15^FD SKU: {$etiqueta->sku} ^FS
^FO50,150^ADN,30,15^FD Desc: {$etiqueta->descricao} ^FS
^FO50,200^ADN,30,15^FD Qtd: {$etiqueta->quantidade} ^FS
^FO50,250^ADN,30,15^FD Etiqueta {$etiqueta->numero_etiqueta}/{$etiqueta->total_etiquetas} ^FS
^XZ";
    }

    /**
     * Converter ZPL em PNG usando API do Labelary
     */
private function converterParaPng($zpl, $path)
{
    $ch = curl_init("http://api.labelary.com/v1/printers/8dpmm/labels/100x80/0/");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $zpl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: image/png"]);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $result) {
        Storage::put($path, $result);
    } else {
        \Log::error("Erro ao gerar PNG", ['http' => $httpCode, 'zpl' => $zpl]);
    }
}

public function criarEtiquetasDoKit($kitId, $quantidade)
{
    $totalEtiquetas = $quantidade;

    for ($i = 1; $i <= $totalEtiquetas; $i++) {
        // 1) Criar registro no banco
        $etiqueta = KitEtiqueta::create([
            'id_kit'          => $kitId,
            'sku'             => 'KIT-' . $kitId,
            'ean'             => '123456789012' . $i, // ajustar se tiver EAN real
            'descricao'       => 'Kit Programado ' . $kitId,
            'ua'              => 'UA01',
            'lastro'          => 1,
            'camada'          => 1,
            'paletizacao'     => 1,
            'numero_etiqueta' => $i,
            'total_etiquetas' => $totalEtiquetas,
            'data_geracao'    => now(),
            'quantidade'      => 1, // quantidade por etiqueta
        ]);

        // 2) Gerar ZPL
        $zpl = "^XA
^FO50,50^ADN,36,20^FD KIT {$etiqueta->id_kit} ^FS
^FO50,100^ADN,30,15^FD SKU: {$etiqueta->sku} ^FS
^FO50,150^ADN,30,15^FD DESC: {$etiqueta->descricao} ^FS
^FO50,200^ADN,30,15^FD ETQ {$etiqueta->numero_etiqueta}/{$etiqueta->total_etiquetas} ^FS
^XZ";

        // 3) Salvar arquivo .zpl
        $fileName = "etiqueta_{$etiqueta->id}.zpl";
        Storage::put("etiquetas/kits/{$kitId}/{$fileName}", $zpl);
    }

    \Log::info("Etiquetas do kit {$kitId} criadas no banco e salvas em ZPL", [
        'quantidade' => $totalEtiquetas
    ]);
}


}
