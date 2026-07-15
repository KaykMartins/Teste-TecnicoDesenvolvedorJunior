# Decisões técnicas

Este documento registra as decisões tomadas ao longo do teste técnico: qual era o problema, quais opções existiam, por que escolhemos uma delas, e qual o trade-off aceito.

## Setup inicial

**Ambiente**: a máquina de desenvolvimento não tinha PHP, Composer nem Git instalados. Foram instalados via `winget` (Git, PHP 8.3) e via instalador oficial `getcomposer.org` (Composer, com verificação de assinatura SHA-384 do instalador antes de executá-lo — prática recomendada pela própria documentação do Composer para evitar rodar um script baixado da internet sem checagem de integridade).

**Extensões PHP**: o `php.ini` não existia por padrão (instalação via winget não cria um automaticamente). Foi criado a partir do template `php.ini-production` com as extensões necessárias para Laravel + Composer habilitadas: `openssl` (obrigatória para o Composer baixar pacotes via HTTPS), `mbstring`, `pdo_sqlite`, `pdo_mysql`, `curl`, `fileinfo`, `zip`, `gd`, `intl`, `gmp`, `mysqli`, `sqlite3`.

**Versão do Laravel**: o comando `composer create-project laravel/laravel .` instalou a versão mais recente disponível no Packagist no momento (Laravel Framework 13.20.0), mais nova que a "11 ou 12" mencionada inicialmente no briefing do teste. Optamos por manter a 13 por ser a versão estável atual de fato — o objetivo do teste é demonstrar Laravel atual, e fixar artificialmente em uma versão anterior não traria benefício. Essa decisão foi apresentada e confirmada antes de prosseguir.

- **Alternativa considerada**: fixar em `"^12"` no `composer create-project` para bater literalmente com o texto do briefing.
- **Trade-off**: usar a 13 significa que qualquer diferença de sintaxe/estrutura em relação a tutoriais baseados em Laravel 11/12 deve ser considerada; em compensação, é a versão que qualquer avaliador rodando `composer create-project laravel/laravel` hoje também receberia.

**Banco de dados: SQLite**. O instalador do Laravel já configura SQLite por padrão desde a versão 11 (cria `database/database.sqlite` e ajusta o `.env` automaticamente), e essa escolha foi confirmada explicitamente.

- **Alternativa considerada**: MySQL, para simular mais de perto um ambiente de produção/sustentação.
- **Por que não**: exigiria um servidor MySQL rodando na máquina (nenhum foi encontrado), mais passos de configuração no `.env`, e nenhum ganho real de avaliação para um teste que roda localmente — o enunciado inclusive já sugere SQLite. O uso de `SUM`/`GROUP BY`/`JOIN` nas consultas da Parte 2 funciona da mesma forma em ambos.
- **Trade-off aceito**: em um cenário real de sustentação com múltiplos acessos concorrentes, MySQL seria mais adequado; para este teste, a simplicidade do SQLite pesa mais.

---

## Parte 1 — Correção do `calcularDesconto`

**O problema real não era a fórmula.** A precedência de operadores em `$valor - $valor * $desconto / 100` já está correta em PHP (`*` e `/` são avaliados antes do `-`). O bug relatado ("valor negativo, desconto maior que 100%, valor nulo, desconto nulo") é sobre a ausência total de validação de entrada — o método original aceita qualquer coisa e devolve um número sem sentido (ex.: desconto 150% devolve um valor negativo; `null` vira `0` silenciosamente via coerção de tipos do PHP).

**Onde o método mora**: criei `app/Services/DescontoService.php` em vez de deixar a lógica solta em um Controller. Não existe ainda um Controller óbvio para "desconto" no domínio do teste (Clientes/Veículos/Ordens de Serviço), e mesmo que existisse, regra de negócio pura (sem acesso a request/response) fica mais fácil de testar isoladamente numa classe de serviço — é só instanciar e chamar, sem precisar simular uma requisição HTTP.

**Decisão: lançar exceção em vez de fazer clamp.** Apresentei as duas opções e ficou definido lançar `InvalidArgumentException` para cada caso inválido (nulo, não numérico, valor negativo, desconto fora de 0–100), em vez de "corrigir" silenciosamente o valor (ex.: desconto 150 virar 100).

