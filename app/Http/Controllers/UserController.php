<?php

namespace App\Http\Controllers;

use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (session('tipo') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Acesso nao autorizado!');
        }

        $query = DB::table('_tb_usuarios')
            ->where('email', '!=', 'admin')
            ->where('id_user', '>', 5);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo')->value());
        }

        $usuarios = $query->orderBy('nome')->paginate(15);

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'login' => ['required', 'email', 'max:255', 'unique:_tb_usuarios,email'],
            'senha' => ['required', 'string', 'min:6'],
            'unidade' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['0', '1'])],
            'cod_nivel' => ['required', 'integer'],
            'desc_nivel' => ['required', 'string', 'max:255'],
        ]);

        $unidadeId = $this->resolveUnidadeId($validated['unidade']);

        if (! $unidadeId) {
            return back()
                ->withErrors(['unidade' => 'Unidade informada nao foi encontrada.'])
                ->withInput();
        }

        $usuarioId = DB::table('_tb_usuarios')->insertGetId([
            'nome' => $validated['nome'],
            'email' => mb_strtolower($validated['login']),
            'password' => Hash::make($validated['senha']),
            'unidade_id' => $unidadeId,
            'status' => $validated['status'] === '1' ? 'ativo' : 'inativo',
            'tipo' => $this->mapTipoFromNivel((int) $validated['cod_nivel']),
            'nivel' => $validated['desc_nivel'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'administracao',
            'action' => 'usuario_criado',
            'description' => "Usuário {$validated['nome']} criado.",
            'entity_type' => 'usuario',
            'entity_id' => $usuarioId,
            'new_values' => [
                'nome' => $validated['nome'],
                'email' => mb_strtolower($validated['login']),
                'unidade_id' => $unidadeId,
                'status' => $validated['status'] === '1' ? 'ativo' : 'inativo',
                'tipo' => $this->mapTipoFromNivel((int) $validated['cod_nivel']),
                'nivel' => $validated['desc_nivel'],
                'senha_definida' => true,
            ],
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario cadastrado com sucesso!');
    }

    public function edit(int $id)
    {
        $usuario = DB::table('_tb_usuarios')->where('id_user', $id)->first();

        if (! $usuario) {
            return redirect()->route('usuarios.index')->with('error', 'Usuario nao encontrado.');
        }

        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, int $id)
    {
        $usuario = DB::table('_tb_usuarios')->where('id_user', $id)->first();

        if (! $usuario) {
            return redirect()->route('usuarios.index')->with('error', 'Usuario nao encontrado.');
        }

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'login' => ['required', 'email', 'max:255', Rule::unique('_tb_usuarios', 'email')->ignore($id, 'id_user')],
            'unidade' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['0', '1'])],
            'tipo' => ['required', Rule::in(['admin', 'gestor', 'operador', 'supervisor'])],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $unidadeId = $this->resolveUnidadeId($validated['unidade']);

        if (! $unidadeId) {
            return back()
                ->withErrors(['unidade' => 'Unidade informada nao foi encontrada.'])
                ->withInput();
        }

        $payload = [
            'nome' => $validated['nome'],
            'email' => mb_strtolower($validated['login']),
            'unidade_id' => $unidadeId,
            'status' => $validated['status'] === '1' ? 'ativo' : 'inativo',
            'tipo' => $validated['tipo'] === 'supervisor' ? 'gestor' : $validated['tipo'],
            'updated_at' => now(),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        DB::table('_tb_usuarios')->where('id_user', $id)->update($payload);

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'administracao',
            'action' => 'usuario_atualizado',
            'description' => "Usuário {$payload['nome']} atualizado.",
            'entity_type' => 'usuario',
            'entity_id' => $id,
            'old_values' => (array) $usuario,
            'new_values' => array_merge($payload, [
                'password' => null,
                'senha_alterada' => ! empty($validated['password']),
            ]),
        ]);

        if (($usuario->tipo ?? null) !== $payload['tipo'] || ($usuario->status ?? null) !== $payload['status']) {
            SystemLogService::record([
                ...$this->systemLogActor(),
                'module' => 'administracao',
                'action' => 'usuario_permissao_alterada',
                'description' => "Perfil/status do usuário {$payload['nome']} alterado.",
                'entity_type' => 'usuario',
                'entity_id' => $id,
                'old_values' => [
                    'tipo' => $usuario->tipo ?? null,
                    'nivel' => $usuario->nivel ?? null,
                    'status' => $usuario->status ?? null,
                ],
                'new_values' => [
                    'tipo' => $payload['tipo'],
                    'nivel' => $usuario->nivel ?? null,
                    'status' => $payload['status'],
                ],
            ]);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario atualizado com sucesso!');
    }

    public function destroy(int $id)
    {
        $usuario = DB::table('_tb_usuarios')->where('id_user', $id)->first();

        DB::table('_tb_usuarios')->where('id_user', $id)->delete();

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'administracao',
            'action' => 'usuario_excluido',
            'description' => 'Usuário excluído.',
            'entity_type' => 'usuario',
            'entity_id' => $id,
            'old_values' => $usuario ? (array) $usuario : null,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario excluido com sucesso!');
    }

    private function resolveUnidadeId(string $input): ?int
    {
        if (is_numeric($input)) {
            $id = (int) $input;
            $exists = DB::table('_tb_unidades')->where('id', $id)->exists();

            return $exists ? $id : null;
        }

        $id = DB::table('_tb_unidades')
            ->where('nome', $input)
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function mapTipoFromNivel(int $nivel): string
    {
        return match ($nivel) {
            1 => 'admin',
            2, 3, 4 => 'gestor',
            default => 'operador',
        };
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

    public function buscarSeparadores(Request $request)
    {
        $q = trim($request->get('q', ''));

        $cadastrados = DB::table('_tb_separadores')
            ->select(['id', 'chapa', 'nome', 'cargo', 'turno'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nome', 'like', "%{$q}%")
                        ->orWhere('chapa', 'like', "%{$q}%")
                        ->orWhere('cargo', 'like', "%{$q}%")
                        ->orWhere('turno', 'like', "%{$q}%");
                });
            })
            ->orderBy('nome')
            ->limit(20)
            ->get()
            ->map(fn ($separador) => [
                'id' => $separador->id,
                'chapa' => $separador->chapa,
                'nome' => $separador->nome,
                'cargo' => $separador->cargo ?: 'Separador',
                'turno' => $separador->turno,
                'origem' => 'cadastro',
            ]);

        $historico = DB::table('_tb_demanda_distribuicoes')
            ->selectRaw('MIN(id) as id, separador_nome as nome')
            ->whereNotNull('separador_nome')
            ->whereRaw("TRIM(separador_nome) <> ''")
            ->when($q !== '', fn ($query) => $query->where('separador_nome', 'like', "%{$q}%"))
            ->groupBy('separador_nome')
            ->orderBy('separador_nome')
            ->limit(20)
            ->get()
            ->map(fn ($separador) => [
                'id' => 'historico-' . $separador->id,
                'chapa' => 'historico',
                'nome' => $separador->nome,
                'cargo' => 'Separador',
                'turno' => null,
                'origem' => 'historico',
            ]);

        $separadores = $cadastrados
            ->concat($historico)
            ->unique(fn ($separador) => mb_strtoupper($separador['nome']))
            ->sortBy('nome')
            ->take(20)
            ->values();

        return response()->json($separadores);
    }
}
