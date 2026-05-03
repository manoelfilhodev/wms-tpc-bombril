<?php

namespace App\Exports;

use App\Models\Demanda;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DemandasExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Demanda::query();

        if ($this->request->filled('fo')) {
            $query->where('fo', 'like', "%{$this->request->fo}%");
        }

        if ($this->request->filled('transportadora')) {
            $query->where('transportadora', 'like', "%{$this->request->transportadora}%");
        }

        if ($this->request->filled('cliente')) {
            $query->where('cliente', 'like', "%{$this->request->cliente}%");
        }

        if ($this->request->filled('tipo')) {
            $query->where('tipo', $this->request->tipo);
        }

        if ($this->request->filled('data_inicio') && $this->request->filled('data_fim')) {
            $query->whereBetween('created_at', [
                $this->request->data_inicio,
                $this->request->data_fim
            ]);
        }

        return $query->get([
            'fo',
            'cliente',
            'transportadora',
            'doca',
            'tipo',
            'quantidade',
            'peso',
            'valor_carga',
            'hora_agendada',
            'entrada',
            'saida',
            'status',
            'created_at',
        ]);
    }

    public function headings(): array
    {
        return [
            'FO',
            'Cliente',
            'Transportadora',
            'Doca',
            'Tipo',
            'Quantidade',
            'Peso',
            'Valor Carga',
            'Hora Agendada',
            'Entrada',
            'Saída',
            'Status',
            'Criado em',
        ];
    }
}
