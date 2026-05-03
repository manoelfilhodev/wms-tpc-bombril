<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SaldoEstoqueIndexRequest;
use App\Http\Requests\Api\V1\SaldoEstoqueUpdateRequest;
use App\Http\Resources\Api\V1\SaldoEstoqueResource;
use App\Models\SaldoEstoque;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\Builder;

class SaldoEstoqueController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lista saldos de estoque
     *
     * @group Saldo de Estoque
     * @authenticated
     *
     * @queryParam page integer Pagina atual. Example: 1
     * @queryParam per_page integer Itens por pagina (max 100). Example: 15
     * @queryParam sort string Campo de ordenacao (id,quantidade,created_at,updated_at,sku,descricao,posicao,unidade_id). Example: updated_at
     * @queryParam direction string Direcao da ordenacao (asc|desc). Example: desc
     * @queryParam sku string Filtro parcial por SKU. Example: ABC
     * @queryParam descricao string Filtro parcial por descricao. Example: PARAFUSO
     * @queryParam unidade integer Filtro por unidade. Example: 1
     * @queryParam posicao string|integer Filtro por posicao (id ou codigo). Example: A01
     * @queryParam min_qtd integer Quantidade minima. Example: 10
     * @queryParam max_qtd integer Quantidade maxima. Example: 100
     * @queryParam updated_from date Data inicial de atualizacao (Y-m-d). Example: 2026-01-01
     * @queryParam updated_to date Data final de atualizacao (Y-m-d). Example: 2026-01-31
     */
    public function index(SaldoEstoqueIndexRequest $request)
    {
        $params = $request->validated();
        $perPage = min((int) ($params['per_page'] ?? 15), 100);

        // Sorting is restricted to a known allowlist to avoid arbitrary SQL fragments.
        $sortableColumns = [
            'id' => 's.id',
            'quantidade' => 's.quantidade',
            'created_at' => 's.created_at',
            'updated_at' => 's.updated_at',
            'sku' => 'm.sku',
            'descricao' => 'm.descricao',
            'posicao' => 'p.codigo_posicao',
            'unidade_id' => 's.unidade_id',
        ];

        $sortKey = $params['sort'] ?? 'id';
        $sortColumn = $sortableColumns[$sortKey] ?? 's.id';
        $direction = $params['direction'] ?? 'desc';

        $query = $this->baseQuery();

        if (! empty($params['sku'])) {
            $query->where('m.sku', 'like', '%' . $params['sku'] . '%');
        }

        if (! empty($params['material'])) {
            $query->where(function (Builder $builder) use ($params) {
                $builder->where('s.sku_id', (int) $params['material'])
                    ->orWhere('s.material_id', (int) $params['material']);
            });
        }

        if (! empty($params['descricao'])) {
            $query->where(function (Builder $builder) use ($params) {
                $builder->where('m.descricao', 'like', '%' . $params['descricao'] . '%')
                    ->orWhere('m.nome', 'like', '%' . $params['descricao'] . '%');
            });
        }

        if (! empty($params['unidade'])) {
            $query->where('s.unidade_id', (int) $params['unidade']);
        }

        if (! empty($params['posicao'])) {
            if (is_numeric($params['posicao'])) {
                $query->where('s.posicao_id', (int) $params['posicao']);
            } else {
                $query->where('p.codigo_posicao', 'like', '%' . $params['posicao'] . '%');
            }
        }

        if (array_key_exists('min_qtd', $params)) {
            $query->where('s.quantidade', '>=', (int) $params['min_qtd']);
        }

        if (array_key_exists('max_qtd', $params)) {
            $query->where('s.quantidade', '<=', (int) $params['max_qtd']);
        }

        if (! empty($params['updated_from'])) {
            $query->whereDate('s.updated_at', '>=', $params['updated_from']);
        }

        if (! empty($params['updated_to'])) {
            $query->whereDate('s.updated_at', '<=', $params['updated_to']);
        }

        $paginator = $query
            ->orderBy($sortColumn, $direction)
            ->paginate($perPage)
            ->appends($request->query());

        return $this->success(
            SaldoEstoqueResource::collection(collect($paginator->items())),
            'Saldos de estoque listados com sucesso.',
            [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ]
        );
    }

    /**
     * Exibe um saldo por ID
     *
     * @group Saldo de Estoque
     * @authenticated
     *
     * @urlParam id integer required ID do saldo de estoque. Example: 1
     */
    public function show(int $id)
    {
        $saldo = $this->baseQuery()
            ->where('s.id', $id)
            ->first();

        if (! $saldo) {
            return $this->error('Saldo de estoque nao encontrado.', 404);
        }

        return $this->success(new SaldoEstoqueResource($saldo), 'Saldo de estoque recuperado com sucesso.');
    }

    /**
     * Atualiza um saldo de estoque
     *
     * @group Saldo de Estoque
     * @authenticated
     *
     * @urlParam id integer required ID do saldo de estoque. Example: 1
     * @bodyParam quantidade integer Quantidade atual do saldo. Example: 150
     * @bodyParam data_entrada date Data de entrada no formato Y-m-d. Example: 2026-02-20
     */
    public function update(SaldoEstoqueUpdateRequest $request, int $id)
    {
        $saldo = SaldoEstoque::query()->find($id);

        if (! $saldo) {
            return $this->error('Saldo de estoque nao encontrado.', 404);
        }

        $saldo->update($request->validated());

        $updated = $this->baseQuery()
            ->where('s.id', $saldo->id)
            ->first();

        return $this->success(new SaldoEstoqueResource($updated), 'Saldo de estoque atualizado com sucesso.');
    }

    private function baseQuery(): Builder
    {
        return SaldoEstoque::query()
            ->from('_tb_saldo_estoque as s')
            ->leftJoin('_tb_materiais as m', 'm.id', '=', 's.sku_id')
            ->leftJoin('_tb_posicoes as p', 'p.id', '=', 's.posicao_id')
            ->leftJoin('_tb_unidades as u', 'u.id', '=', 's.unidade_id')
            ->select([
                's.id',
                's.sku_id',
                's.material_id',
                's.posicao_id',
                's.unidade_id',
                's.quantidade',
                's.data_entrada',
                's.created_at',
                's.updated_at',
                'm.sku',
                'm.nome as material_nome',
                'm.descricao',
                'p.codigo_posicao as posicao_codigo',
                'u.nome as unidade_nome',
            ]);
    }
}
