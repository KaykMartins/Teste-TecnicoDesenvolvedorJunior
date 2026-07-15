<?php

namespace App\Services;

use App\Exceptions\ViaCepIndisponivelException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ViaCepService
{
    private const BASE_URL = 'https://viacep.com.br/ws';

    /**
     * Consulta um CEP (já sanitizado, 8 dígitos) na API do ViaCEP.
     *
     * @return array<string, mixed>|null Dados do endereço, ou null se o CEP não existir.
     *
     * @throws ViaCepIndisponivelException se a integração falhar (timeout, conexão, ou HTTP de erro).
     */
    public function consultar(string $cep): ?array
    {
        try {
            $response = Http::timeout(5)
                ->retry(2, 200, throw: false)
                ->get(self::BASE_URL."/{$cep}/json/");

            if ($response->failed()) {
                throw new ViaCepIndisponivelException(
                    "ViaCEP respondeu com status HTTP {$response->status()} para o CEP {$cep}."
                );
            }

            $dados = $response->json();

            if (! is_array($dados)) {
                throw new ViaCepIndisponivelException(
                    "ViaCEP respondeu com um corpo que não é JSON válido para o CEP {$cep}."
                );
            }
        } catch (ViaCepIndisponivelException $e) {
            Log::error('Falha na integração com o ViaCEP.', [
                'cep' => $cep,
                'mensagem' => $e->getMessage(),
            ]);

            throw $e;
        } catch (Throwable $e) {
            Log::error('Falha na integração com o ViaCEP.', [
                'cep' => $cep,
                'mensagem' => $e->getMessage(),
            ]);

            throw new ViaCepIndisponivelException(
                "Não foi possível consultar o CEP {$cep} no ViaCEP: {$e->getMessage()}",
                previous: $e
            );
        }

        // O ViaCEP responde HTTP 200 com {"erro": true} (às vezes {"erro": "true"},
        // como STRING) para CEP inexistente — isso NÃO é falha de integração, é um
        // resultado negativo válido. filter_var trata true/"true"/"1" da mesma forma.
        if (filter_var($dados['erro'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        return $dados;
    }
}
