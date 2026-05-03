<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ArmazenagemExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $dados;

    public function __construct($dados)
    {
        $this->dados = $dados;
    }

    public function collection()
    {
        return $this->dados->map(function ($item) {
            return [
                'Data' => date('d/m/Y', strtotime($item->data_armazenagem)),
                'Usuário' => mb_strtoupper($item->usuario_nome),
                'Unidade' => $item->unidade_nome,
                'SKU' => mb_strtoupper($item->sku),
                'Posição' => mb_strtoupper($item->endereco),
                'Quantidade' => $item->quantidade,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Data',
            'Usuário',
            'Unidade',
            'SKU',
            'Posição',
            'Quantidade',
        ];
    }
}