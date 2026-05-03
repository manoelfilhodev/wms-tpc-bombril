<?php

namespace App\Services;

use App\Models\DispositivoAutorizado;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class DeviceAuthorizationService
{
    public const COOKIE_NAME = 'systex_wms_device_id';
    public const LEGACY_COOKIE_NAME = 'systex_device_id';

    public function requiresDeviceValidation(User $user): bool
    {
        return $user->tipo === 'operador';
    }

    public function findAuthorizedDevice(User $user, ?string $deviceId, string $tipo): ?DispositivoAutorizado
    {
        if (! $deviceId) {
            return null;
        }

        return DispositivoAutorizado::query()
            ->where('device_id', $deviceId)
            ->where('tipo', $tipo)
            ->where('ativo', true)
            ->where(function ($query) use ($user) {
                $query->whereNull('usuario_id')
                    ->orWhere('usuario_id', $user->id_user);
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('perfil_permitido')
                    ->orWhere('perfil_permitido', $user->tipo)
                    ->orWhere('perfil_permitido', $user->nivel);
            })
            ->first();
    }

    public function isActiveDeviceRegistered(?string $deviceId, string $tipo): bool
    {
        if (! $deviceId || ! Schema::hasTable('_tb_dispositivos_autorizados')) {
            return false;
        }

        return DispositivoAutorizado::query()
            ->where('device_id', $deviceId)
            ->where('tipo', $tipo)
            ->where('ativo', true)
            ->exists();
    }

    public function touchLastAccess(DispositivoAutorizado $device): void
    {
        $device->forceFill(['ultimo_acesso_em' => now()])->save();
    }
}
