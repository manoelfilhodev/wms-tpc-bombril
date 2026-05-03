<?php

namespace App\Exports;

use App\Models\Equipamento;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EquipamentosExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Equipamento::select(
            'tipo',
            'modelo',
            'patrimonio',
            'numero_serie',
            'status',
            'localizacao',
            'responsavel',
            'data_aquisicao',
            'observacoes'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Tipo',
            'Modelo',
            'Patrimônio',
            'Número de Série',
            'Status',
            'Localização',
            'Responsável',
            'Data de Aquisição',
            'Observações'
        ];
    }
}
