# Uso de IA neste projeto

Ferramentas utilizadas: **Claude Code** (VS Code, sessão interativa — o trabalho foi feito parte por parte, com pausas para revisão e decisão a cada etapa), **ChatGPT**, **Obsidian** e **Claude Fable 5** (usado só na tela do Bônus). Detalhes de cada uma na seção "Parte 4" abaixo.

Este arquivo registra, por parte do teste: as instruções reais que dei, em que a IA ajudou, o que eu alterei do que foi gerado, como validei, e quais cuidados eu teria antes de produção.

## Parte 4 — Uso de Inteligência Artificial

### Ferramentas utilizadas

Durante o desenvolvimento do projeto foram utilizadas as seguintes ferramentas:

- **Claude.ai / Claude Code (VS Code)**: utilizado como apoio no desenvolvimento, geração de código, revisão de implementação e auxílio na resolução de problemas técnicos.
- **ChatGPT**: utilizado para criação, organização e refinamento de prompts utilizados durante o desenvolvimento.
- **Obsidian**: utilizado para estruturar a linha de raciocínio do projeto e organizar decisões técnicas.
- **Claude Fable 5**: utilizado exclusivamente para gerar a tela de cadastro de clientes do Bônus (front-end isolado, fora do escopo do Laravel) — ver `docs/BONUS.md` para os prompts completos.

Os prompts utilizados durante o desenvolvimento estão documentados neste arquivo.

### Em quais partes do teste a IA auxiliou

A IA foi utilizada como par de programação durante todo o desenvolvimento do teste, não apenas em uma etapa específica. O auxílio ocorreu nas seguintes partes:

**Parte 1 — Regras de negócio e testes**

A IA auxiliou na correção da função `calcularDesconto` e na criação dos testes automatizados. Durante essa etapa, as decisões foram definidas antes da implementação. Por exemplo, foi escolhido lançar uma exceção para cenários inválidos em vez de realizar uma correção silenciosa do valor recebido.

**Parte 2 — Banco de dados e consultas**

A IA auxiliou na criação de: migrations; models; relacionamentos; seeder; consultas SQL solicitadas. Também ajudou na organização das consultas, incluindo a separação dos valores de gastos em: valores concluídos; valores em aberto; total geral.

**Parte 3 — Integração com ViaCEP**

A IA auxiliou na construção de toda a cadeia da integração: Form Request; Service; Controller; API Resource; testes automatizados. Durante essa etapa também foi identificado e corrigido um problema no tratamento da resposta do ViaCEP, onde um CEP inexistente retornava `{"erro":"true"}` como string, fazendo com que o sistema interpretasse incorretamente a resposta e permitisse o salvamento de um CEP inválido.

**Parte 5 — Investigação de problemas**

Esta parte foi escrita por mim, com raciocínio próprio. A IA auxiliou apenas na formatação do texto, sem alterar o conteúdo ou a linha de investigação.

**Documentação**

Também houve auxílio na organização dos arquivos de documentação: `README.md`; `DECISOES.md`; documentação final do teste.

O uso da IA foi assistido e supervisionado. As decisões técnicas foram definidas antes da implementação, o código gerado foi revisado e todas as alterações foram validadas antes de serem aceitas.

### Quais alterações foram realizadas no código gerado

As principais alterações e direcionamentos realizados foram:

- Ajuste das consultas SQL para separar corretamente valores pagos, pendentes e totais, facilitando a análise dos dados.
- Definição do comportamento da função `calcularDesconto`, optando por lançar exceções em casos inválidos ao invés de alterar valores automaticamente.
- Criação de testes automatizados para garantir os cenários esperados e evitar regressões.
- Definição do relacionamento entre entidades utilizando `restrictOnDelete()`, evitando exclusões acidentais de dados relacionados.
- Implementação de API Resource na integração com ViaCEP.
- Inclusão de testes para validar diferentes cenários da integração.
- Correção do tratamento de CEP inexistente, garantindo que a aplicação retorne erro corretamente em vez de salvar informações inválidas.

