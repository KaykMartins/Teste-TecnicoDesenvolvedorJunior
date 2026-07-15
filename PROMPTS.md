# Uso de IA neste projeto

Ferramenta utilizada: **Claude Code**, rodando como extensão dentro do VS Code, em sessão interativa (não foi gerado código "de uma vez" — o trabalho foi feito parte por parte, com pausas para revisão e decisão a cada etapa).

Este arquivo registra, por parte do teste: as instruções reais que dei, em que a IA ajudou, o que eu alterei do que foi gerado, como validei, e quais cuidados eu teria antes de produção.

## Setup inicial

**Instrução dada**: um briefing completo (colado como primeira mensagem) definindo a ordem de execução (Setup → Parte 1 → ... → Bônus), a regra de trabalhar parte por parte com pausa para confirmação ao final de cada uma, de manter `docs/DECISOES.md` e `PROMPTS.md` atualizados, e de perguntar antes de tomar decisões com mais de uma opção razoável.

**Onde a IA ajudou**: detectou que a máquina não tinha PHP, Composer nem Git instalados; instalou as três ferramentas (Git e PHP via `winget`, Composer via instalador oficial com verificação de assinatura); montou o `php.ini` do zero com as extensões necessárias; criou o projeto Laravel via `composer create-project`; inicializou o Git e fez o primeiro commit.

**O que eu decidi (não a IA sozinha)**:
- Confirmei manter o Laravel 13 (a IA identificou que era mais novo que o "11 ou 12" do briefing e perguntou antes de prosseguir, em vez de decidir sozinha).
- Confirmei SQLite como banco de dados (a IA apresentou o trade-off contra MySQL antes de fixar).

**Como validei**: rodei/conferi os outputs de `php --version`, `composer --version`, `php artisan --version`, `git --version`, `php artisan migrate:status` e `git log --oneline` mostrados no terminal — não aceitei "deu certo" sem ver a versão exata e o commit real.

**Cuidados antes de produção**: nenhum ainda nesta etapa (é só setup de ambiente local).

## Parte 1 — Correção do `calcularDesconto`

**Instrução dada**: "podemos seguir" (após a Parte 0), seguindo a especificação já definida no briefing inicial (corrigir/robustecer o método, decidir exceção vs. clamp comigo antes de fixar, documentar em `docs/DECISOES.md`, listar/testar os casos de borda).

**Onde a IA ajudou**: escreveu o `DescontoService` com as validações (nulo, não numérico, valor negativo, desconto fora de 0–100) e os testes PHPUnit cobrindo os 11 casos de borda listados no briefing.

**O que eu decidi (não a IA sozinha)**:
- Perguntada antes de escrever qualquer código, escolhi **lançar exceção** (`InvalidArgumentException`) em vez de fazer *clamp* silencioso dos valores fora de faixa.
- Perguntada em seguida, escolhi **testes automatizados (PHPUnit)** em vez de apenas uma lista manual de casos.

**O que eu alterei do que a IA gerou**: nada alterado nesta parte — revisei o código e os testes gerados e concordei com a abordagem (parâmetros `mixed` em vez de `float` tipado, para poder dar mensagens de erro específicas em vez de um `TypeError` genérico do PHP).

**Como validei**: rodei `php artisan test --filter=DescontoServiceTest` e conferi que os 11 testes passaram (11 assertions, 0 falhas) antes de aceitar a implementação como pronta.

**Cuidados antes de produção**:
- Se este método for exposto via API (endpoint HTTP), a `InvalidArgumentException` não deve vazar como erro 500 puro — precisaria de um handler específico (ou uma Form Request antes de chamar o service) para virar uma resposta 422 com mensagem amigável.
- Validar se `mixed $valor, mixed $desconto` é aceitável no restante do projeto ou se, ao integrar com outras partes (ex.: Ordens de Serviço), faz mais sentido tipar como `int|float` e tratar `null` como caso à parte antes de chamar o service.

## Parte 2 — Banco de dados (Clientes, Veículos, Ordens de Serviço)

**Instrução dada**: "Podemos seguir" (após a Parte 1), seguindo a especificação já definida no briefing inicial (migrations, models com relações, `sql/consultas.sql` com as 4 consultas comentadas, seeder de exemplo).

**Onde a IA ajudou**: gerou as 3 migrations, os 3 models com as relações (`hasMany`/`belongsTo`), o `DomainDataSeeder` com dados de exemplo, e as 4 consultas em `sql/consultas.sql`; rodou as migrations e testou cada consulta manualmente contra o banco populado antes de considerar a parte pronta.

**O que eu decidi (não a IA sozinha)**:
- Perguntada sobre o critério de "aberta", escolhi um `status` com só 2 valores (`aberta`/`concluida`), evitando modelar um fluxo de 4 estados que o teste não pediu.
- Perguntada sobre exclusão em cascata vs. bloqueio, escolhi **restrict** (bloquear exclusão de Cliente/Veículo com filhos), por segurança contra apagar histórico de Ordens de Serviço por engano.

**O que eu alterei do que a IA gerou**: nenhuma alteração de código nesta parte — revisei o schema gerado (`sqlite_master`), os índices únicos de `cpf`/`placa`, e o resultado das 4 consultas rodadas contra os dados do seeder, e todos bateram com o esperado.

**Decisão registrada, mas não perguntada**: a IA decidiu por conta própria que a soma de "valor total gasto" (consultas 3 e 4) inclui ordens com qualquer status (não só `concluida`), por ser a leitura mais literal do enunciado ("SUM(valor) + GROUP BY", sem filtro de status mencionado). Documentado em `docs/DECISOES.md` como uma leitura alternativa possível, para eu revisar se concordo.

**Como validei**: rodei `php artisan migrate` (schema limpo), inspecionei o `CREATE TABLE` real gerado no SQLite (`sqlite_master`) para confirmar as constraints `restrict`/`unique`, rodei `php artisan migrate:fresh --seed`, e executei as 4 consultas manualmente via um script PHP temporário, conferindo os resultados linha a linha (incluindo o caso de borda do cliente sem veículos, que retornou array vazio em vez de erro).

**Cuidados antes de produção**:
- O `restrictOnDelete()` significa que excluir um Cliente com veículos ativos falha com erro de integridade — se isso vier a ser uma operação de negócio real (ex.: LGPD), precisaria de um fluxo explícito (soft delete ou exclusão em cascata controlada por código, com confirmação), não apenas deixar o banco rejeitar.
- Revisar com o time se "total gasto" deveria mesmo incluir ordens ainda abertas ou só as concluídas — é uma leitura que pode mudar o resultado que um gestor vê no relatório.

---

_As próximas seções (Parte 3 em diante) serão adicionadas conforme o desenvolvimento avança._
