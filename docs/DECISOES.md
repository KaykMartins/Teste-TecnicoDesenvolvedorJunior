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

_As próximas seções (Parte 1 em diante) serão adicionadas conforme o desenvolvimento avança._
