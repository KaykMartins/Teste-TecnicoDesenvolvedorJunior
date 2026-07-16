# HyperGestor

Teste técnico — Desenvolvedor Júnior (projeto de sustentação). API Laravel para gestão de Clientes, Veículos e Ordens de Serviço, incluindo integração com a API pública ViaCEP.

## Sobre o projeto

O projeto cobre 5 partes de um teste técnico:

1. **Correção de bug** — `App\Services\DescontoService::calcularDesconto()`, com validação de entrada (exceções) e arredondamento monetário.
2. **Banco de dados** — migrations e models de Clientes, Veículos e Ordens de Serviço, com relações (`hasMany`/`belongsTo`) e 4 consultas SQL (`sql/consultas.sql`).
3. **Integração com API pública (ViaCEP)** — endpoint `POST /api/enderecos` que consulta, valida, salva/atualiza e trata erros de um CEP.
4. **Uso de IA documentado** — ver [PROMPTS.md](PROMPTS.md).
5. **Investigação de problemas** — raciocínio estruturado em [docs/INVESTIGACAO.md](docs/INVESTIGACAO.md).

Mais um **Bônus**: tela de cadastro de clientes (front-end isolado, sem backend) — ver [docs/BONUS.md](docs/BONUS.md).

## Requisitos para execução

- **PHP**: 8.3.32 (testado; qualquer 8.2+ deve funcionar)
- **Composer**: 2.10.2
- **Laravel Framework**: 13.20.0
- **Banco de dados**: SQLite (zero configuração — ver justificativa em [docs/DECISOES.md](docs/DECISOES.md))

Extensões PHP necessárias (algumas não vêm habilitadas por padrão em instalações via winget/Windows): `openssl`, `pdo_sqlite`, `mbstring`, `curl`, `fileinfo`, `zip`.

> **Nota (Windows)**: se a integração com o ViaCEP (Parte 3) falhar com `cURL error 60` (certificado SSL), o PHP não tem um CA bundle configurado. Baixe o `cacert.pem` oficial em [curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem) e aponte `curl.cainfo` e `openssl.cafile` para ele no `php.ini`. Detalhes em [docs/DECISOES.md](docs/DECISOES.md) (Parte 3).

## Instalação

```bash
git clone https://github.com/KaykMartins/Teste-TecnicoDesenvolvedorJunior.git hypergestor
cd hypergestor
composer install
```

## Como configurar o `.env`

```bash
cp .env.example .env
php artisan key:generate
```

O `.env.example` já vem configurado para SQLite:

```
DB_CONNECTION=sqlite
```

Crie o arquivo do banco (vazio) antes de rodar as migrations:

```bash
# PowerShell
New-Item -ItemType File database/database.sqlite

# bash/Linux/macOS
touch database/database.sqlite
```

## Como executar as migrations

```bash
php artisan migrate
```

Para já popular o banco com dados de exemplo (necessário para testar as consultas de `sql/consultas.sql` com resultados reais):

```bash
php artisan migrate --seed
# ou, para recomeçar do zero a qualquer momento:
php artisan migrate:fresh --seed
```

## Como iniciar o projeto

```bash
php artisan serve
```

A aplicação sobe em `http://127.0.0.1:8000`.

## Rodando os testes

```bash
php artisan test
```

20 testes automatizados (PHPUnit): validação/arredondamento do `DescontoService` (Parte 1) e o fluxo completo da integração com ViaCEP via `Http::fake` (Parte 3) — sucesso, sanitização de CEP, atualização sem duplicar, CEP inexistente e falha de integração.

## Endpoint da API (Parte 3 — ViaCEP)

```
POST /api/enderecos
Content-Type: application/json

{ "cep": "13289-180" }
```

| Cenário | Status | Corpo (resumo) |
|---|---|---|
| CEP válido, novo | `201` | endereço criado |
| CEP válido, já salvo | `200` | endereço atualizado |
| CEP com 8 dígitos mas inexistente | `404` | `{"message": "CEP ... não foi encontrado."}` |
| Formato inválido (≠ 8 dígitos após sanitizar) | `422` | erros de validação |
| Falha na integração com o ViaCEP | `500` | mensagem genérica + log em `storage/logs/laravel.log` |

## Consultas SQL (Parte 2)

Ver [sql/consultas.sql](sql/consultas.sql) — as 4 consultas comentadas (veículos de um cliente, ordens de serviço abertas, total gasto por cliente separado em concluído/em aberto, top 5 clientes por valor gasto).

## Principais decisões técnicas

Ver [docs/DECISOES.md](docs/DECISOES.md) para o detalhamento de cada decisão (o porquê, as alternativas consideradas e o trade-off). Resumo:

- **Banco de dados: SQLite** — zero configuração, ideal para um teste técnico avaliado localmente, sem depender de um servidor externo.
- **Laravel 13** (framework 13.20.0) — versão estável mais recente disponível via Composer no momento da criação do projeto.
- **`calcularDesconto` lança exceção** (`InvalidArgumentException`) para entradas inválidas, em vez de corrigir silenciosamente (clamp) — e arredonda o resultado para 2 casas decimais.
- **Status de Ordem de Serviço com 2 valores** (`aberta`/`concluida`) e **`restrictOnDelete()`** nas chaves estrangeiras (bloqueia exclusão de Cliente/Veículo com filhos, em vez de cascata).
- **ViaCEP**: `updateOrCreate()` para nunca duplicar um CEP, `Http::timeout()` + `retry()`, Service dedicado isolando a chamada externa, checagem do corpo da resposta (`{"erro": true}` — e também `"true"` como string, um bug real encontrado e corrigido) mesmo com HTTP 200.

## Documentação do processo

- [docs/DECISOES.md](docs/DECISOES.md) — decisões técnicas e seus porquês, por parte do teste.
- [docs/INVESTIGACAO.md](docs/INVESTIGACAO.md) — raciocínio de investigação de problemas (Parte 5).
- [docs/BONUS.md](docs/BONUS.md) — prompts, prints e explicação da tela de cadastro de clientes (front-end à parte, sem backend).
- [PROMPTS.md](PROMPTS.md) — uso de IA no desenvolvimento deste projeto.
- [sql/consultas.sql](sql/consultas.sql) — consultas SQL da Parte 2.