As alterações não foram feitas simplesmente aceitando o código gerado pela IA. Cada implementação foi revisada, avaliada e validada antes de ser incorporada ao projeto.

### Como foi verificado se o código estava correto

A validação não foi baseada apenas na leitura do código gerado. Cada parte foi verificada conforme sua finalidade.

**Código e regras de negócio**

Nas partes envolvendo lógica de aplicação, foram executados testes automatizados utilizando `php artisan test`. Foram validados cenários como: regras de desconto; casos de borda; integração com sucesso; CEP inexistente; formato inválido; falhas de comunicação.

**Banco de dados**

Além de verificar se as migrations executavam corretamente, foi feita uma validação do banco gerado para confirmar: criação correta das tabelas; relacionamentos; constraints; regras de exclusão. As consultas foram executadas utilizando o banco populado pelo seeder e os resultados foram conferidos manualmente.

**Integração ViaCEP**

A integração foi testada inicialmente contra a API real do ViaCEP. Esse processo permitiu identificar um comportamento inesperado da API, onde CEPs inexistentes retornavam uma propriedade `"erro"` como string. Após a correção, o comportamento foi coberto por testes automatizados para evitar regressões futuras.

De forma geral, o critério utilizado para considerar uma implementação concluída foi: testes automatizados passando; comportamento validado; resultado conferido com o banco e integrações reais.

### Cuidados antes de colocar a solução em produção

Antes de realizar um deploy em produção, alguns pontos precisariam ser avaliados:

**Parte 1 — Regras de negócio**

Como o cálculo de desconto utiliza exceções para valores inválidos, seria necessário garantir que essas exceções sejam tratadas corretamente pela aplicação, evitando que sejam exibidos erros técnicos para o usuário final. Também seria importante validar com o responsável pelo negócio a regra de arredondamento utilizada.

**Parte 2 — Banco de dados**

O uso de `restrictOnDelete()` impede a exclusão de registros que possuem dependências. Caso futuramente exista uma necessidade real de exclusão de clientes, seria necessário definir uma estratégia adequada, como: soft delete; remoção controlada; fluxo específico de exclusão.

**Parte 3 — Integração ViaCEP**

Em um ambiente de produção com grande volume de acessos, seria recomendado: implementar cache de consultas de CEP; revisar valores de timeout e retry; tornar configurações externas parametrizáveis por ambiente; monitorar falhas de comunicação com a API.

**Antes do deploy final**

Também seriam realizados: execução da suíte completa de testes no pipeline; validação das variáveis de ambiente; conferência das configurações entre homologação e produção; acompanhamento dos logs após publicação.

---

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

**O que eu alterei do que a IA gerou**: As alterações que dirigi nesta parte estão consolidadas no resumo no fim do arquivo (Resumo: quais alterações realizei no código gerado). Onde não mexi — como os parâmetros `mixed` em vez de `float` tipado — foi por ter revisado o código e os testes gerados e concordado com a abordagem (evita um `TypeError` genérico do PHP em favor de mensagens de erro específicas), não por aceitar sem conferir.

**Como validei**: rodei `php artisan test --filter=DescontoServiceTest` e conferi que os 11 testes passaram (11 assertions, 0 falhas) antes de aceitar a implementação como pronta.

**Cuidados antes de produção**:
- Se este método for exposto via API (endpoint HTTP), a `InvalidArgumentException` não deve vazar como erro 500 puro — precisaria de um handler específico (ou uma Form Request antes de chamar o service) para virar uma resposta 422 com mensagem amigável.
- Validar se `mixed $valor, mixed $desconto` é aceitável no restante do projeto ou se, ao integrar com outras partes (ex.: Ordens de Serviço), faz mais sentido tipar como `int|float` e tratar `null` como caso à parte antes de chamar o service.

**Instrução dada (revisão)**: você percebeu que o método fazia conta de dinheiro em `float` sem tratar arredondamento (`calcularDesconto(19.99, 15)` devolve `16.9915`, que em float pode aparecer com "lixo" tipo `16.991499...`). Pediu para eu explicar as opções (round, BCMath, centavos como inteiro) com o trade-off de cada uma, perguntar antes de escolher, e só então documentar em `docs/DECISOES.md` — sem implementar antes de decidir.

