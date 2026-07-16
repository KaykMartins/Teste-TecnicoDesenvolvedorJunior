# HyperGestor

Teste técnico — Desenvolvedor Júnior (projeto de sustentação). API Laravel para gestão de clientes, veículos e ordens de serviço, incluindo integração com a API pública ViaCEP.

> Este README será completado ao longo do desenvolvimento (uma seção por parte do teste). Por enquanto, contém apenas o esqueleto inicial.

## Requisitos para execução

- PHP: 8.3.32
- Composer: 2.10.2
- Laravel Framework: 13.20.0
- Banco de dados: SQLite

## Como configurar o `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Banco de dados (SQLite, já é o padrão do `.env.example` do Laravel 13):

```
DB_CONNECTION=sqlite
```

O arquivo `database/database.sqlite` precisa existir (vazio) antes de rodar as migrations:

```bash
type nul > database\database.sqlite   # Windows (cmd)
# ou
New-Item -ItemType File database/database.sqlite   # PowerShell
```

## Como executar as migrations

```bash
php artisan migrate
```

## Como iniciar o projeto

```bash
php artisan serve
```

## Principais decisões técnicas

Ver [docs/DECISOES.md](docs/DECISOES.md) para o detalhamento de cada decisão (o porquê, as alternativas consideradas e o trade-off). Resumo:

- **Banco de dados: SQLite** — zero configuração, ideal para um teste técnico avaliado localmente, sem depender de um servidor externo.
- **Laravel 13** (framework 13.20.0) — versão estável mais recente disponível via Composer no momento da criação do projeto.

## Documentação do processo

- [docs/DECISOES.md](docs/DECISOES.md) — decisões técnicas e seus porquês, por parte do teste.
- [docs/INVESTIGACAO.md](docs/INVESTIGACAO.md) — raciocínio de investigação de problemas (Parte 5).
- [docs/BONUS.md](docs/BONUS.md) — prompts, prints e explicação da tela de cadastro de clientes (front-end à parte, sem backend).
- [PROMPTS.md](PROMPTS.md) — uso de IA no desenvolvimento deste projeto.
- [sql/consultas.sql](sql/consultas.sql) — consultas SQL da Parte 2.
