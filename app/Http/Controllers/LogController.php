<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLog;
use App\Models\Unidade;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LogsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->filtrarLogs($request)->paginate(10);
        $unidades = Unidade::orderBy('nome')->get();
        $usuarios = User::orderBy('nome')->get();

        return view('logs.index', compact('logs', 'unidades', 'usuarios'));
    }

    public function exportExcel(Request $request)
    {
        $logs = $this->filtrarLogs($request)->get();
        $filename = 'logs_' . now()->format('Y-m-d_H-i') . '.xlsx';
    
        return Excel::download(new LogsExport($logs), $filename);
    }


    public function exportPDF(Request $request)
    {
        $logs = $this->filtrarLogs($request)->get();
        $filename = 'logs_' . now()->format('Y-m-d_H-i') . '.pdf';
    
        $pdf = Pdf::loadView('logs.pdf', compact('logs'));
        return $pdf->download($filename);
    }   


    private function filtrarLogs(Request $request)
    {
        $query = UserLog::with(['usuario', 'unidade']);

        if ($request->filled('usuario')) {
            $query->whereHas('usuario', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->usuario . '%');
            });
        }

        if ($request->filled('acao')) {
            $query->where('acao', 'like', '%' . $request->acao . '%');
        }

        if ($request->filled('data')) {
            $query->whereDate('created_at', $request->data);
        }

        if ($request->filled('unidade')) {
            $query->whereHas('unidade', function ($q) use ($request) {
                $q->where('nome', $request->unidade);
            });
        }

        return $query->orderBy('created_at', 'desc');
    }
}
