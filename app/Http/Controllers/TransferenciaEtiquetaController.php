<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Transferencia;
use App\Models\TransferenciaEtiqueta;

class TransferenciaEtiquetaController extends Controller
{
    /**
     * Visualizar todas as etiquetas de uma transferência
     */
    public function index()
    {
        $transferencias = Transferencia::orderBy('created_at', 'desc')->get();
        return view('transferencia.etiquetas.index', compact('transferencias'));
    }

    public function visualizar($id)
    {
        $transferencia = Transferencia::findOrFail($id);

        $etiquetas = DB::table('_tb_apontamentos_transferencia')
            ->where('codigo_material', $transferencia->codigo_material)
            ->orderBy('id', 'desc')
            ->get();

        return view('transferencia.etiquetas.visualizar', compact('transferencia', 'etiquetas'));
    }

    /**
     * Imprimir todas as etiquetas
     */
    public function imprimirTudo($transferencia_id)
    {
        $transferencia = Transferencia::findOrFail($transferencia_id);

        $apontamentos = DB::table('_tb_apontamentos_transferencia as at')
            ->join('_tb_materiais as m', 'm.sku', '=', 'at.codigo_material')
            ->select(
                'at.id',
                'at.codigo_material',
                'at.quantidade',
                'at.palete_uid',
                'm.sku',
                'm.descricao',
                'm.ean'
            )
            ->where('at.palete_uid', 'like', "TRF{$transferencia->id}-%")
            ->get();

        return view('transferencia.etiquetas.imprimir_tudo', [
            'transferencia' => $transferencia,
            'apontamentos'  => $apontamentos
        ]);

        if ($apontamentos->isEmpty()) {
            return back()->with('error', 'Nenhum apontamento encontrado para esta transferência.');
        }

        $zpls = [];
        $zplDir = storage_path("app/etiquetas/transferencias/{$transferencia_id}");

        foreach ($apontamentos as $apontamento) {
            $zplFile = "{$zplDir}/transferencia_{$apontamento->id}.zpl";

            if (file_exists($zplFile)) {
                $zpls[] = file_get_contents($zplFile);
            }
        }

        return view('transferencia.etiquetas.imprimir_tudo', [
            'transferencia' => $transferencia,
            'apontamentos'  => $apontamentos
        ]);
    }

    /**
     * Reimprimir uma etiqueta específica
     */
    public function reimprimir($id)
    {
        $etiqueta = DB::table('_tb_apontamentos_transferencia')->where('id', $id)->first();
        if (!$etiqueta) {
            return abort(404, 'Etiqueta não encontrada no banco!');
        }

        $transferencia = DB::table('_tb_transferencia')
            ->where('codigo_material', $etiqueta->codigo_material)
            ->orderBy('id', 'desc')
            ->first();

        if (!$transferencia) {
            return abort(404, 'Transferência relacionada não encontrada!');
        }

        $pngPath = storage_path("app/etiquetas_png/transferencias/{$transferencia->id}/transferencia_{$etiqueta->id}.png");

        if (!file_exists($pngPath)) {
            return abort(404, "PNG não encontrado em: {$pngPath}");
        }

        return response()->file($pngPath, [
            'Content-Type' => 'image/png'
        ]);
    }

    /**
     * Montar ZPL
     */
    private function montarZpl($etiqueta)
    {
        return "^XA
^FO50,50^ADN,36,20^FD TRF {$etiqueta->id_transferencia} ^FS
^FO50,100^ADN,30,15^FD SKU: {$etiqueta->sku} ^FS
^FO50,150^ADN,30,15^FD Desc: {$etiqueta->descricao} ^FS
^FO50,200^ADN,30,15^FD Qtd: {$etiqueta->quantidade} ^FS
^FO50,250^ADN,30,15^FD Etiqueta {$etiqueta->numero_etiqueta}/{$etiqueta->total_etiquetas} ^FS
^XZ";
    }

    /**
     * Converter ZPL em PNG via Labelary
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

    public function criarEtiquetasDaTransferencia($transferenciaId, $quantidade)
    {
        $totalEtiquetas = $quantidade;

        for ($i = 1; $i <= $totalEtiquetas; $i++) {
            $etiqueta = TransferenciaEtiqueta::create([
                'id_transferencia' => $transferenciaId,
                'sku'              => 'TRF-' . $transferenciaId,
                'ean'              => '123456789012' . $i,
                'descricao'        => 'Transferência Programada ' . $transferenciaId,
                'ua'               => 'UA01',
                'lastro'           => 1,
                'camada'           => 1,
                'paletizacao'      => 1,
                'numero_etiqueta'  => $i,
                'total_etiquetas'  => $totalEtiquetas,
                'data_geracao'     => now(),
                'quantidade'       => 1,
            ]);

            $zpl = "^XA
^FO50,50^ADN,36,20^FD TRF {$etiqueta->id_transferencia} ^FS
^FO50,100^ADN,30,15^FD SKU: {$etiqueta->sku} ^FS
^FO50,150^ADN,30,15^FD DESC: {$etiqueta->descricao} ^FS
^FO50,200^ADN,30,15^FD ETQ {$etiqueta->numero_etiqueta}/{$etiqueta->total_etiquetas} ^FS
^XZ";

            $fileName = "etiqueta_{$etiqueta->id}.zpl";
            Storage::put("etiquetas/transferencias/{$transferenciaId}/{$fileName}", $zpl);
        }

        \Log::info("Etiquetas da transferência {$transferenciaId} criadas no banco e salvas em ZPL", [
            'quantidade' => $totalEtiquetas
        ]);
    }
}
