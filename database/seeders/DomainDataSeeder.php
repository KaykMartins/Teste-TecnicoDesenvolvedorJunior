<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class DomainDataSeeder extends Seeder
{
    /**
     * Popula Clientes, Veículos e Ordens de Serviço com dados de exemplo,
     * para que sql/consultas.sql possa ser testado com resultados reais.
     */
    public function run(): void
    {
        $dados = [
            [
                'cliente' => ['nome' => 'João Silva', 'cpf' => '11122233344', 'telefone' => '(19) 99999-0001'],
                'veiculos' => [
                    [
                        'veiculo' => ['placa' => 'ABC1234', 'marca' => 'Fiat', 'modelo' => 'Uno'],
                        'ordens' => [
                            ['valor' => 350.00, 'status' => 'concluida'],
                            ['valor' => 800.00, 'status' => 'aberta'],
                        ],
                    ],
                    [
                        'veiculo' => ['placa' => 'XYZ9A87', 'marca' => 'Honda', 'modelo' => 'Civic'],
                        'ordens' => [
                            ['valor' => 1200.00, 'status' => 'concluida'],
                        ],
                    ],
                ],
            ],
            [
                'cliente' => ['nome' => 'Maria Souza', 'cpf' => '22233344455', 'telefone' => '(19) 99999-0002'],
                'veiculos' => [
                    [
                        'veiculo' => ['placa' => 'DEF4567', 'marca' => 'Volkswagen', 'modelo' => 'Gol'],
                        'ordens' => [
                            ['valor' => 500.00, 'status' => 'concluida'],
                            ['valor' => 150.00, 'status' => 'aberta'],
                            ['valor' => 620.00, 'status' => 'concluida'],
                        ],
                    ],
                ],
            ],
            [
                'cliente' => ['nome' => 'Pedro Santos', 'cpf' => '33344455566', 'telefone' => '(19) 99999-0003'],
                'veiculos' => [
                    [
                        'veiculo' => ['placa' => 'GHI7890', 'marca' => 'Chevrolet', 'modelo' => 'Onix'],
                        'ordens' => [
                            ['valor' => 2500.00, 'status' => 'concluida'],
                        ],
                    ],
                    [
                        'veiculo' => ['placa' => 'JKL1M23', 'marca' => 'Toyota', 'modelo' => 'Corolla'],
                        'ordens' => [
                            ['valor' => 900.00, 'status' => 'aberta'],
                        ],
                    ],
                ],
            ],
            [
                'cliente' => ['nome' => 'Ana Oliveira', 'cpf' => '44455566677', 'telefone' => '(19) 99999-0004'],
                'veiculos' => [
                    [
                        'veiculo' => ['placa' => 'MNO3456', 'marca' => 'Ford', 'modelo' => 'Ka'],
                        'ordens' => [
                            ['valor' => 300.00, 'status' => 'concluida'],
                        ],
                    ],
                ],
            ],
            [
                'cliente' => ['nome' => 'Carlos Pereira', 'cpf' => '55566677788', 'telefone' => '(19) 99999-0005'],
                'veiculos' => [
                    [
                        'veiculo' => ['placa' => 'PQR6789', 'marca' => 'Hyundai', 'modelo' => 'HB20'],
                        'ordens' => [
                            ['valor' => 1800.00, 'status' => 'concluida'],
                            ['valor' => 400.00, 'status' => 'aberta'],
                        ],
                    ],
                ],
            ],
            [
                'cliente' => ['nome' => 'Fernanda Lima', 'cpf' => '66677788899', 'telefone' => '(19) 99999-0006'],
                'veiculos' => [
                    [
                        'veiculo' => ['placa' => 'STU9012', 'marca' => 'Renault', 'modelo' => 'Kwid'],
                        'ordens' => [
                            ['valor' => 100.00, 'status' => 'concluida'],
                        ],
                    ],
                ],
            ],
            [
                // Cliente sem veículos, de propósito: caso de borda para a consulta 1
                // (listar veículos de um cliente que não tem nenhum deve retornar vazio, não erro).
                'cliente' => ['nome' => 'Rafael Costa', 'cpf' => '77788899900', 'telefone' => '(19) 99999-0007'],
                'veiculos' => [],
            ],
        ];

        foreach ($dados as $entry) {
            $cliente = Cliente::create($entry['cliente']);

            foreach ($entry['veiculos'] as $veiculoEntry) {
                $veiculo = $cliente->veiculos()->create($veiculoEntry['veiculo']);

                foreach ($veiculoEntry['ordens'] as $ordem) {
                    $veiculo->ordensServico()->create($ordem);
                }
            }
        }
    }
}
