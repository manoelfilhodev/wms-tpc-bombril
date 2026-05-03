<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventarioExport implements FromCollection, WithHeadings
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function collection()
{
    return DB::table('_tb_inventario_itens')
        ->leftJoin('_tb_usuarios', '_tb_usuarios.id_user', '=', '_tb_inventario_itens.contado_por')
        ->where('id_inventario', $this->id)
        ->select(
            '_tb_inventario_itens.sku',
            '_tb_inventario_itens.descricao',
            '_tb_inventario_itens.posicao',
            '_tb_inventario_itens.quantidade_sistema',
            '_tb_inventario_itens.quantidade_fisica',
            '_tb_inventario_itens.tipo_ajuste',
            '_tb_inventario_itens.necessita_ajuste',
            '_tb_usuarios.nome as usuario',
            '_tb_inventario_itens.updated_at as data_contagem'
        )
        ->get();
}

public function headings(): array
{
    return [
        'SKU',
        'Descrição',
        'Posição',
        'Qtd Sistema',
        'Qtd Física',
        'Tipo Ajuste',
        'Necessita Ajuste',
        'Usuário',
        'Data/Hora Contagem',
    ];
}

}
