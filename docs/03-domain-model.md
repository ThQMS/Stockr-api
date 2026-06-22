# 03 · Modelo de Domínio

[← Índice](README.md)

Tudo aqui vive em `src/Domain` e **não depende do framework**.

## Contextos

- **Inventory** — produtos, movimentações, categorias, cálculo de estoque.
- **Auth** — usuários, workspaces (tenant) e suas credenciais.
- **Shared** — kernel compartilhado (`DomainEvent`, `EventDispatcherInterface`).

## Agregados e Entidades

### `Product` (raiz de agregado)
Dono do estoque e das invariantes que o protegem.

Campos: `id` (ULID), `workspaceId`, `sku` (`ProductSku`), `name`, `price`
(`Money` de custo), `stock`/`reorderLevel` (`StockQuantity`), `categoryId`,
`description`, `status` (`ProductStatus`), `salePrice`, `barcode`, `unit`,
`qrCodePath`.

Comportamento principal:
- `Product::create(...)` — named constructor; gera ULID e grava `ProductCreated`.
- `registerMovement(type, quantity, userId, notes?, referenceCode?)` — aplica o
  movimento ao estoque (com invariantes), constrói o `Movement` com snapshots
  *antes/depois* e grava `StockMovementRegistered` (+ `LowStockDetected` se cruzar
  o mínimo).
- `applyMovement(type, quantity)` — `in` soma, `out`/`transfer` subtrai (não pode
  ficar negativo → `InsufficientStockException`), `adjustment` define valor absoluto.
- `isBelowReorderLevel()` — no nível mínimo ou abaixo.
- `pullDomainEvents()` — drena os eventos acumulados.

### `Movement`
Registro **imutável** de uma movimentação. Campos: `id`, `workspaceId`,
`productId` (ULID), `userId`, `type`, `quantity`, `quantityBefore`,
`quantityAfter`, `notes`, `referenceCode`, `movedAt`.
`signedQuantity()` devolve o efeito com sinal sobre o estoque.

### `Category`, `User`, `Workspace`
Entidades de apoio. `Workspace` é a fronteira de *tenant* (todo produto/movimento
pertence a um). `User` carrega os `workspaceIds` a que pertence.

## Value Objects

| VO | Regras |
|---|---|
| `Money` | Armazena **centavos (int)**; `of()`, `ofReais()`, `add/subtract/multiply`, `toCents()`, `toReais()` → `"R$ 1.234,56"`. Nunca float. |
| `ProductSku` | Regex `^[A-Z]{2,6}-\d{3,6}$`; `fromString()`, `generate(code, seq)` → `COOL-001`. |
| `StockQuantity` | Inteiro não-negativo; `add/subtract` (subtrair além do disponível lança `InsufficientStockException`), `isBelow`, `equals`. |
| `MovementType` | Enum: `in`, `out`, `adjustment`, `transfer`; `label()`, `affectsStockPositively()`. |
| `ProductStatus` | Enum: `active`, `inactive`, `discontinued`; `isSellable()`. |
| `Email` | `filter_var` + normalização lowercase. |
| `WorkspaceSlug` | `^[a-z0-9-]{3,50}$`; `fromName()` (slugify) e variante com sufixo aleatório. |

## Domain Events

Todos implementam `DomainEvent` (`occurredOn(): DateTimeImmutable`) e carregam
entidades:

| Evento | Quando | Carga |
|---|---|---|
| `ProductCreated` | produto criado | `Product` |
| `StockMovementRegistered` | movimento aplicado e persistido | `Movement`, `Product` |
| `LowStockDetected` | estoque ≤ mínimo após um movimento | `Product`, `threshold` |

São acumulados no agregado e despachados pelo use case **após** a persistência,
através do `EventDispatcherInterface` (port) → `LaravelEventDispatcherAdapter`.

## Serviço de domínio — `StockCalculator`

Puro e sem estado:
- `calculateTotalValue(ProductCollection): Money` — soma `custo × estoque` em
  centavos.
- `getCriticalProducts(ProductCollection): ProductCollection` — produtos abaixo do
  mínimo, ordenados por criticidade (`estoque / mínimo` crescente).

## Ports (interfaces implementadas na Infraestrutura)

| Port | Adapter |
|---|---|
| `ProductRepositoryInterface` | `EloquentProductRepository` |
| `MovementRepositoryInterface` | `EloquentMovementRepository` |
| `UserRepositoryInterface` / `WorkspaceRepositoryInterface` | Eloquent* |
| `EventDispatcherInterface` | `LaravelEventDispatcherAdapter` |
| `InventoryCacheInterface` | `RedisInventoryCache` |
| `QrCodeGeneratorInterface` | `SimpleSoftwareQrCodeAdapter` |
| `PasswordHasherInterface` / `TokenIssuerInterface` / `CredentialVerifierInterface` | Infra/Auth |

## Exceções de domínio

| Exceção | HTTP | Significado |
|---|---|---|
| `InsufficientStockException` | 422* | Saída maior que o disponível |
| `InvalidSkuException` / `InvalidQuantityException` | 422* | Value Object inválido |
| `DuplicateSkuException` | 409 | SKU já existe no workspace |
| `ProductNotFoundException` | 404 | Produto inexistente |
| `UnauthorizedWorkspaceException` | 403 | Acesso a workspace de que não é membro |

\* Mapeamento HTTP em `bootstrap/app.php` (404/409/403) e validação (422). As
demais sobem como erro de domínio.

## Regras de negócio centrais

1. **Estoque nunca fica negativo** — garantido em `Product::applyMovement`.
2. **SKU é único por workspace** — checado no `CreateProductUseCase`; se omitido,
   um SKU é gerado a partir do nome + sequência.
3. **Movimentos são imutáveis** — só inserção; trilha com snapshots antes/depois.
4. **Baixo estoque** — ao registrar um movimento que leve `estoque ≤ mínimo`,
   emite-se `LowStockDetected`.
5. **Isolamento por workspace** — todo acesso valida membership (middleware +
   Policy) e o agregado confirma `getWorkspaceId()`.

Próximo: **[04 · Referência da API →](04-api-reference.md)**
