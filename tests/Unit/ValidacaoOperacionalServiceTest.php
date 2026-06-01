<?php

namespace Tests\Unit;

use App\Services\Expedicao\ValidacaoOperacionalService;
use Tests\TestCase;

class ValidacaoOperacionalServiceTest extends TestCase
{
    public function test_data_operacional_invalida_de_1970_e_ignorada(): void
    {
        $resultado = app(ValidacaoOperacionalService::class)->validarEtapa(
            '1970-01-01 00:00:00',
            '2026-05-20 12:00:00',
            240
        );

        $this->assertFalse($resultado['valido']);
        $this->assertFalse($resultado['anomalia']);
        $this->assertSame('SEM_REALIZADO', $resultado['status']);
    }
}
