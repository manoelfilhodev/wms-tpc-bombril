<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreApontamentoPaleteStretchApiRequest;
use App\Models\ApontamentoPaleteStretch;
use Illuminate\Http\JsonResponse;

class ApontamentoPaleteStretchApiController extends Controller
{
    /**
     * Registra apontamento de palete com Stretch
     *
     * @group Apontamentos Paletes Stretch
     * @authenticated
     *
     * @bodyParam palete_codigo string required Codigo lido no app. Example: PAL123456
     * @bodyParam origem string Origem do apontamento (APP ou API). Example: APP
     * @bodyParam device_id string Identificador do dispositivo/coletor. Example: coletor-01
     * @bodyParam app_version string Versao do app. Example: 1.0.0
     * @bodyParam client_uuid string required UUID unico da operacao no app. Example: 6f3626c8-0a35-4e56-9b5d-3e7c3e7df4a1
     * @bodyParam apontado_em_app datetime Data/hora local do app em ISO 8601. Example: 2026-05-02T10:30:00-03:00
     * @bodyParam observacao string Observacao opcional. Example: Leitura manual
     */
    public function store(StoreApontamentoPaleteStretchApiRequest $request): JsonResponse
    {
        $dados = $request->validated();
        $user = $request->user();

        $existentePorUuid = ApontamentoPaleteStretch::query()
            ->where('client_uuid', $dados['client_uuid'])
            ->first();

        if ($existentePorUuid) {
            return response()->json([
                'success' => true,
                'message' => 'Apontamento ja registrado anteriormente.',
                'data' => $this->payload($existentePorUuid),
                'meta' => [
                    'idempotent' => true,
                ],
            ]);
        }

        $duplicado = ApontamentoPaleteStretch::query()
            ->where('palete_codigo', $dados['palete_codigo'])
            ->where('status', 'APONTADO')
            ->exists();

        if ($duplicado) {
            return response()->json([
                'success' => false,
                'message' => 'Este palete ja possui apontamento de Stretch ativo.',
                'data' => (object) [],
                'meta' => (object) [],
            ], 409);
        }

        $apontamento = ApontamentoPaleteStretch::create([
            'palete_codigo' => $dados['palete_codigo'],
            'usuario_id' => $user?->id_user,
            'unidade_id' => $user?->unidade_id,
            'status' => 'APONTADO',
            'origem' => $dados['origem'] ?? 'APP',
            'observacao' => $dados['observacao'] ?? null,
            'client_uuid' => $dados['client_uuid'],
            'device_id' => $dados['device_id'] ?? null,
            'app_version' => $dados['app_version'] ?? null,
            'apontado_em_app' => $dados['apontado_em_app'] ?? null,
            'apontado_em_servidor' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Palete apontado com sucesso.',
            'data' => $this->payload($apontamento),
            'meta' => (object) [],
        ], 201);
    }

    private function payload(ApontamentoPaleteStretch $apontamento): array
    {
        return [
            'id' => $apontamento->id,
            'palete_codigo' => $apontamento->palete_codigo,
            'status' => $apontamento->status,
            'origem' => $apontamento->origem,
            'usuario_id' => $apontamento->usuario_id,
            'unidade_id' => $apontamento->unidade_id,
            'device_id' => $apontamento->device_id,
            'app_version' => $apontamento->app_version,
            'client_uuid' => $apontamento->client_uuid,
            'apontado_em_app' => optional($apontamento->apontado_em_app)->toISOString(),
            'apontado_em_servidor' => optional($apontamento->apontado_em_servidor)->toISOString(),
        ];
    }
}