**Onde a IA ajudou**: expliquei as 3 opções com prós/contras específicos para este projeto (ex.: BCMath quebraria os 11 testes existentes que comparam `float`; centavos criaria uma representação de dinheiro inconsistente com o resto do domínio, que usa `decimal(10,2)`); confirmei o comportamento real do float com `printf('%.20f', ...)` antes de escrever a explicação, em vez de descrever de memória.

**O que eu decidi (não a IA sozinha)**: escolhi `round($resultado, 2)` entre as três opções apresentadas.

**O que eu alterei do que a IA gerou**: As alterações que dirigi nesta parte estão consolidadas no resumo no fim do arquivo (Resumo: quais alterações realizei no código gerado) — aqui, a implementação de uma linha (`return round($valor - $valor * $desconto / 100, 2);`) e um teste novo cobrindo o caso específico.

**Como validei**: rodei `php artisan test --filter=DescontoServiceTest` (12 testes, todos passando) e depois a suíte completa (18 testes). O teste novo verifica tanto o valor (`16.99`) quanto a ausência de "lixo" decimal (`number_format($resultado, 2) === '16.99'`).

**Cuidados antes de produção**: `round()` resolve o caso de um cálculo isolado, mas se este valor entrar em somas/cálculos encadeados sem re-arredondar a cada etapa, o erro de ponto flutuante pode reaparecer — vale revisar se algum fluxo futuro (ex.: somar várias Ordens de Serviço com desconto aplicado) precisa de um cuidado adicional.

## Parte 2 — Banco de dados (Clientes, Veículos, Ordens de Serviço)

**Instrução dada**: "Podemos seguir" (após a Parte 1), seguindo a especificação já definida no briefing inicial (migrations, models com relações, `sql/consultas.sql` com as 4 consultas comentadas, seeder de exemplo).

**Onde a IA ajudou**: gerou as 3 migrations, os 3 models com as relações (`hasMany`/`belongsTo`), o `DomainDataSeeder` com dados de exemplo, e as 4 consultas em `sql/consultas.sql`; rodou as migrations e testou cada consulta manualmente contra o banco populado antes de considerar a parte pronta.

**O que eu decidi (não a IA sozinha)**:
- Perguntada sobre o critério de "aberta", escolhi um `status` com só 2 valores (`aberta`/`concluida`), evitando modelar um fluxo de 4 estados que o teste não pediu.
- Perguntada sobre exclusão em cascata vs. bloqueio, escolhi **restrict** (bloquear exclusão de Cliente/Veículo com filhos), por segurança contra apagar histórico de Ordens de Serviço por engano.

**O que eu alterei do que a IA gerou**: As alterações que dirigi nesta parte estão consolidadas no resumo no fim do arquivo (Resumo: quais alterações realizei no código gerado). Onde não mexi diretamente no código gerado, foi por ter revisado o schema gerado (`sqlite_master`), os índices únicos de `cpf`/`placa`, e o resultado das 4 consultas rodadas contra os dados do seeder, e todos bateram com o esperado.

**Decisão registrada, mas não perguntada (revisada logo abaixo)**: a IA decidiu por conta própria que a soma de "valor total gasto" (consultas 3 e 4) incluía ordens com qualquer status (não só `concluida`), por ser a leitura mais literal do enunciado ("SUM(valor) + GROUP BY", sem filtro de status mencionado). Documentado em `docs/DECISOES.md` como uma leitura alternativa possível. Essa leitura foi revisada logo em seguida — ver "Instrução dada (revisão)" abaixo, onde pedi para separar em gasto concluído, em aberto e total.

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

**O que eu alterei do que a IA gerou**: As alterações que dirigi nesta parte estão consolidadas no resumo no fim do arquivo (Resumo: quais alterações realizei no código gerado). Revisei o fluxo completo, e o bug do `erro: "true"` foi corrigido pela própria IA após o teste manual expor o problema, antes de eu precisar apontar.

