<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $unidadeId = DB::table('_tb_unidades')->where('nome', 'Unidade Central')->value('id');
        if (! $unidadeId) {
            $unidadeId = DB::table('_tb_unidades')->min('id');
        }
        if (! $unidadeId) {
            $unidadeId = DB::table('_tb_unidades')->insertGetId([
                'nome' => 'Unidade Central',
                'endereco' => 'Rua Principal, 123',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'status' => 'ativo',
                'created_at' => now(),
            ]);
        }

        $usuarios = [
            [
                'nome' => 'Admin Teste',
                'email' => 'admin@wms.com',
                'password' => Hash::make('admin123!@'),
                'tipo' => 'admin',
                'tipo_usuario' => 'admin',
                'nivel' => 'Admin',
                'status' => 'ativo',
                'unidade_id' => $unidadeId,
            ],
            [
                'nome' => 'Operador Teste',
                'email' => 'operador1@wms.com',
                'password' => Hash::make('operador123'),
                'tipo' => 'operador',
                'tipo_usuario' => 'operador',
                'nivel' => 'Operador',
                'status' => 'ativo',
                'unidade_id' => $unidadeId,
            ],
            [
                'nome' => 'Gerente Teste',
                'email' => 'gerente1@wms.com',
                'password' => Hash::make('gerente123'),
                'tipo' => 'gestor',
                'tipo_usuario' => 'gestor',
                'nivel' => 'Gestor',
                'status' => 'ativo',
                'unidade_id' => $unidadeId,
            ],
        ];

        foreach ($usuarios as $usuario) {
            DB::table('_tb_usuarios')->updateOrInsert(
                ['email' => $usuario['email']],
                array_merge($usuario, ['updated_at' => now()])
            );
        }
    }
}
