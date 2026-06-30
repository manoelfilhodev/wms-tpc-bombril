<?php

namespace App\Http\Controllers;

use App\Models\ClienteTransitTime;
use App\Services\ImportacaoClientesTransitTimeService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClienteTransitTimeController extends Controller
{
    public function index(Request $request)
    {
        $query = ClienteTransitTime::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();

            $query->where(function ($query) use ($search) {
                $query->where('codigo_cliente', 'like', "%{$search}%")
                    ->orWhere('nome_cliente', 'like', "%{$search}%")
                    ->orWhere('zona_partida', 'like', "%{$search}%")
                    ->orWhere('regiao', 'like', "%{$search}%")
                    ->orWhere('uf', 'like', "%{$search}%")
                    ->orWhere('cidade', 'like', "%{$search}%")
                    ->orWhere('zona_transporte', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->input('status') === 'ativo');
        }

        $clientes = $query
            ->orderBy('nome_cliente')
            ->orderBy('codigo_cliente')
            ->paginate(15);

        return view('clientes-transit-time.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes-transit-time.create');
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);
        $cliente = ClienteTransitTime::create($this->normalizarDados($dados));

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'parametros_logisticos',
            'action' => 'cliente_transit_time_criado',
            'description' => "Transit-time do cliente {$cliente->codigo_cliente} criado.",
            'entity_type' => 'cliente_transit_time',
            'entity_id' => $cliente->id,
            'new_values' => $cliente->toArray(),
        ]);

        return redirect()
            ->route('clientes-transit-time.index')
            ->with('success', 'Cliente cadastrado na base de transit-time com sucesso.');
    }

    public function edit(ClienteTransitTime $clienteTransitTime)
    {
        return view('clientes-transit-time.edit', compact('clienteTransitTime'));
    }

    public function update(Request $request, ClienteTransitTime $clienteTransitTime)
    {
        $dados = $this->validar($request, $clienteTransitTime);
        $oldValues = $clienteTransitTime->toArray();

        $clienteTransitTime->update($this->normalizarDados($dados, $clienteTransitTime->ativo));

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'parametros_logisticos',
            'action' => 'cliente_transit_time_atualizado',
            'description' => "Transit-time do cliente {$clienteTransitTime->codigo_cliente} atualizado.",
            'entity_type' => 'cliente_transit_time',
            'entity_id' => $clienteTransitTime->id,
            'old_values' => $oldValues,
            'new_values' => $clienteTransitTime->fresh()->toArray(),
        ]);

        return redirect()
            ->route('clientes-transit-time.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function toggle(ClienteTransitTime $clienteTransitTime)
    {
        $oldValues = $clienteTransitTime->only(['ativo']);
        $clienteTransitTime->ativo = ! $clienteTransitTime->ativo;
        $clienteTransitTime->save();

        SystemLogService::record([
            ...$this->systemLogActor(),
            'module' => 'parametros_logisticos',
            'action' => 'cliente_transit_time_status_alterado',
            'description' => "Status do cliente {$clienteTransitTime->codigo_cliente} alterado.",
            'entity_type' => 'cliente_transit_time',
            'entity_id' => $clienteTransitTime->id,
            'old_values' => $oldValues,
            'new_values' => $clienteTransitTime->only(['ativo']),
        ]);

        $status = $clienteTransitTime->ativo ? 'reativado' : 'inativado';

        return back()->with('success', "Cliente {$status} com sucesso.");
    }

    public function importForm()
    {
        return view('clientes-transit-time.import');
    }

    public function import(Request $request, ImportacaoClientesTransitTimeService $service)
    {
        $request->validate([
            'arquivo' => [
                'required',
                'file',
                'max:10240',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $extensoesPermitidas = ['xlsx', 'xls', 'csv'];
                    $mimesPermitidos = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'application/zip',
                        'application/octet-stream',
                        'text/csv',
                        'text/plain',
                    ];

                    $extensao = strtolower((string) $value->getClientOriginalExtension());
                    $mime = (string) $value->getMimeType();

                    if (! in_array($extensao, $extensoesPermitidas, true)) {
                        $fail('O arquivo deve estar nos formatos .xlsx, .xls ou .csv.');
                    }

                    if (! in_array($mime, $mimesPermitidos, true)) {
                        $fail('O MIME do arquivo enviado não é permitido.');
                    }
                },
            ],
        ], [
            'arquivo.required' => 'Selecione a base de transit-time para importar.',
            'arquivo.file' => 'O upload enviado não é um arquivo válido.',
            'arquivo.max' => 'O arquivo deve ter no máximo 10 MB.',
        ]);

        try {
            $resumo = $service->importar($request->file('arquivo'));

            SystemLogService::record([
                ...$this->systemLogActor(),
                'module' => 'parametros_logisticos',
                'action' => 'clientes_transit_time_importados',
                'description' => 'Usuário importou a base de transit-time por cliente.',
                'entity_type' => 'cliente_transit_time',
                'new_values' => [
                    'arquivo' => $request->file('arquivo')?->getClientOriginalName(),
                    'resumo' => $resumo,
                ],
            ]);

            if (($resumo['total_lidas'] ?? 0) > 0 && ($resumo['erros'] ?? 0) > 0 && (($resumo['criadas'] ?? 0) + ($resumo['atualizadas'] ?? 0)) === 0) {
                return back()
                    ->with('error', 'A importação foi processada, mas nenhuma linha foi gravada. Confira o resumo de erros abaixo.')
                    ->with('clientes_transit_time_importacao_resumo', $resumo);
            }

            return back()
                ->with('success', 'Importação da base de transit-time concluída.')
                ->with('clientes_transit_time_importacao_resumo', $resumo);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    private function validar(Request $request, ?ClienteTransitTime $cliente = null): array
    {
        return $request->validate([
            'codigo_cliente' => [
                'required',
                'string',
                'max:50',
                Rule::unique('_tb_wms_clientes_transit_time', 'codigo_cliente')->ignore($cliente?->id),
            ],
            'nome_cliente' => ['nullable', 'string', 'max:150'],
            'zona_partida' => ['nullable', 'string', 'max:50'],
            'regiao' => ['nullable', 'string', 'max:100'],
            'uf' => ['nullable', 'string', 'max:2'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'zona_transporte' => ['nullable', 'string', 'max:50'],
            'ciclo_inte' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'transit_time_fechada_dias' => ['required', 'integer', 'min:0', 'max:3650'],
            'transit_time_fracionada_dias' => ['required', 'integer', 'min:0', 'max:3650'],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'codigo_cliente.required' => 'Informe o código do cliente.',
            'codigo_cliente.unique' => 'Já existe um cliente cadastrado com este código.',
            'transit_time_fechada_dias.required' => 'Informe o transit-time para carga fechada.',
            'transit_time_fechada_dias.integer' => 'O transit-time de carga fechada deve ser um número inteiro.',
            'transit_time_fracionada_dias.required' => 'Informe o transit-time para carga fracionada.',
            'transit_time_fracionada_dias.integer' => 'O transit-time de carga fracionada deve ser um número inteiro.',
        ]);
    }

    private function normalizarDados(array $dados, bool $ativoAtual = true): array
    {
        return [
            'codigo_cliente' => trim($dados['codigo_cliente']),
            'nome_cliente' => filled($dados['nome_cliente'] ?? null) ? trim($dados['nome_cliente']) : null,
            'zona_partida' => filled($dados['zona_partida'] ?? null) ? trim($dados['zona_partida']) : null,
            'regiao' => filled($dados['regiao'] ?? null) ? trim($dados['regiao']) : null,
            'uf' => filled($dados['uf'] ?? null) ? strtoupper(trim($dados['uf'])) : null,
            'cidade' => filled($dados['cidade'] ?? null) ? trim($dados['cidade']) : null,
            'zona_transporte' => filled($dados['zona_transporte'] ?? null) ? trim($dados['zona_transporte']) : null,
            'ciclo_inte' => filled($dados['ciclo_inte'] ?? null) ? (int) $dados['ciclo_inte'] : null,
            'transit_time_fechada_dias' => (int) $dados['transit_time_fechada_dias'],
            'transit_time_fracionada_dias' => (int) $dados['transit_time_fracionada_dias'],
            'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : $ativoAtual,
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
