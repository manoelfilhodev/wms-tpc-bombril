<?php

namespace App\Models\Wms;

use App\Models\Demanda;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WmsSeparacaoGeracao extends Model
{
    protected $table = '_tb_wms_separacao_geracoes';

    protected $fillable = [
        'demanda_id',
        'fo',
        'criterio_agrupamento',
        'criterio_ordenacao',
        'criterio_equalizacao',
        'quantidade_separadores',
        'total_itens',
        'total_skus',
        'total_ruas',
        'itens_sem_endereco',
        'status',
        'gerado_por',
    ];

    protected $casts = [
        'quantidade_separadores' => 'integer',
        'total_itens' => 'integer',
        'total_skus' => 'integer',
        'total_ruas' => 'integer',
        'itens_sem_endereco' => 'integer',
    ];

    public function demanda()
    {
        return $this->belongsTo(Demanda::class, 'demanda_id');
    }

    public function folhas()
    {
        return $this->hasMany(WmsSeparacaoFolha::class, 'geracao_id');
    }

    public function itens()
    {
        return $this->hasMany(WmsSeparacaoItem::class, 'geracao_id');
    }

    public function gerador()
    {
        return $this->belongsTo(User::class, 'gerado_por', 'id_user');
    }
}
