<?php

namespace App\Exports;

use App\Models\KitMontagem;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KitMontagemExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = KitMontagem::query();

        if ($this->request->filled('data_inicio')) {
            $query->whereDate('data_montagem', '>=', $this->request->data_inicio);
        }

        if ($this->request->filled('data_fim')) {
            $query->whereDate('data_montagem', '<=', $this->request->data_fim);
        }

        if ($this->request->filled('sku')) {
            $query->where('codigo_material', 'like', '%' . $this->request->sku . '%');
        }

        return $query->orderBy('data_montagem', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Data Montagem',
            'SKU',
            'Programado',
            'Produzido',
            '% Realizado'
        ];
    }

    public function map($kit): array
    {
        $realizado = $kit->quantidade_programada > 0
            ? round(($kit->quantidade_produzida ?? 0) / $kit->quantidade_programada * 100, 1)
            : 0;

        return [
            \Carbon\Carbon::parse($kit->data_montagem)->format('d/m/Y'),
            strtoupper($kit->codigo_material),
            $kit->quantidade_programada,
            $kit->quantidade_produzida ?? 0,
            "{$realizado}%",
        ];
    }
}
