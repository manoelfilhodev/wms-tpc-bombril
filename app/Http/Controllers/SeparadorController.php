<?php

namespace App\Http\Controllers;

use App\Models\Separador;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SeparadorController extends Controller
{
    public function index(Request $request)
    {
        $query = Separador::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($query) use ($search) {
                $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('chapa', 'like', "%{$search}%")
                    ->orWhere('cargo', 'like', "%{$search}%")
                    ->orWhere('turno', 'like', "%{$search}%");
            });
        }

        $separadores = $query->orderBy('nome')->paginate(15);

        return view('separadores.index', compact('separadores'));
    }

    public function create()
    {
        return view('separadores.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'chapa' => ['required', 'string', 'max:50', 'unique:_tb_separadores,chapa'],
            'nome' => ['required', 'string', 'max:150'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'turno' => ['nullable', 'string', 'max:50'],
        ]);

        $separador = Separador::create($this->normalizarDados($dados));

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'administracao',
            'action' => 'separador_criado',
            'description' => "Separador {$separador->nome} criado.",
            'entity_type' => 'separador',
            'entity_id' => $separador->id,
            'new_values' => $separador->only(['chapa', 'nome', 'cargo', 'turno']),
        ]);

        return redirect()->route('separadores.index')->with('success', 'Separador cadastrado com sucesso!');
    }

    public function edit(Separador $separador)
    {
        return view('separadores.edit', compact('separador'));
    }

    public function update(Request $request, Separador $separador)
    {
        $dados = $request->validate([
            'chapa' => ['required', 'string', 'max:50', Rule::unique('_tb_separadores', 'chapa')->ignore($separador->id)],
            'nome' => ['required', 'string', 'max:150'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'turno' => ['nullable', 'string', 'max:50'],
        ]);

        $oldValues = $separador->only(['chapa', 'nome', 'cargo', 'turno']);
        $separador->update($this->normalizarDados($dados));

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'administracao',
            'action' => 'separador_atualizado',
            'description' => "Separador {$separador->nome} atualizado.",
            'entity_type' => 'separador',
            'entity_id' => $separador->id,
            'old_values' => $oldValues,
            'new_values' => $separador->fresh()->only(['chapa', 'nome', 'cargo', 'turno']),
        ]);

        return redirect()->route('separadores.index')->with('success', 'Separador atualizado com sucesso!');
    }

    public function destroy(Separador $separador)
    {
        $oldValues = $separador->only(['chapa', 'nome', 'cargo', 'turno']);
        $separadorId = $separador->id;
        $separadorNome = $separador->nome;

        $separador->delete();

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'administracao',
            'action' => 'separador_excluido',
            'description' => "Separador {$separadorNome} excluído.",
            'entity_type' => 'separador',
            'entity_id' => $separadorId,
            'old_values' => $oldValues,
        ]);

        return redirect()->route('separadores.index')->with('success', 'Separador excluído com sucesso!');
    }

    private function normalizarDados(array $dados): array
    {
        return [
            'chapa' => trim($dados['chapa']),
            'nome' => trim($dados['nome']),
            'cargo' => filled($dados['cargo'] ?? null) ? trim($dados['cargo']) : null,
            'turno' => filled($dados['turno'] ?? null) ? trim($dados['turno']) : null,
        ];
    }

    private function systemLogActor(): array
    {
        $user = Auth::user();

        return [
            'user_id' => $user?->id_user ?? session('user_id'),
            'user_name' => $user?->nome ?? session('nome'),
            'user_email' => $user?->email,
            'user_role' => trim(implode(' / ', array_filter([
                $user?->tipo ?? session('tipo'),
                $user?->nivel ?? session('nivel'),
            ]))) ?: null,
        ];
    }
}