**Como validei**: (1) testes manuais reais contra o ViaCEP via `php artisan serve` + `Invoke-WebRequest`, cobrindo os 4 status esperados (201, 200, 404, 422); (2) 6 testes automatizados com `Http::fake` cobrindo os mesmos caminhos mais a falha de integração (500) e a regressão do `erro` como string; (3) suíte completa do projeto rodada no final (18 testes, todos passando).

**Cuidados antes de produção**:
- O timeout de 5s e o retry de 2 tentativas são valores razoáveis para um teste, mas devem ser calibrados com dados reais de latência do ViaCEP em produção (talvez via config, não hardcoded no Service).
- Como a integração sempre consulta o ViaCEP de novo mesmo para um CEP já salvo (para manter o endereço atualizado), em alto volume isso significa uma chamada externa por requisição — se performance for um problema, valeria adicionar uma janela de cache (ex.: só re-consultar se o registro tiver mais de X dias).
- O CA bundle (`cacert.pem`) foi baixado manualmente nesta máquina de desenvolvimento; em um ambiente de produção real, isso é responsabilidade da imagem/infra (Docker base image, servidor gerenciado), não algo para versionar no projeto.

---

## Parte 5 — Investigação de problemas

**Instrução dada**: eu primeiro pedi um resumo/roteiro dos eixos a cobrir (não o texto final, já que o enunciado pede que essa parte "soe como eu"). Depois, você escreveu o raciocínio completo com suas próprias palavras (perguntas ao cliente, logs, API externa, banco de dados, alterações recentes e deploy) e me pediu para "adicionar em investigação.md".

**Onde a IA ajudou**: no primeiro pedido, gerei apenas um resumo/roteiro dos eixos exigidos pelo enunciado, como ponto de partida — não um rascunho pronto, porque você deixou claro que essa parte é pessoal. Depois, só formatei o texto que você mesmo escreveu em `docs/INVESTIGACAO.md` (títulos de seção, lista numerada), sem alterar o conteúdo ou o tom.

**O que eu identifiquei, mas não decidi sozinho**: o enunciado pede para cobrir *todos* os eixos (informações do cliente, logs, API externa, banco, código-fonte, alterações recentes, deploy, variáveis de ambiente, homologação vs. produção, testes). Na primeira versão, faltavam três: "variáveis de ambiente", "homologação vs. produção" e "testes". Perguntei se você queria escrever essas partes ou se eu deveria rascunhar — você decidiu escrever você mesmo. Os três eixos já estão escritos por você em `docs/INVESTIGACAO.md`, concluindo a Parte 5.

**Como validei**: nenhuma validação técnica aplicável (é um documento de raciocínio, não código).

**Cuidados antes de produção**: não aplicável a esta parte.

**Instrução dada (complemento)**: pediu para adicionar, ao final do documento, um parágrafo seu resumindo a abordagem geral (seguir a evidência a partir dos logs, em vez de testar tudo em paralelo). Só copiei o texto para uma seção "Abordagem geral" no fim do arquivo, sem alterar nada.

---

## Bônus — Tela de cadastro de clientes

**Instrução dada**: "O bônus eu finalizei, realizei os prints... Adicione essas informações em um novo arquivo de prompt (bonus) e comite" — você gerou a tela em uma ferramenta separada (Claude Fable 5, front-end apenas), com dois prompts (criação e redesign visual), e pediu para eu registrar os prompts, os prints e a explicação de produção em um arquivo dedicado, em vez de no `PROMPTS.md`.

**Onde a IA ajudou**: criei `docs/BONUS.md` com os dois prompts na íntegra (sem alterar o texto que você usou), copiei os dois prints de `Downloads` para `docs/bonus/` (para ficarem versionados no repositório, já que um avaliador clonando o projeto não tem acesso à sua pasta Downloads), e escrevi a explicação do que mudaria antes de produção — incluindo notar que a tela ainda não integra com o endpoint real de CEP que já existe na Parte 3 (`POST /api/enderecos`).

