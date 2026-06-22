# 01 · Getting Started

[← Índice](README.md)

## Requisitos

| Ferramenta | Versão | Observação |
|---|---|---|
| PHP | 8.4 (mínimo 8.2) | extensões: `pdo_sqlite`, `mbstring`, `openssl`, `curl`, `fileinfo`, `zip`, `gd`, `bcmath` |
| Composer | 2.x | nesta máquina, via `composer.phar` na raiz |
| SQLite | — | banco padrão (zero configuração) |
| Redis | opcional | usado pelo cache de inventário; cai no store default se ausente |
| Node | opcional | só para tooling de front, não usado pela API |

## Instalação

> Como o Composer não está no PATH desta máquina, use o `composer.phar` da raiz.

```bash
# 1. Dependências
php composer.phar install

# 2. Chave da aplicação (o .env já vem com SQLite configurado)
php artisan key:generate

# 3. Banco de dados
php artisan migrate

# 4. Servir a API
php artisan serve         # http://localhost:8000
```

### Particularidades deste projeto

- **Migrations vivem em `src/Infrastructure/Persistence/Migrations`** (não em
  `database/migrations`). São carregadas pelo `AppServiceProvider::boot()` via
  `loadMigrationsFrom(...)`.
- O autoload mapeia `Stockr\` → `src/` (PSR-4 no `composer.json`).
- A política de *security advisories* do Composer 2.10 está desativada no
  `composer.json` (`policy.advisories.block: false`) para permitir instalar o
  Laravel 11 conforme requisito do projeto.

## Variáveis de ambiente relevantes

| Variável | Padrão | Função |
|---|---|---|
| `DB_CONNECTION` | `sqlite` | banco |
| `CACHE_STORE` | `database` | store de cache padrão |
| `INVENTORY_CACHE_STORE` | _(vazio)_ | store dedicado do cache de inventário; se vazio usa `redis`, senão o default |
| `APP_URL` | `http://localhost` | base usada em e-mails/links |

## Primeiro request (smoke test)

```bash
# Registrar — cria usuário + workspace e devolve um token
curl -s -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Thiago","email":"t@example.com","password":"secret123","workspace_name":"Jeito Frio"}'
```

A resposta traz `token` e `workspaceIds`. Use-os nos próximos requests:

```bash
curl -s -X POST http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer <TOKEN>" \
  -H "X-Workspace-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{"name":"Compressor","cost_price":1200.50,"initial_stock":10,"minimum_stock":4}'
```

## Comandos do dia a dia

```bash
# Testes
php vendor/pestphp/pest/bin/pest

# Análise estática (PHPStan nível 8)
php vendor/phpstan/phpstan/phpstan.phar analyse

# Recriar o banco do zero
php artisan migrate:fresh

# Exportar o OpenAPI para arquivo
php artisan scramble:export

# Listar rotas da API
php artisan route:list --path=api
```

Próximo: **[02 · Arquitetura →](02-architecture.md)**
