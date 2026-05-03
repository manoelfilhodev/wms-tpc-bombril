<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RecebimentoEtiquetaController extends Controller
{
    public function imprimirTudo($recebimento_id)
    {
        \Log::info("Método imprimirTudo foi chamado para o recebimento ID: {$recebimento_id}");

        // Buscar todas as etiquetas do recebimento
        $etiquetas = DB::table('_tb_recebimento_etiquetas')
            ->where('id_recebimento', $recebimento_id)
            ->get();

        \Log::info("Total de etiquetas no banco", ['count' => $etiquetas->count()]);

        if ($etiquetas->isEmpty()) {
            return back()->with('error', 'Nenhuma etiqueta encontrada para este recebimento.');
        }

        $imagens = [];
        $cacheDir = storage_path("app/etiquetas_png");

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        foreach ($etiquetas as $etiqueta) {
            $cacheFile = "{$cacheDir}/{$etiqueta->ua}.png";

            // Se já tiver no cache, usa direto
            if (file_exists($cacheFile)) {
                $imagens[] = 'data:image/png;base64,' . base64_encode(file_get_contents($cacheFile));
                continue;
            }

            // Arquivo ZPL original
            $zplFile = storage_path("app/etiquetas/{$etiqueta->ua}.zpl");

            if (!file_exists($zplFile)) {
                \Log::warning("Arquivo ZPL não encontrado", ['path' => $zplFile]);
                continue;
            }

            $zpl = file_get_contents($zplFile);

            // Chamada à API do Labelary
            $response = Http::withHeaders(['Accept' => 'image/png'])
                ->timeout(10)
                ->retry(3, 500) // tenta 3x com 500ms de intervalo
                ->send('POST', 'http://api.labelary.com/v1/printers/8dpmm/labels/4x6/0/', [
                    'body' => $zpl
                ]);

            if ($response->successful()) {
                // Salva no cache
                file_put_contents($cacheFile, $response->body());
                $imagens[] = 'data:image/png;base64,' . base64_encode($response->body());
            } else {
                \Log::error("Erro ao converter ZPL para imagem", [
                    'ua' => $etiqueta->ua,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            // Delay de 0.5s para evitar limite da API
            usleep(500000);
        }

        \Log::info("Total de imagens geradas", ['count' => count($imagens)]);

        return view('setores.recebimento.visualizar_etiquetas', compact('imagens'));
    }

    public function reimprimir($item_id)
    {
        $etiqueta = DB::table('_tb_recebimento_etiquetas')
            ->where('id', $item_id)
            ->first();

        if (!$etiqueta) {
            return back()->with('error', 'Etiqueta não encontrada.');
        }

        $filePath = storage_path("app/etiquetas/{$etiqueta->ua}.zpl");
        if (!file_exists($filePath)) {
            return back()->with('error', 'Arquivo ZPL não encontrado.');
        }

        $zplOutput = file_get_contents($filePath);

        return response($zplOutput)->header('Content-Type', 'text/plain');
    }
}
