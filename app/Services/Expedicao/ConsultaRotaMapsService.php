<?php

namespace App\Services\Expedicao;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsultaRotaMapsService
{
    public function consultar(string $cidadeDestino, string $ufDestino): ?array
    {
        if (! $this->habilitado()) {
            return null;
        }

        if (config('services.expedicao_rotas.provider') === 'osrm') {
            return $this->consultarOsrm($cidadeDestino, $ufDestino);
        }

        $origem = $this->montarEnderecoOrigem();

        $destino = $this->montarEndereco($cidadeDestino, $ufDestino);

        try {
            $response = Http::timeout($this->timeout())
                ->retry($this->tentativas(), 250)
                ->get(config('services.expedicao_rotas.endpoint'), [
                    'origins' => $origem,
                    'destinations' => $destino,
                    'mode' => 'driving',
                    'language' => 'pt-BR',
                    'region' => 'br',
                    'key' => config('services.expedicao_rotas.key'),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao consultar API de rotas da expedição.', [
                'destino' => $destino,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('API de rotas retornou resposta inválida.', [
                'destino' => $destino,
                'status_http' => $response->status(),
            ]);

            return null;
        }

        $dados = $response->json();
        $elemento = $dados['rows'][0]['elements'][0] ?? null;

        if (($dados['status'] ?? null) !== 'OK' || ($elemento['status'] ?? null) !== 'OK') {
            Log::warning('API de rotas não encontrou trajeto válido.', [
                'destino' => $destino,
                'status_api' => $dados['status'] ?? null,
                'status_rota' => $elemento['status'] ?? null,
            ]);

            return null;
        }

        $duracaoSegundos = (int) ($elemento['duration']['value'] ?? 0);
        $distanciaMetros = (int) ($elemento['distance']['value'] ?? 0);

        if ($duracaoSegundos <= 0) {
            return null;
        }

        return [
            'tempo_api_minutos' => (int) ceil($duracaoSegundos / 60),
            'distancia_km' => $distanciaMetros > 0 ? round($distanciaMetros / 1000, 2) : null,
        ];
    }

    private function habilitado(): bool
    {
        $provider = config('services.expedicao_rotas.provider');

        return $provider === 'osrm'
            || ($provider === 'google' && filled(config('services.expedicao_rotas.key')));
    }

    private function montarEndereco(string $cidade, string $uf): string
    {
        $pais = config('services.expedicao_rotas.country', 'Brasil');

        return trim("{$cidade}, {$uf}, {$pais}");
    }

    private function montarEnderecoOrigem(): string
    {
        $enderecoOrigem = config('services.expedicao_rotas.origin_address');

        if (filled($enderecoOrigem)) {
            return (string) $enderecoOrigem;
        }

        return $this->montarEndereco(
            config('services.expedicao_rotas.origin_city', 'Sao Bernardo do Campo'),
            config('services.expedicao_rotas.origin_uf', 'SP')
        );
    }

    private function consultarOsrm(string $cidadeDestino, string $ufDestino): ?array
    {
        $origem = $this->geocodificarEndereco($this->montarEnderecoOrigem());

        $destino = $this->geocodificar($cidadeDestino, $ufDestino);

        if (! $origem || ! $destino) {
            return null;
        }

        $endpoint = rtrim((string) config('services.expedicao_rotas.osrm_endpoint'), '/');
        $coordenadas = "{$origem['lon']},{$origem['lat']};{$destino['lon']},{$destino['lat']}";

        try {
            $response = Http::timeout($this->timeout())
                ->retry($this->tentativas(), 250)
                ->get("{$endpoint}/{$coordenadas}", [
                    'overview' => 'false',
                    'alternatives' => 'false',
                    'steps' => 'false',
                ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao consultar OSRM para rota da expedição.', [
                'destino' => $this->montarEndereco($cidadeDestino, $ufDestino),
                'erro' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('OSRM retornou resposta inválida para rota da expedição.', [
                'destino' => $this->montarEndereco($cidadeDestino, $ufDestino),
                'status_http' => $response->status(),
            ]);

            return null;
        }

        $dados = $response->json();
        $rota = $dados['routes'][0] ?? null;

        if (($dados['code'] ?? null) !== 'Ok' || ! $rota) {
            Log::warning('OSRM não encontrou rota válida para expedição.', [
                'destino' => $this->montarEndereco($cidadeDestino, $ufDestino),
                'codigo' => $dados['code'] ?? null,
            ]);

            return null;
        }

        $duracaoSegundos = (float) ($rota['duration'] ?? 0);
        $distanciaMetros = (float) ($rota['distance'] ?? 0);

        if ($duracaoSegundos <= 0) {
            return null;
        }

        return [
            'tempo_api_minutos' => (int) ceil($duracaoSegundos / 60),
            'distancia_km' => $distanciaMetros > 0 ? round($distanciaMetros / 1000, 2) : null,
        ];
    }

    private function geocodificar(string $cidade, string $uf): ?array
    {
        return $this->geocodificarEndereco($this->montarEndereco($cidade, $uf));
    }

    private function geocodificarEndereco(string $endereco): ?array
    {
        $endpoint = (string) config('services.expedicao_rotas.geocode_endpoint');
        $userAgent = (string) config('services.expedicao_rotas.user_agent');

        try {
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
            ])
                ->timeout($this->timeout())
                ->retry($this->tentativas(), 250)
                ->get($endpoint, [
                    'q' => $endereco,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'br',
                ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao geocodificar cidade para rota da expedição.', [
                'endereco' => $endereco,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Geocoder retornou resposta inválida para rota da expedição.', [
                'endereco' => $endereco,
                'status_http' => $response->status(),
            ]);

            return null;
        }

        $resultado = $response->json()[0] ?? null;

        if (! isset($resultado['lat'], $resultado['lon'])) {
            return null;
        }

        return [
            'lat' => (float) $resultado['lat'],
            'lon' => (float) $resultado['lon'],
        ];
    }

    private function timeout(): int
    {
        return max(2, (int) config('services.expedicao_rotas.timeout', 5));
    }

    private function tentativas(): int
    {
        return max(1, (int) config('services.expedicao_rotas.retry_times', 1));
    }
}
