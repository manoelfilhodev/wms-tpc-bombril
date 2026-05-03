<?php

namespace Tests\Feature\Api\V1\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

trait CreatesSaldoEstoqueData
{
    protected function createAuthToken(): string
    {
        $unidadeId = $this->ensureUnidade();

        $email = 'saldo.api.' . uniqid() . '@example.com';

        DB::table('_tb_usuarios')->insert([
            'nome' => 'Usuario API Saldo',
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'operador',
            'status' => 'ativo',
            'nivel' => 'Operador',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::query()->where('email', $email)->firstOrFail();

        return $user->createToken('saldo-api-tests')->plainTextToken;
    }

    protected function createSaldo(array $attributes = []): int
    {
        $unidadeId = $attributes['unidade_id'] ?? $this->ensureUnidade();
        $materialId = $attributes['sku_id'] ?? $this->createMaterial($unidadeId, $attributes['sku'] ?? null, $attributes['descricao'] ?? null);
        $posicaoId = $attributes['posicao_id'] ?? $this->createPosicao($unidadeId, $attributes['posicao_codigo'] ?? null);

        return (int) DB::table('_tb_saldo_estoque')->insertGetId([
            'sku_id' => $materialId,
            'material_id' => $attributes['material_id'] ?? $materialId,
            'posicao_id' => $posicaoId,
            'unidade_id' => $unidadeId,
            'quantidade' => $attributes['quantidade'] ?? 0,
            'data_entrada' => $attributes['data_entrada'] ?? now(),
            'created_at' => $attributes['created_at'] ?? now(),
            'updated_at' => $attributes['updated_at'] ?? now(),
        ]);
    }

    protected function ensureUnidade(): int
    {
        $existing = DB::table('_tb_unidades')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade API Saldo',
            'status' => 'ativo',
            'created_at' => now(),
        ]);
    }

    protected function createMaterial(int $unidadeId, ?string $sku = null, ?string $descricao = null): int
    {
        $skuValue = $sku ?? ('SKU' . uniqid());

        return (int) DB::table('_tb_materiais')->insertGetId([
            'nome' => 'Material ' . substr($skuValue, -6),
            'unidade_id' => $unidadeId,
            'sku' => $skuValue,
            'descricao' => $descricao ?? 'Descricao ' . $skuValue,
            'ean' => str_pad((string) random_int(1, 99999999999), 11, '0', STR_PAD_LEFT),
            'status' => 'ativo',
            'created_at' => now(),
        ]);
    }

    protected function createPosicao(int $unidadeId, ?string $codigo = null): int
    {
        $codigoPosicao = $codigo ?? ('POS-' . uniqid());

        return (int) DB::table('_tb_posicoes')->insertGetId([
            'codigo_posicao' => $codigoPosicao,
            'setor' => 'A',
            'unidade_id' => $unidadeId,
            'status' => 'ativa',
            'created_at' => now(),
        ]);
    }
}
