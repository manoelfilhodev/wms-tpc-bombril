<?php

namespace App\Exports;

use App\Models\UserLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LogsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $logs;

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs->map(function ($log) {
            return [
                'Usuário' => $log->usuario->nome ?? '---',
                'Unidade' => $log->unidade->nome ?? '---',
                'Ação' => $log->acao,
                'Dados' => $log->dados,
                'IP' => $log->ip_address,
                'Navegador' => $log->navegador,
                'Data/Hora' => \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Usuário',
            'Unidade',
            'Ação',
            'Dados',
            'IP',
            'Navegador',
            'Data/Hora',
        ];
    }
}