- **Por que exceção e não clamp**: em um sistema de sustentação, um desconto de 150% enviado por engano é sintoma de um bug em algum outro lugar (formulário, integração, cálculo anterior). Se o método "conserta" isso na surdina, o bug do chamador nunca aparece — ele só é descoberto quando alguém audita valores errados no banco, o que é bem mais caro do que descobrir na hora com uma exceção.
- **Trade-off aceito**: quem chama `calcularDesconto` precisa estar preparado para capturar a exceção (ou deixá-la subir e virar um erro 500/handler global) — dá mais trabalho no chamador do que simplesmente confiar que o método sempre devolve um número. Julgamos que esse trabalho extra é aceitável porque força decisões explícitas de tratamento de erro em vez de mascarar dados inconsistentes.

**Tipagem dos parâmetros**: os parâmetros são `mixed $valor, mixed $desconto` (sem type hint `float`) de propósito. Se fossem tipados como `float`, passar `null` ou uma string não numérica geraria um `TypeError` genérico do próprio PHP, com uma mensagem técnica (ex.: "must be of type float, null given") em vez de uma mensagem de negócio clara. Validando manualmente dentro do método, cada caso (nulo, não numérico, negativo, fora de faixa) tem sua própria mensagem em português, mais fácil de entender e de tratar no chamador.

**Testes**: decidimos por testes automatizados (PHPUnit, que já vem configurado no projeto) em vez de uma lista manual, já que a lógica é pequena, isolada e o custo de escrever os testes é baixo. Criado `tests/Unit/DescontoServiceTest.php` cobrindo:

| Caso | Entrada | Resultado esperado |
|---|---|---|
| Desconto normal | `100, 10` | `90.0` |
| Valor zero | `0, 10` | `0.0` |
| Desconto zero | `100, 0` | `100.0` |
| Desconto 100% | `100, 100` | `0.0` |
| Desconto 101% | `100, 101` | `InvalidArgumentException` |
| Desconto negativo | `100, -1` | `InvalidArgumentException` |
| Valor negativo | `-100, 10` | `InvalidArgumentException` |
| Valor nulo | `null, 10` | `InvalidArgumentException` |
| Desconto nulo | `100, null` | `InvalidArgumentException` |
| Valor não numérico | `'abc', 10` | `InvalidArgumentException` |
| Desconto não numérico | `100, 'abc'` | `InvalidArgumentException` |

Rodado com `php artisan test --filter=DescontoServiceTest` — 11 testes, 11 passando.

## Parte 2 — Banco de dados (Clientes, Veículos, Ordens de Serviço)

**Critério de "aberta" (decisão sua)**: o campo `status` de `ordens_servico` tem apenas dois valores possíveis, via `enum('status', ['aberta', 'concluida'])`: `aberta` e `concluida`. A consulta obrigatória #2 filtra literalmente `status = 'aberta'` — não há ambiguidade porque não existe um terceiro estado (como "em andamento" ou "cancelada") que pudesse ser confundido com "aberta".

- **Alternativa que ficou de fora**: um fluxo de 4 estados (aberta / em_andamento / concluida / cancelada), mais realista para uma oficina, mas que exigiria decidir se "aberta" significa só o valor inicial ou qualquer coisa não finalizada — complexidade que o teste não pediu.

**Comportamento de exclusão em cascata (decisão sua)**: as chaves estrangeiras usam `restrictOnDelete()` — o banco impede excluir um Cliente que ainda tenha Veículos, ou um Veículo que ainda tenha Ordens de Serviço.

- **Por quê**: em um sistema de sustentação, um `DELETE` acidental de um Cliente não deve apagar silenciosamente o histórico de Ordens de Serviço (que representa valores cobrados/faturados). Se alguém realmente precisar excluir um cliente, precisa remover os veículos e ordens primeiro — uma ação deliberada, não um efeito colateral.
- **Trade-off aceito**: exclusões legítimas (ex.: LGPD, cliente pediu para apagar os dados) dão mais trabalho — seria necessário um fluxo específico (soft delete ou exclusão em cascata controlada por código, não pelo banco) se isso vier a ser um requisito real.

