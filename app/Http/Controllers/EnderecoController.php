<?php

namespace App\Http\Controllers;

use App\Exceptions\ViaCepIndisponivelException;
use App\Http\Requests\BuscarEnderecoRequest;
use App\Http\Resources\EnderecoResource;
use App\Models\Endereco;
use App\Services\ViaCepService;
use Illuminate\Http\JsonResponse;

class EnderecoController extends Controller
{
    public function __construct(private readonly ViaCepService $viaCep)
    {
        //
    }

    /**
     * Consulta um CEP no ViaCEP e salva (ou atualiza) o endereço correspondente.
     *
     * Fluxo: receber -> validar formato (BuscarEnderecoRequest) -> consultar
     * ViaCEP -> verificar se foi encontrado -> salvar ou atualizar (sem duplicar)
     * -> responder com o resultado.
     */
    public function store(BuscarEnderecoRequest $request): JsonResponse
    {
        $cep = $request->validated('cep');

        try {
            $dados = $this->viaCep->consultar($cep);
        } catch (ViaCepIndisponivelException) {
            return response()->json([
                'message' => 'Não foi possível consultar o ViaCEP no momento. Tente novamente mais tarde.',
            ], 500);
        }

        if ($dados === null) {
            return response()->json([
                'message' => "CEP {$cep} não foi encontrado.",
            ], 404);
        }

        $endereco = Endereco::updateOrCreate(
            ['cep' => $cep],
            [
                'logradouro' => $dados['logradouro'] ?? null,
                'complemento' => $dados['complemento'] ?? null,
                'bairro' => $dados['bairro'] ?? null,
                'cidade' => $dados['localidade'] ?? null,
                'estado' => $dados['uf'] ?? null,
            ]
        );

        return (new EnderecoResource($endereco))
            ->response()
            ->setStatusCode($endereco->wasRecentlyCreated ? 201 : 200);
    }
}
