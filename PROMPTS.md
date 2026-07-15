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

---

_As próximas seções (Parte 2 em diante) serão adicionadas conforme o desenvolvimento avança._