**Nome da tabela de Ordens de Serviço**: o Laravel, ao gerar a migration a partir do nome do model `OrdemServico`, sugere por convenção a tabela `ordem_servicos`. Preferimos `ordens_servico` (mais próximo de como o domínio é descrito no enunciado: "Ordens de Serviço"), então defini `protected $table = 'ordens_servico';` explicitamente no model.

**Coluna `updated_at`**: o schema pedido no enunciado lista apenas `id, veiculo_id, valor, status, created_at` para Ordens de Serviço — sem `updated_at`. Segui essa especificação literalmente (`$table->timestamp('created_at')->useCurrent();`, sem `$table->timestamps()`) e configurei `const UPDATED_AT = null;` no model para o Eloquent não tentar gravar uma coluna inexistente ao salvar. Vale registrar o trade-off: sem `updated_at`, não há como saber quando uma OS mudou de "aberta" para "concluida" só olhando a tabela — para o escopo deste teste isso não é necessário, mas seria a primeira coisa a adicionar num cenário real de sustentação.

**CPF e placa únicos**: adicionei `unique()` nas migrations de `clientes.cpf` e `veiculos.placa`, mesmo sem o enunciado exigir explicitamente (diferente do `cep` da Parte 3, que é unique por especificação). Não tratei isso como uma decisão a perguntar porque é uma regra de integridade óbvia do domínio — dois clientes não têm o mesmo CPF, duas placas não se repetem.

**"Total gasto" separado em concluído vs. em aberto (revisão pedida)**: a primeira versão das consultas 3 e 4 somava tudo junto (`SUM(valor)`, sem distinguir status). Depois de revisar, ficou definido separar os dois: cada consulta agora traz `gasto_concluido` (soma onde `status = 'concluida'`), `gasto_em_aberto` (soma onde `status = 'aberta'`) e `total_gasto` (soma dos dois), usando `SUM(CASE WHEN ... THEN valor ELSE 0 END)` para cada coluna. A ordenação do top 5 (consulta 4) continua pelo `total_gasto` (concluído + em aberto), não só pelo concluído — é a métrica que reflete o cliente que mais movimenta a oficina no geral.

**Seeder (`DomainDataSeeder`)**: criei 7 clientes com quantidades variadas de veículos e ordens de serviço (incluindo um cliente propositalmente sem nenhum veículo, para testar o caso de borda da consulta 1) para que as 4 consultas em `sql/consultas.sql` pudessem ser validadas com resultados reais, não hipotéticos. Rodado via `php artisan migrate:fresh --seed` e cada consulta foi executada manualmente contra o banco para conferir o resultado antes de considerar a parte concluída.

---

## Parte 3 — Integração com ViaCEP

**Diferenciais incluídos** (todos os "recomendados" do briefing): `updateOrCreate()`, `Http::timeout()` + `retry()`, Form Request para validação, Service para isolar a chamada externa, API Resource e testes automatizados (os dois últimos, decididos com você antes de começar).

**Arquitetura**: `BuscarEnderecoRequest` (sanitiza + valida o formato) → `EnderecoController::store` (orquestra o fluxo) → `ViaCepService::consultar` (isola a chamada HTTP externa, único ponto que sabe que a integração é com o ViaCEP) → `Endereco::updateOrCreate` (persiste sem duplicar) → `EnderecoResource` (formata a resposta).

- **`updateOrCreate()` vs. `firstOrCreate()`**: `firstOrCreate(['cep' => $cep], $dados)` busca por `cep`; se existir, **ignora** `$dados` e devolve o registro como está. `updateOrCreate(['cep' => $cep], $dados)` busca por `cep`; se existir, **atualiza** com `$dados`. Como o enunciado pede "salvar **ou atualizar** sem duplicar", e um mesmo CEP pode ter o logradouro corrigido pelos Correios com o tempo, `updateOrCreate` é o correto aqui — `firstOrCreate` deixaria o endereço desatualizado para sempre depois do primeiro salvamento.

