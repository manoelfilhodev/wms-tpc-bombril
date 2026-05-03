<?php

namespace App\Http\Controllers;

use App\Models\DispositivoAutorizado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DispositivoAutorizadoController extends Controller
{
    public function index(Request $request)
    {
        $query = DispositivoAutorizado::query()
            ->leftJoin('_tb_usuarios', '_tb_usuarios.id_user', '=', '_tb_dispositivos_autorizados.usuario_id')
            ->select('_tb_dispositivos_autorizados.*', '_tb_usuarios.nome as usuario_nome');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($q) use ($search) {
                $q->where('_tb_dispositivos_autorizados.nome_dispositivo', 'like', "%{$search}%")
                    ->orWhere('_tb_dispositivos_autorizados.device_id', 'like', "%{$search}%")
                    ->orWhere('_tb_usuarios.nome', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('_tb_dispositivos_autorizados.tipo', $request->string('tipo')->value());
        }

        if ($request->filled('ativo')) {
            $query->where('_tb_dispositivos_autorizados.ativo', $request->boolean('ativo'));
        }

        $dispositivos = $query
            ->orderByDesc('_tb_dispositivos_autorizados.updated_at')
            ->paginate(15);

        return view('dispositivos.index', compact('dispositivos'));
    }

    public function create(Request $request)
    {
        $currentDeviceId = $request->cookie(\App\Services\DeviceAuthorizationService::COOKIE_NAME);

        return view('dispositivos.create', compact('currentDeviceId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_dispositivo' => ['required', 'string', 'max:120'],
            'device_id' => ['required', 'string', 'max:100', 'unique:_tb_dispositivos_autorizados,device_id'],
            'tipo' => ['required', Rule::in(['web', 'app'])],
            'usuario_id' => ['nullable', 'integer', 'exists:_tb_usuarios,id_user'],
            'perfil_permitido' => ['nullable', 'string', 'max:50'],
            'ativo' => ['required', Rule::in(['0', '1'])],
        ]);

        DispositivoAutorizado::create([
            'nome_dispositivo' => $validated['nome_dispositivo'],
            'device_id' => $validated['device_id'],
            'tipo' => $validated['tipo'],
            'usuario_id' => $validated['usuario_id'] ?? null,
            'perfil_permitido' => $validated['perfil_permitido'] ?: null,
            'ativo' => $validated['ativo'] === '1',
        ]);

        return redirect()->route('dispositivos.index')->with('success', 'Dispositivo autorizado cadastrado com sucesso!');
    }

    public function edit(DispositivoAutorizado $dispositivo)
    {
        return view('dispositivos.edit', compact('dispositivo'));
    }

    public function update(Request $request, DispositivoAutorizado $dispositivo)
    {
        $validated = $request->validate([
            'nome_dispositivo' => ['required', 'string', 'max:120'],
            'device_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('_tb_dispositivos_autorizados', 'device_id')->ignore($dispositivo->id),
            ],
            'tipo' => ['required', Rule::in(['web', 'app'])],
            'usuario_id' => ['nullable', 'integer', 'exists:_tb_usuarios,id_user'],
            'perfil_permitido' => ['nullable', 'string', 'max:50'],
            'ativo' => ['required', Rule::in(['0', '1'])],
        ]);

        $dispositivo->update([
            'nome_dispositivo' => $validated['nome_dispositivo'],
            'device_id' => $validated['device_id'],
            'tipo' => $validated['tipo'],
            'usuario_id' => $validated['usuario_id'] ?? null,
            'perfil_permitido' => $validated['perfil_permitido'] ?: null,
            'ativo' => $validated['ativo'] === '1',
        ]);

        return redirect()->route('dispositivos.index')->with('success', 'Dispositivo atualizado com sucesso!');
    }

    public function toggle(DispositivoAutorizado $dispositivo)
    {
        $dispositivo->update(['ativo' => ! $dispositivo->ativo]);

        return redirect()->route('dispositivos.index')->with('success', 'Status do dispositivo atualizado.');
    }
}
