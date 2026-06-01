<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('label', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('label', 160)->nullable();
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id_user')->on('_tb_usuarios')->cascadeOnDelete();
        });

        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    private function seedDefaults(): void
    {
        $now = now();
        $permissions = [
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

        foreach ($permissions as $name => $label) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $name,
                'label' => $label,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roles = [
            'admin' => ['label' => 'Administrador', 'permissions' => array_keys($permissions)],
            'gestor' => ['label' => 'Gestor', 'permissions' => [
                'demandas.view', 'demandas.edit', 'recebimento.view', 'recebimento.edit',
                'kits.view', 'kits.edit', 'relatorios.view', 'etiquetas.view',
            ]],
            'operador' => ['label' => 'Operador', 'permissions' => [
                'demandas.view', 'demandas.edit', 'recebimento.view', 'recebimento.edit',
                'kits.view', 'kits.edit', 'etiquetas.view',
            ]],
            'relatorios' => ['label' => 'Relatorios', 'permissions' => ['relatorios.view']],
        ];

        foreach ($roles as $name => $data) {
            DB::table('roles')->insertOrIgnore([
                'name' => $name,
                'label' => $data['label'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $roleId = DB::table('roles')->where('name', $name)->value('id');
            $permissionIds = DB::table('permissions')->whereIn('name', $data['permissions'])->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        DB::table('_tb_usuarios')->orderBy('id_user')->select(['id_user', 'tipo'])->chunk(100, function ($users): void {
            foreach ($users as $user) {
                $roleName = match (strtolower((string) $user->tipo)) {
                    'admin' => 'admin',
                    'gestor', 'supervisor' => 'gestor',
                    default => 'operador',
                };

                $roleId = DB::table('roles')->where('name', $roleName)->value('id');
                if ($roleId) {
                    DB::table('user_role')->insertOrIgnore([
                        'user_id' => $user->id_user,
                        'role_id' => $roleId,
                    ]);
                }
            }
        });
    }
};
