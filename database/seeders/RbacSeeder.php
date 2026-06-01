<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    private const PERMISSIONS = [
        'demandas.view' => 'Visualizar demandas',
        'demandas.edit' => 'Editar demandas',
        'demandas.delete' => 'Excluir demandas',
        'recebimento.view' => 'Visualizar recebimento',
        'recebimento.edit' => 'Editar recebimento',
        'kits.view' => 'Visualizar kits',
        'kits.edit' => 'Editar kits',
        'relatorios.view' => 'Visualizar relatorios',
        'etiquetas.view' => 'Visualizar etiquetas',
        'admin.access' => 'Acessar administracao',
    ];

    private const ROLES = [
        'admin' => [
            'label' => 'Administrador',
            'permissions' => [
                'demandas.view',
                'demandas.edit',
                'demandas.delete',
                'recebimento.view',
                'recebimento.edit',
                'kits.view',
                'kits.edit',
                'relatorios.view',
                'etiquetas.view',
                'admin.access',
            ],
        ],
        'gestor' => [
            'label' => 'Gestor',
            'permissions' => [
                'demandas.view',
                'demandas.edit',
                'recebimento.view',
                'recebimento.edit',
                'kits.view',
                'kits.edit',
                'relatorios.view',
                'etiquetas.view',
            ],
        ],
        'operador' => [
            'label' => 'Operador',
            'permissions' => [
                'demandas.view',
                'demandas.edit',
                'recebimento.view',
                'recebimento.edit',
                'kits.view',
                'kits.edit',
                'etiquetas.view',
            ],
        ],
        'relatorios' => [
            'label' => 'Relatorios',
            'permissions' => ['relatorios.view'],
        ],
    ];

    public function run(): void
    {
        $permissions = collect(self::PERMISSIONS)
            ->mapWithKeys(fn (string $label, string $name) => [
                $name => Permission::updateOrCreate(['name' => $name], ['label' => $label]),
            ]);

        foreach (self::ROLES as $name => $data) {
            $role = Role::updateOrCreate(['name' => $name], ['label' => $data['label']]);
            $role->permissions()->sync($permissions->only($data['permissions'])->pluck('id')->all());
        }

        User::query()->each(function (User $user): void {
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
}
