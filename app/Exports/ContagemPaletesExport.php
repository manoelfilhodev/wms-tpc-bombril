<?php

namespace App\Exports;

use App\Models\ContagemPalete;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContagemPaletesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return ContagemPalete::with('usuario')
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'Tipo de Palete' => $item->tipo_palete,
                    'Quantidade' => $item->quantidade,
                    'Responsável' => $item->usuario->nome ?? 'N/A',
                    'Data' => \Carbon\Carbon::parse($item->data_contagem)->format('d/m/Y H:i'),
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Tipo de Palete', 'Quantidade', 'Responsável', 'Data'];
    }
}
