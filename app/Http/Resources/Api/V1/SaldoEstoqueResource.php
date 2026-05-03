<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaldoEstoqueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku_id' => $this->sku_id,
            'material_id' => $this->material_id,
            'sku' => $this->sku,
            'descricao' => $this->descricao,
            'material_nome' => $this->material_nome,
            'posicao_id' => $this->posicao_id,
            'posicao_codigo' => $this->posicao_codigo,
            'unidade_id' => $this->unidade_id,
            'unidade_nome' => $this->unidade_nome,
            'quantidade' => $this->quantidade,
            'data_entrada' => $this->data_entrada,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