- **Sanitização no Form Request (`prepareForValidation`)**: o CEP chega sujo (`13.289-180`, `13289 180`, etc.), é limpo com `preg_replace('/[^0-9]/', '', ...)` **antes** da regra `digits:8` rodar. Assim o controller e o Service só recebem CEP já limpo (8 dígitos), e o `unique` na migration nunca vê duas grafias do mesmo CEP como registros diferentes.

- **Critério de "CEP não encontrado" (bug real encontrado e corrigido)**: a primeira versão comparava `$dados['erro'] === true` (booleano estrito). Testando manualmente com um CEP fora de qualquer faixa válida (`99999999`), descobri que o ViaCEP responde `{"erro": "true"}` — **string**, não booleano — nesse caso. A comparação estrita não capturava isso, e o CEP inexistente estava sendo salvo no banco como um endereço vazio (bug: retornava 201 em vez de 404). Corrigido com `filter_var($dados['erro'] ?? false, FILTER_VALIDATE_BOOLEAN)`, que trata `true`, `"true"` e `"1"` da mesma forma. Ficou registrado como teste automatizado (`test_cep_inexistente_com_erro_como_string_tambem_retorna_404`) para não regredir.

- **`Http::timeout(5)->retry(2, 200, throw: false)`**: timeout de 5s evita que uma instabilidade do ViaCEP trave a aplicação indefinidamente. `retry(2, 200)` tenta mais 2 vezes (200ms de intervalo) em caso de falha de conexão. `throw: false` é deliberado: sem isso, o próprio Laravel lançaria uma exception genérica ao esgotar as tentativas; com `throw: false`, o método sempre devolve uma `Response`, e o código verifica `$response->failed()` explicitamente — controle de fluxo mais claro e uma mensagem de erro específica da aplicação, não uma exception genérica do cliente HTTP.

- **`ViaCepIndisponivelException` (Service) vs. retorno `null` (Controller)**: o Service **lança exceção** para falha de integração (timeout, conexão, HTTP 5xx/4xx do ViaCEP) — isso é "algo deu errado". Já **CEP não encontrado é um resultado válido, não um erro** — o Service retorna `null`, e o Controller decide o 404. Misturar os dois na mesma exceção obrigaria o Controller a inspecionar a mensagem da exception para saber se é 404 ou 500, o que é frágil.

- **Log dentro do `try/catch` do Service**: `Log::error()` roda no Service (onde a exception original e o CEP consultado estão disponíveis), não no Controller — assim o log tem o contexto técnico completo (mensagem da exception original) mesmo que o Controller só devolva uma mensagem genérica ao cliente da API.

- **Campos `logradouro`, `bairro`, `cidade`, `estado`, `complemento` nullable** na migration: o ViaCEP pode devolver alguns desses campos vazios para determinados CEPs (ex.: `complemento` quase sempre vem vazio; CEPs que cobrem uma cidade inteira podem não ter `logradouro`). Tratar como obrigatório quebraria o insert nesses casos.

- **Rota**: `POST /api/enderecos` recebendo `{"cep": "..."}` no corpo — criei `routes/api.php` do zero (o skeleton do Laravel 13 não vem mais com esse arquivo por padrão) e registrei em `bootstrap/app.php`.

- **Ambiente**: a integração falhava com erro de certificado SSL (`cURL error 60`) porque a instalação do PHP via winget não vem com um CA bundle configurado. Resolvido baixando o `cacert.pem` oficial da cURL e apontando `curl.cainfo`/`openssl.cainfo` para ele no `php.ini` — a alternativa (desabilitar a verificação SSL) nunca foi considerada, por ser uma falha de segurança real, não um workaround aceitável.

**Testes automatizados** (`tests/Feature/EnderecoControllerTest.php`, usando `Http::fake()` para não depender da API real): formato inválido (422, sem sequer chamar o ViaCEP), sanitização de CEP com pontuação, atualização sem duplicar, CEP inexistente com `erro: true` e com `erro: "true"` (o bug corrigido), e falha de integração (500). 19 testes no total do projeto, todos passando.

---

_As próximas seções (Parte 4 em diante) serão adicionadas conforme o desenvolvimento avança._
