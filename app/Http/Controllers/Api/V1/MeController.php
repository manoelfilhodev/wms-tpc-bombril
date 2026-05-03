<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MeController extends Controller
{
    use ApiResponseTrait;

    /**
     * Retorna o usuario autenticado
     *
     * @group Auth
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Usuario autenticado.",
     *   "data": {
     *     "id": 1,
     *     "nome": "Nome do Usuario",
     *     "email": "usuario@empresa.com",
     *     "tipo": "admin",
     *     "unidade_id": 1,
     *     "nivel": 1
     *   },
     *   "meta": {}
     * }
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id_user ?? $user->id,
            'nome' => $user->nome,
            'email' => $user->email,
            'tipo' => $user->tipo ?? null,
            'unidade_id' => $user->unidade_id ?? null,
            'nivel' => $user->nivel ?? null,
        ], 'Usuario autenticado.');
    }
}