**Como validei**: abri os dois prints para conferir que a tela realmente cobre os campos e o comportamento pedidos no enunciado original (13 campos, obrigatórios marcados, fluxo em 3 etapas, resumo lateral) antes de escrever a explicação de produção.

**Instrução dada (complemento)**: pediu para adicionar também o "prompt puro do projeto bônus" (na verdade, o código-fonte gerado, `CadastroCliente.html`) junto com os docs do projeto — copiei para `docs/bonus/CadastroCliente.html` e referenciei em `docs/BONUS.md`. Não toquei em `docs/DECISOES.md`, como você já tinha deixado claro antes que esse arquivo não faz parte da documentação técnica do Laravel.

**Instrução dada (complemento 2)**: você escreveu, com suas próprias palavras, a análise completa do que melhoraria antes de produção (CPF sem dígito verificador, ausência total de backend/segurança/LGPD, a distinção entre validação client-side como UX vs. segurança real, e o roadmap em ordem de prioridade). Pediu para eu anotar "nos docs de prompts e decisões" — copiei seu texto para substituir a seção "O que eu mudaria antes de produção" em `docs/BONUS.md` (que era só um rascunho meu, bem mais raso), sem alterar o conteúdo. Mantive fora de `docs/DECISOES.md`, pelo mesmo motivo de sempre: esse arquivo é sobre as decisões técnicas do projeto Laravel, não sobre o front-end à parte do Bônus.

**Como validei**: nenhuma validação técnica aplicável — é uma análise textual, não código.

Ver [docs/BONUS.md](docs/BONUS.md) para o conteúdo completo (prompts + prints + checklist de produção).

---

## README final

**Instrução dada**: "readme final".

**Onde a IA ajudou**: expandiu o README esqueleto (que só tinha requisitos, `.env` e migrations) para a versão final: seção "Sobre o projeto" resumindo as 5 partes + bônus, instruções de instalação do zero (`git clone` + `composer install`), seção de testes (`php artisan test`, 18 testes), documentação do endpoint da API (`POST /api/enderecos` com tabela de status esperados), link para `sql/consultas.sql`, e um resumo de decisões técnicas cobrindo as partes 1–3 (antes só mencionava Laravel/SQLite do setup).

**Como validei**: rodei `php artisan test` (18 testes, todos passando) e `php artisan route:list --path=api` para confirmar que os números e a rota citados no README batem com o estado real do projeto antes de escrever.

**Cuidados antes de produção**: não aplicável — é documentação, não código.

---

## Resumo: quais alterações realizei no código gerado

Não editei o código linha a linha. Intervim dirigindo decisões, pedindo mudanças e validando cada saída antes de aceitar. As principais foram:

- **Consultas 3 e 4** (Parte 2): pedi para separar o `SUM(valor)` em três colunas (gasto concluído, gasto em aberto e o total dos dois), para distinguir o que já foi pago do que ainda está em aberto.
- **Parte 1**: decidi lançar exceção em vez de corrigir o valor silenciosamente, e cobrir tudo com testes automatizados em vez de lista manual.
- **Parte 2**: defini o status com dois valores e usei `restrictOnDelete()` no lugar de cascata, para não apagar histórico por engano.
- **Parte 3**: pedi API Resource e testes, e direcionei a correção do bug em que o ViaCEP retornava `{"erro":"true"}` como string, o que fazia o sistema salvar um CEP inexistente em vez de responder 404.

Onde não mexi no código, foi por ter revisado o que foi gerado e concordado, não por aceitar sem conferir.

## Nota sobre a contagem de testes

O projeto tinha 20 testes até este ponto, mas 2 deles eram `ExampleTest.php` (um em `tests/Unit`, outro em `tests/Feature`) — scaffolding padrão gerado pelo `laravel/laravel` na instalação, que não testam nada específico desta aplicação. Foram removidos para a contagem refletir só testes com propósito real: **18 testes**, todos escritos para cobrir os cenários das Partes 1 e 3. Todas as menções de contagem neste arquivo, em `README.md` e em `docs/DECISOES.md` foram atualizadas para 18.
