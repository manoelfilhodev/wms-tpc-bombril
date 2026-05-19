<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::query();
        $this->applyFilters($query, $request);

        $direction = $request->input('ordem') === 'asc' ? 'asc' : 'desc';
        $logs = $query
            ->orderBy('created_at', $direction)
            ->paginate(20)
            ->withQueryString();

        $usuarios = User::query()
            ->orderBy('nome')
            ->get(['id_user', 'nome', 'email']);

        $modulos = SystemLog::query()
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $acoes = SystemLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $hoje = Carbon::today();
        $resumo = [
            'total' => SystemLog::count(),
            'hoje' => SystemLog::whereDate('created_at', $hoje)->count(),
            'usuarios_ativos_hoje' => SystemLog::whereDate('created_at', $hoje)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id'),
            'criticas' => SystemLog::whereIn('module', ['administracao', 'sistema'])
                ->orWhere('action', 'like', '%falha%')
                ->orWhere('action', 'like', '%erro%')
                ->orWhere('action', 'like', '%exclus%')
                ->count(),
        ];

        return view('admin.logs.index', compact('logs', 'usuarios', 'modulos', 'acoes', 'resumo'));
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('data_inicio')) {
            $query->where('created_at', '>=', Carbon::parse($request->data_inicio)->startOfDay());
        }

        if ($request->filled('data_fim')) {
            $query->where('created_at', '<=', Carbon::parse($request->data_fim)->endOfDay());
        }

        if ($request->filled('usuario')) {
            $query->where('user_id', $request->usuario);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                    ->orWhere('user_name', 'like', "%{$term}%")
                    ->orWhere('user_email', 'like', "%{$term}%")
                    ->orWhere('entity_type', 'like', "%{$term}%")
                    ->orWhere('entity_id', 'like', "%{$term}%")
                    ->orWhere('ip_address', 'like', "%{$term}%")
                    ->orWhere('route', 'like', "%{$term}%");
            });
        }
    }
}
