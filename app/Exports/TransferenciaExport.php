<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;

class TransferenciaExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = DB::table('_tb_transferencias as t')
            ->select(
                't.codigo_material',
                't.quantidade_programada',
                DB::raw('COALESCE(SUM(a.quantidade),0) as quantidade_apontada'),
                't.data_transferencia',
                't.created_at',
                't.updated_at'
            )
            ->leftJoin('_tb_apontamentos_transferencia as a', function ($join) {
                $join->on('t.codigo_material', '=', 'a.codigo_material')
                     ->on('t.data_transferencia', '=', 'a.data');
            })
            ->where('a.status', 'APONTADO')
            ->groupBy(
                't.codigo_material',
                't.quantidade_programada',
                't.data_transferencia',
                't.created_at',
                't.updated_at'
            );

        if ($this->request->filled('data_inicio')) {
            $query->whereDate('t.data_transferencia', '>=', $this->request->data_inicio);
        }

        if ($this->request->filled('data_fim')) {
            $query->whereDate('t.data_transferencia', '<=', $this->request->data_fim);
        }

        if ($this->request->filled('sku')) {
            $query->where('t.codigo_material', 'like', '%' . $this->request->sku . '%');
        }

        return $query->orderBy('t.data_transferencia', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'C¨®digo Material',
            'Qtd Programada',
            'Qtd Apontada',
            'Data Transfer¨ºncia',
            'Criado em',
            'Atualizado em'
        ];
    }
}
