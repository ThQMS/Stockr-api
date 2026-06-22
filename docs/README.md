# Documentação — Stockr API

API REST de gestão de estoque multi-workspace, construída em **Laravel 11 / PHP 8.4**
com **DDD em 4 camadas**, PHPStan nível 8 e testes em Pest.

Esta pasta contém a documentação completa do projeto. Comece pelo índice abaixo.

## Índice

| # | Documento | Conteúdo |
|---|-----------|----------|
| 01 | [Getting Started](01-getting-started.md) | Requisitos, instalação, ambiente, como rodar |
| 02 | [Arquitetura](02-architecture.md) | Camadas DDD, regras de dependência, diagramas, decisões |
| 03 | [Modelo de Domínio](03-domain-model.md) | Entidades, Value Objects, eventos, regras de negócio |
| 04 | [Referência da API](04-api-reference.md) | Todos os endpoints, payloads, erros |
| 05 | [Autenticação & Autorização](05-authentication.md) | Sanctum, workspace, Policies |
| 06 | [Banco de Dados](06-database.md) | Schema, tabelas, migrations |
| 07 | [Testes & Qualidade](07-testing.md) | Pest, PHPStan, convenções |

## Visão rápida

- **Domínio puro** em `src/Domain` (zero dependência de framework).
- **Casos de uso** em `src/Application` (1 método `execute`, recebe/retorna DTO).
- **Infraestrutura** em `src/Infrastructure` (Eloquent, Redis, Sanctum, QR).
- **Apresentação** em `src/Presentation` (controllers finos, Form Requests, Resources).
- Autenticação via **token Sanctum**; workspace ativo via header **`X-Workspace-Id`**.
- Documentação OpenAPI automática em **`/docs/api`** (Scramble).

> **Nota de ambiente:** nesta máquina o Composer não está no PATH — use o
> `composer.phar` da raiz: `php composer.phar <comando>`.
