<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = '_tb_usuarios'; // tabela correta
    protected $primaryKey = 'id_user'; // chave primária
    public $timestamps = true; // já que você tem created_at e updated_at

    protected $fillable = [
        'nome',
        'email',
        'password',
        'unidade_id',
        'tipo',
        'status',
        'nivel',
        'azure_id'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            if (! Schema::hasTable('roles') || ! Schema::hasTable('user_role')) {
                return;
            }

            $roleName = match (strtolower((string) $user->tipo)) {
                'admin' => 'admin',
                'gestor', 'supervisor' => 'gestor',
                default => 'operador',
            };

            $role = Role::query()->where('name', $roleName)->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        });
    }
    
    public function contagens()
{
    return $this->hasMany(ContagemItem::class, 'usuario_id', 'id_user');
}

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    public function hasPermission(string $permission): bool
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions') || ! Schema::hasTable('role_permission')) {
            return false;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->exists();
    }
}
