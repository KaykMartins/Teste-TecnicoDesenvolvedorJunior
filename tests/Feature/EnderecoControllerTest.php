<?php

namespace Tests\Feature;

use App\Models\Endereco;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnderecoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_formato_invalido_retorna_422_sem_chamar_o_viacep(): void
    {
        Http::fake();

        $response = $this->postJson('/api/enderecos', ['cep' => '123']);

        $response->assertStatus(422);
        Http::assertNothingSent();
        $this->assertDatabaseCount('enderecos', 0);
    }

    public function test_cep_com_pontuacao_e_sanitizado_antes_de_consultar(): void
    {
        Http::fake([
            'viacep.com.br/*' => Http::response([
                'cep' => '13289-180',
                'logradouro' => 'Rua dos Pardais',
                'complemento' => '',
                'bairro' => 'Santa Rosa',
                'localidade' => 'Vinhedo',
                'uf' => 'SP',
            ], 200),
        ]);

        $response = $this->postJson('/api/enderecos', ['cep' => '13.289-180']);

        $response->assertStatus(201);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://viacep.com.br/ws/13289180/json/');
        $this->assertDatabaseHas('enderecos', ['cep' => '13289180', 'cidade' => 'Vinhedo']);
    }

    public function test_cep_ja_existente_atualiza_em_vez_de_duplicar(): void
    {
        Endereco::create([
            'cep' => '13289180',
            'logradouro' => 'Endereço antigo',
            'cidade' => 'Vinhedo',
            'estado' => 'SP',
        ]);

        Http::fake([
            'viacep.com.br/*' => Http::response([
                'cep' => '13289-180',
                'logradouro' => 'Rua dos Pardais',
                'complemento' => '',
                'bairro' => 'Santa Rosa',
                'localidade' => 'Vinhedo',
                'uf' => 'SP',
            ], 200),
        ]);

        $response = $this->postJson('/api/enderecos', ['cep' => '13289180']);

        $response->assertStatus(200);
        $this->assertDatabaseCount('enderecos', 1);
        $this->assertDatabaseHas('enderecos', ['cep' => '13289180', 'logradouro' => 'Rua dos Pardais']);
    }

    public function test_cep_inexistente_retorna_404(): void
    {
        Http::fake([
            'viacep.com.br/*' => Http::response(['erro' => true], 200),
        ]);

        $response = $this->postJson('/api/enderecos', ['cep' => '99999999']);

        $response->assertStatus(404);
        $this->assertDatabaseCount('enderecos', 0);
    }

    public function test_cep_inexistente_com_erro_como_string_tambem_retorna_404(): void
    {
        // O ViaCEP às vezes retorna {"erro": "true"} como string, não booleano.
        Http::fake([
            'viacep.com.br/*' => Http::response(['erro' => 'true'], 200),
        ]);

        $response = $this->postJson('/api/enderecos', ['cep' => '99999999']);

        $response->assertStatus(404);
    }

    public function test_falha_de_integracao_retorna_500_e_registra_log(): void
    {
        Http::fake([
            'viacep.com.br/*' => Http::response('erro interno', 500),
        ]);

        $response = $this->postJson('/api/enderecos', ['cep' => '13289180']);

        $response->assertStatus(500);
        $this->assertDatabaseCount('enderecos', 0);
    }
}
