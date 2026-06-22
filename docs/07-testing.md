# 07 · Testes & Qualidade

[← Índice](README.md)

## Stack de qualidade

| Ferramenta | Para quê |
|---|---|
| **Pest** | Testes (unit, feature, arquitetura) |
| **Larastan / PHPStan nível 8** | Análise estática |
| **Laravel Pint** | Code style (PSR-12) |
| **IDE Helper** | Autocompletar (dev) |

Estado atual: **28 testes / 77 asserts** verdes; PHPStan nível 8 sem erros.

## Rodando

```bash
# Testes
php vendor/pestphp/pest/bin/pest
php vendor/pestphp/pest/bin/pest --filter=ProductUseCasesTest   # um arquivo
php vendor/pestphp/pest/bin/pest tests/Unit                     # uma pasta

# Análise estática
php vendor/phpstan/phpstan/phpstan.phar analyse

# Code style
php vendor/bin/pint            # aplica
php vendor/bin/pint --test     # só verifica
```

> O `vendor/bin/pest` é um script sem extensão; no Windows chame o binário PHP
> diretamente: `php vendor/pestphp/pest/bin/pest`.

## Estrutura dos testes

```
tests/
├── Unit/
│   ├── ArchitectureTest.php      # regras de dependência entre camadas (Pest arch)
│   ├── ProductTest.php           # invariantes do agregado Product
│   ├── StockCalculatorTest.php   # serviço de domínio + eventos
│   └── ValueObjectsTest.php      # Money, ProductSku, Email, WorkspaceSlug...
└── Feature/
    ├── Http/
    │   ├── InventoryFlowTest.php     # fluxo ponta a ponta (register→produto→movimento→relatório)
    │   ├── ProductUseCasesTest.php   # geração de SKU, SKU duplicado, scan
    │   └── AuthorizationTest.php     # negação por Policy (403) + export CSV
    └── MovementModelImmutabilityTest.php  # ledger imutável
```

Configuração em `tests/Pest.php` (aplica `RefreshDatabase` em `Feature`) e
`phpunit.xml` (SQLite em memória, cache `array`, `INVENTORY_CACHE_STORE=array`).

## Testes de arquitetura (destaque)

As regras de DDD são **executáveis** — quebrar a fronteira falha o build:

```php
arch('Domain never depends on the framework')
    ->expect('Stockr\Domain')->not->toUse('Illuminate');

arch('Application never depends on Eloquent')
    ->expect('Stockr\Application')->not->toUse('Illuminate\Database\Eloquent');
```

## Convenções ao adicionar um recurso

1. **Domínio primeiro** — modele entidade/VO/evento em `src/Domain`, sem Illuminate.
2. **Caso de uso** em `src/Application` com `execute(DTO): DTO`, dependendo de
   *interfaces*.
3. **Adapter** em `src/Infrastructure` implementando o novo *port*; registre o
   binding no `AppServiceProvider`.
4. **Controller fino** + Form Request (com Policy) + Resource em `src/Presentation`.
5. **Testes**: unit para o domínio, feature para o endpoint; rode PHPStan 8.

## Observação conhecida

Há **1 deprecação** reportada pelo Pest vinda do pacote de terceiros
`simplesoftwareio/simple-qrcode` (parâmetro nullable implícito no PHP 8.4) — não é
do código do projeto. Um *stub* em `stubs/Generator.stub` corrige o tipo de
retorno para o PHPStan.

[← Voltar ao índice](README.md)
