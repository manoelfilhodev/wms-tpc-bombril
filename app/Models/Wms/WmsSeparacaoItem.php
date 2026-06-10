<?php

namespace App\Models\Wms;

use App\Models\Demanda;
use Illuminate\Database\Eloquent\Model;

class WmsSeparacaoItem extends Model
{
    protected $table = '_tb_wms_separacao_itens';

    protected $fillable = [
        'geracao_id',
        'folha_id',
        'demanda_id',
        'fo',
        'sku_id',
        'posicao_id',
        'sku',
        'descricao',
        'curva_abc',
        'rua',
        'posicao',
        'endereco',
        'lado',
        'quantidade',
        'sequencia_rota',
        'ordem_separacao',
        'status',
        'observacao',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'sequencia_rota' => 'integer',
        'ordem_separacao' => 'integer',
    ];

    public function geracao()
    {
        return $this->belongsTo(WmsSeparacaoGeracao::class, 'geracao_id');
    }

    public function folha()
    {
        return $this->belongsTo(WmsSeparacaoFolha::class, 'folha_id');
    }

    public function demanda()
    {
        return $this->belongsTo(Demanda::class, 'demanda_id');
    }

    public function skuCadastro()
    {
        return $this->belongsTo(WmsSku::class, 'sku_id');
    }

    public function posicaoCadastro()
    {
        return $this->belongsTo(WmsPosicao::class, 'posicao_id');
    }
}
