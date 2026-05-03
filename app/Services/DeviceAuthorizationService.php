<?php

namespace App\Services;

use App\Models\DispositivoAutorizado;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DeviceAuthorizationService
{
    public const COOKIE_NAME = 'systex_wms_device_id';
    public const LEGACY_COOKIE_NAME = 'systex_device_id';

    public function requiresDeviceValidation(User $user): bool
    {
        return strtolower((string) $user->tipo) === 'operador';
    }

    public function findAuthorizedDevice(User $user, ?string $deviceId, string $tipo): ?DispositivoAutorizado
    {
        //dd('DEVICE SERVICE RODANDO', $deviceId, $tipo, $user->id_user);
        
        $deviceId = trim((string) $deviceId);
        $tipo = strtolower(trim($tipo));

        if ($deviceId === '' || ! Schema::hasTable('_tb_dispositivos_autorizados')) {
            Log::info('DEVICE AUTH CHECK - SEM DEVICE OU TABELA', [
                'device_id' => $deviceId,
                'tipo' => $tipo,
                'user_id' => $user->id_user,
            ]);

            return null;
        }

        $userTipo = strtolower((string) $user->tipo);
        $userNivel = strtolower((string) $user->nivel);
        $userTipoUsuario = strtolower((string) $user->tipo_usuario);

        $device = DispositivoAutorizado::query()
            ->where('device_id', $deviceId)
            ->whereRaw('LOWER(tipo) = ?', [$tipo])
            ->where('ativo', 1)
            ->where(function ($query) use ($user) {
                $query->whereNull('usuario_id')
                    ->orWhere('usuario_id', $user->id_user);
            })
            ->where(function ($query) use ($userTipo, $userNivel, $userTipoUsuario) {
                $query->whereNull('perfil_permitido')
                    ->orWhereRaw('LOWER(perfil_permitido) = ?', [$userTipo])
                    ->orWhereRaw('LOWER(perfil_permitido) = ?', [$userNivel])
                    ->orWhereRaw('LOWER(perfil_permitido) = ?', [$userTipoUsuario]);
            })
            ->first();

        Log::info('DEVICE AUTH CHECK', [
            'device_id' => $deviceId,
            'tipo' => $tipo,
            'usuario_id' => $user->id_user,
            'user_tipo' => $userTipo,
            'user_nivel' => $userNivel,
            'user_tipo_usuario' => $userTipoUsuario,
            'authorized' => (bool) $device,
            'device_record_id' => $device ? $device->id : null,
        ]);

        return $device;
    }

    public function isActiveDeviceRegistered(?string $deviceId, string $tipo): bool
    {
        $deviceId = trim((string) $deviceId);
        $tipo = strtolower(trim($tipo));

        if ($deviceId === '' || ! Schema::hasTable('_tb_dispositivos_autorizados')) {
            return false;
        }

        return DispositivoAutorizado::query()
            ->where('device_id', $deviceId)
            ->whereRaw('LOWER(tipo) = ?', [$tipo])
            ->where('ativo', 1)
            ->exists();
    }

    public function touchLastAccess(DispositivoAutorizado $device): void
    {
        $device->forceFill([
            'ultimo_acesso_em' => now(),
        ])->save();
    }
}