<?php

namespace Tests\Unit;

use App\Services\DescontoService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DescontoServiceTest extends TestCase
{
    private DescontoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DescontoService();
    }

    public function test_aplica_desconto_normal(): void
    {
        $this->assertEquals(90.0, $this->service->calcularDesconto(100, 10));
    }

    public function test_valor_zero_retorna_zero(): void
    {
        $this->assertEquals(0.0, $this->service->calcularDesconto(0, 10));
    }

    public function test_desconto_zero_retorna_valor_cheio(): void
    {
        $this->assertEquals(100.0, $this->service->calcularDesconto(100, 0));
    }

    public function test_desconto_cem_zera_o_valor(): void
    {
        $this->assertEquals(0.0, $this->service->calcularDesconto(100, 100));
    }

    public function test_desconto_acima_de_cem_lanca_excecao(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calcularDesconto(100, 101);
    }

    public function test_desconto_negativo_lanca_excecao(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calcularDesconto(100, -1);
    }

    public function test_valor_negativo_lanca_excecao(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calcularDesconto(-100, 10);
    }

    public function test_valor_nulo_lanca_excecao(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calcularDesconto(null, 10);
    }

    public function test_desconto_nulo_lanca_excecao(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calcularDesconto(100, null);
    }

    public function test_valor_nao_numerico_lanca_excecao(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calcularDesconto('abc', 10);
    }

    public function test_desconto_nao_numerico_lanca_excecao(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calcularDesconto(100, 'abc');
    }
}
