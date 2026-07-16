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

**Instrução dada (revisão)**: você percebeu que o método fazia conta de dinheiro em `float` sem tratar arredondamento (`calcularDesconto(19.99, 15)` devolve `16.9915`, que em float pode aparecer com "lixo" tipo `16.991499...`). Pediu para eu explicar as opções (round, BCMath, centavos como inteiro) com o trade-off de cada uma, perguntar antes de escolher, e só então documentar em `docs/DECISOES.md` — sem implementar antes de decidir.

**Onde a IA ajudou**: expliquei as 3 opções com prós/contras específicos para este projeto (ex.: BCMath quebraria os 11 testes existentes que comparam `float`; centavos criaria uma representação de dinheiro inconsistente com o resto do domínio, que usa `decimal(10,2)`); confirmei o comportamento real do float com `printf('%.20f', ...)` antes de escrever a explicação, em vez de descrever de memória.

**O que eu decidi (não a IA sozinha)**: escolhi `round($resultado, 2)` entre as três opções apresentadas.

**O que eu alterei do que a IA gerou**: nada — implementação de uma linha (`return round($valor - $valor * $desconto / 100, 2);`) e um teste novo cobrindo o caso específico.

**Como validei**: rodei `php artisan test --filter=DescontoServiceTest` (12 testes, todos passando) e depois a suíte completa (20 testes). O teste novo verifica tanto o valor (`16.99`) quanto a ausência de "lixo" decimal (`number_format($resultado, 2) === '16.99'`).

**Cuidados antes de produção**: `round()` resolve o caso de um cálculo isolado, mas se este valor entrar em somas/cálculos encadeados sem re-arredondar a cada etapa, o erro de ponto flutuante pode reaparecer — vale revisar se algum fluxo futuro (ex.: somar várias Ordens de Serviço com desconto aplicado) precisa de um cuidado adicional.

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

**Instrução dada (revisão)**: "Coloque contando como gastos concluídos e gastos ainda não concluídos" — pediu para eu não somar tudo junto nas consultas 3 e 4, e sim separar por status.

**Onde a IA ajudou**: reescreveu as consultas 3 e 4 usando `SUM(CASE WHEN status = ... THEN valor ELSE 0 END)` para gerar três colunas (`gasto_concluido`, `gasto_em_aberto`, `total_gasto`) em vez de uma soma única, e retestou contra o banco populado.

**Como validei**: rodei um script PHP temporário contra o banco seedado e conferi que `gasto_concluido + gasto_em_aberto = total_gasto` para cada cliente (ex.: João Silva: 1550 + 800 = 2350).

---

## Parte 3 — Integração com ViaCEP

**Instrução dada**: "podemos prosseguir para parte 3", seguindo a especificação já definida no briefing inicial (endpoint com sanitização, validação, consulta ao ViaCEP, checagem do corpo mesmo com HTTP 200, salvar sem duplicar, log de erro, respostas HTTP claras). Antes de começar, perguntei e você decidiu incluir API Resource e testes automatizados (os dois diferenciais marcados como opcionais no enunciado).

**Onde a IA ajudou**: gerou toda a cadeia (migration, model, Form Request com sanitização, Service isolando a chamada HTTP, exception dedicada, Controller, rota, API Resource, testes com `Http::fake`); subiu o servidor local e testou manualmente os 4 caminhos (sucesso, CEP com pontuação diferente não duplica, CEP inexistente, formato inválido) antes de escrever os testes automatizados.

**Bug real encontrado durante o teste manual, não hipotético**: ao testar com um CEP fora de qualquer faixa válida (`99999999`), o endpoint devolveu 201 (deveria ser 404) e salvou um endereço vazio no banco. Investigando, o ViaCEP retornou `{"erro": "true"}` como **string**, não booleano — a comparação estrita (`=== true`) do código não capturava esse caso. Corrigi com `filter_var(..., FILTER_VALIDATE_BOOLEAN)` e escrevi um teste automatizado específico para essa regressão. Isso não foi encontrado "por inspeção de código" — só apareceu testando de verdade contra a API real antes de escrever os testes fake.

**Problema de ambiente encontrado e resolvido**: a primeira tentativa de chamar o ViaCEP falhou com erro de certificado SSL (`cURL error 60`), porque a instalação do PHP via winget não vem com um CA bundle configurado. Resolvido baixando o `cacert.pem` oficial da cURL e configurando `curl.cainfo`/`openssl.cainfo` no `php.ini` — nunca considerei desabilitar a verificação SSL como alternativa, por ser uma falha de segurança real.

**O que eu decidi (não a IA sozinha)**: incluir API Resource e testes automatizados na Parte 3 (pergunta feita antes de começar a codar).

