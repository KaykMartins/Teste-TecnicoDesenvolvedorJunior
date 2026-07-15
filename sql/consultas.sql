-- =============================================================================
-- HyperGestor — Parte 2: consultas SQL do domínio (Clientes, Veículos, Ordens de Serviço)
-- Banco: SQLite (sintaxe compatível com MySQL nas partes que importam: JOIN, SUM, GROUP BY, ORDER BY, LIMIT)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. Listar todos os veículos de um cliente (OBRIGATÓRIA)
-- Troque o "?" pelo id do cliente desejado (ex.: 1).
-- Um cliente sem veículos (ex.: "Rafael Costa" no seeder) retorna um resultado
-- vazio, e não um erro — o INNER JOIN simplesmente não encontra correspondência.
-- -----------------------------------------------------------------------------
SELECT
    veiculos.id,
    veiculos.placa,
    veiculos.marca,
    veiculos.modelo
FROM veiculos
INNER JOIN clientes ON clientes.id = veiculos.cliente_id
WHERE clientes.id = ?;


-- -----------------------------------------------------------------------------
-- 2. Listar todas as ordens de serviço abertas (OBRIGATÓRIA)
-- Critério de "aberta": o valor EXATO do campo status = 'aberta'
-- (o schema usa apenas dois valores possíveis: 'aberta' e 'concluida' — ver
-- migration create_ordem_servicos_table e docs/DECISOES.md, Parte 2).
-- -----------------------------------------------------------------------------
SELECT
    ordens_servico.id,
    ordens_servico.veiculo_id,
    ordens_servico.valor,
    ordens_servico.status,
    ordens_servico.created_at
FROM ordens_servico
WHERE ordens_servico.status = 'aberta';


-- -----------------------------------------------------------------------------
-- 3. Calcular o valor total gasto por um cliente (DIFERENCIAL)
-- Soma o valor de TODAS as ordens de serviço do cliente (não filtra por status:
-- entendemos "total gasto" como o total movimentado pelo cliente na oficina,
-- incluindo ordens ainda em aberto). Troque o "?" pelo id do cliente desejado.
-- -----------------------------------------------------------------------------
SELECT
    clientes.id AS cliente_id,
    clientes.nome,
    SUM(ordens_servico.valor) AS total_gasto
FROM clientes
INNER JOIN veiculos ON veiculos.cliente_id = clientes.id
INNER JOIN ordens_servico ON ordens_servico.veiculo_id = veiculos.id
WHERE clientes.id = ?
GROUP BY clientes.id, clientes.nome;


-- -----------------------------------------------------------------------------
-- 4. Listar os cinco clientes com maior valor gasto (DIFERENCIAL)
-- Mesma regra de "total gasto" da consulta 3 (todas as ordens, sem filtrar status),
-- agora para todos os clientes, ordenado do maior para o menor gasto, top 5.
-- Clientes sem nenhuma ordem de serviço (ex.: sem veículos) não aparecem, pois
-- o INNER JOIN exige pelo menos uma ordem de serviço associada.
-- -----------------------------------------------------------------------------
SELECT
    clientes.id AS cliente_id,
    clientes.nome,
    SUM(ordens_servico.valor) AS total_gasto
FROM clientes
INNER JOIN veiculos ON veiculos.cliente_id = clientes.id
INNER JOIN ordens_servico ON ordens_servico.veiculo_id = veiculos.id
GROUP BY clientes.id, clientes.nome
ORDER BY total_gasto DESC
LIMIT 5;