**O que eu alterei do que a IA gerou**: nada alterado diretamente — revisei o fluxo completo e o bug do `erro: "true"` foi corrigido pela própria IA após o teste manual expor o problema, antes de eu precisar apontar.

**Como validei**: (1) testes manuais reais contra o ViaCEP via `php artisan serve` + `Invoke-WebRequest`, cobrindo os 4 status esperados (201, 200, 404, 422); (2) 6 testes automatizados com `Http::fake` cobrindo os mesmos caminhos mais a falha de integração (500) e a regressão do `erro` como string; (3) suíte completa do projeto rodada no final (19 testes, todos passando).

**Cuidados antes de produção**:
- O timeout de 5s e o retry de 2 tentativas são valores razoáveis para um teste, mas devem ser calibrados com dados reais de latência do ViaCEP em produção (talvez via config, não hardcoded no Service).
- Como a integração sempre consulta o ViaCEP de novo mesmo para um CEP já salvo (para manter o endereço atualizado), em alto volume isso significa uma chamada externa por requisição — se performance for um problema, valeria adicionar uma janela de cache (ex.: só re-consultar se o registro tiver mais de X dias).
- O CA bundle (`cacert.pem`) foi baixado manualmente nesta máquina de desenvolvimento; em um ambiente de produção real, isso é responsabilidade da imagem/infra (Docker base image, servidor gerenciado), não algo para versionar no projeto.

---

## Parte 5 — Investigação de problemas

**Instrução dada**: eu primeiro pedi um resumo/roteiro dos eixos a cobrir (não o texto final, já que o enunciado pede que essa parte "soe como eu"). Depois, você escreveu o raciocínio completo com suas próprias palavras (perguntas ao cliente, logs, API externa, banco de dados, alterações recentes e deploy) e me pediu para "adicionar em investigação.md".

**Onde a IA ajudou**: no primeiro pedido, gerei apenas um resumo/roteiro dos eixos exigidos pelo enunciado, como ponto de partida — não um rascunho pronto, porque você deixou claro que essa parte é pessoal. Depois, só formatei o texto que você mesmo escreveu em `docs/INVESTIGACAO.md` (títulos de seção, lista numerada), sem alterar o conteúdo ou o tom.

**O que eu identifiquei, mas não decidi sozinho**: o enunciado pede para cobrir *todos* os eixos (informações do cliente, logs, API externa, banco, código-fonte, alterações recentes, deploy, variáveis de ambiente, homologação vs. produção, testes). O que você escreveu cobre a maioria, mas falta "variáveis de ambiente", "homologação vs. produção" e "testes". Perguntei se você queria escrever essas partes ou se eu deveria rascunhar — você decidiu escrever você mesmo, no seu tempo. Deixei marcadores `_(pendente)_` no arquivo para esses três eixos, para não esquecer antes da entrega final.

**Como validei**: nenhuma validação técnica aplicável (é um documento de raciocínio, não código).

**Cuidados antes de produção**: não aplicável a esta parte.

**Instrução dada (complemento)**: pediu para adicionar, ao final do documento, um parágrafo seu resumindo a abordagem geral (seguir a evidência a partir dos logs, em vez de testar tudo em paralelo). Só copiei o texto para uma seção "Abordagem geral" no fim do arquivo, sem alterar nada.

---

## Bônus — Tela de cadastro de clientes

**Instrução dada**: "O bônus eu finalizei, realizei os prints... Adicione essas informações em um novo arquivo de prompt (bonus) e comite" — você gerou a tela em uma ferramenta separada (Claude Fable 5, front-end apenas), com dois prompts (criação e redesign visual), e pediu para eu registrar os prompts, os prints e a explicação de produção em um arquivo dedicado, em vez de no `PROMPTS.md`.

**Onde a IA ajudou**: criei `docs/BONUS.md` com os dois prompts na íntegra (sem alterar o texto que você usou), copiei os dois prints de `Downloads` para `docs/bonus/` (para ficarem versionados no repositório, já que um avaliador clonando o projeto não tem acesso à sua pasta Downloads), e escrevi a explicação do que mudaria antes de produção — incluindo notar que a tela ainda não integra com o endpoint real de CEP que já existe na Parte 3 (`POST /api/enderecos`).

**Como validei**: abri os dois prints para conferir que a tela realmente cobre os campos e o comportamento pedidos no enunciado original (13 campos, obrigatórios marcados, fluxo em 3 etapas, resumo lateral) antes de escrever a explicação de produção.

Ver [docs/BONUS.md](docs/BONUS.md) para o conteúdo completo (prompts + prints + checklist de produção).

---

_A seção final (finalização/README) será adicionada conforme o desenvolvimento avança._
