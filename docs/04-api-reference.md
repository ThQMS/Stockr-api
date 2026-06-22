# 04 · Referência da API

[← Índice](README.md)

Base: `http://localhost:8000/api/v1`. Documentação interativa (OpenAPI 3.1) em
**`/docs/api`** e JSON em **`/docs/api.json`**.

## Convenções

- **Auth**: header `Authorization: Bearer <token>` em todas as rotas protegidas.
- **Workspace**: header `X-Workspace-Id: <id>` nas rotas de produtos, movimentos e
  relatórios (validado pelo middleware `workspace`).
- **Content-Type**: `application/json`.
- Respostas de Resource único vêm encapsuladas em `data`; DTOs de saída são
  serializados diretamente (camelCase).

---

## Auth

### `POST /auth/register`
Cria usuário + workspace e devolve um token.

Request:
```json
{ "name": "Thiago", "email": "t@example.com", "password": "secret123", "workspace_name": "Jeito Frio" }
```
`201`:
```json
{ "userId": 1, "name": "Thiago", "email": "t@example.com", "token": "1|plain...", "workspaceIds": [1] }
```

### `POST /auth/login`
```json
{ "email": "t@example.com", "password": "secret123" }
```
`200` → mesmo formato do register.

### `GET /auth/me`  *(auth)*
```json
{ "id": 1, "name": "Thiago", "email": "t@example.com" }
```

### `POST /auth/workspace`  *(auth)*
Seleciona o workspace ativo. Body: `{ "workspace_id": 1 }` → `WorkspaceResource`.

### `POST /auth/logout`  *(auth)*
Revoga todos os tokens. `200 { "message": "Logged out." }`

---

## Workspaces  *(auth)*

| Método | Rota | Descrição |
|---|---|---|
| GET | `/workspaces` | Lista workspaces do usuário |
| POST | `/workspaces/select` | Seleciona workspace (`{ "workspace_id": 1 }`) |
| GET | `/workspaces/{workspace}` | Detalha |

`WorkspaceResource`:
```json
{ "data": { "id": 1, "name": "Jeito Frio", "slug": "jeito-frio", "owner_id": 1 } }
```

---

## Produtos  *(auth + `X-Workspace-Id`)*

### `GET /products`
Lista com `spatie/laravel-query-builder`.
- Filtros: `filter[name]`, `filter[sku]`, `filter[category_id]`, `filter[low_stock]=true`
- Ordenação: `sort=name|sku|current_stock|cost_price` (prefixe `-` para desc)
- Paginação: `per_page` (padrão 15)

### `POST /products`
Cria produto. `sku` é opcional (gerado se omitido).
```json
{
  "name": "Compressor", "cost_price": 1200.50, "sale_price": 1800.00,
  "sku": "COOL-001", "barcode": "7891234567890", "unit": "un",
  "initial_stock": 10, "minimum_stock": 4, "category_id": null, "description": null
}
```
`201` → `ProductResource`:
```json
{
  "data": {
    "id": "01J9Z8K7Q3M5X2P0R4T6V8W1Y3",
    "workspace_id": 1, "category_id": null,
    "sku": "COOL-001", "barcode": "7891234567890",
    "name": "Compressor", "description": null, "unit": "un", "status": "active",
    "cost_price": 1200.5, "cost_price_formatted": "R$ 1.200,50",
    "sale_price": 1800, "sale_price_formatted": "R$ 1.800,00",
    "current_stock": 10, "minimum_stock": 4, "is_low_stock": false,
    "qr_code_path": null
  }
}
```

### `GET /products/{product}`
Detalha (404 se não existir / não pertencer ao workspace).

### `PUT /products/{product}`
Atualiza campos mutáveis: `name`, `cost_price`, `minimum_stock`, `description`.
Estoque **não** muda aqui — só via movimentos.

### `DELETE /products/{product}`
Soft delete. `204`.

### `POST /products/scan`
Resolve por SKU ou payload QR (`stockr://product/{ws}/{sku}`).
```json
{ "code": "COOL-001" }
```
`200`:
```json
{
  "productId": "01J9Z8K7Q3M5X2P0R4T6V8W1Y3",
  "sku": "COOL-001", "name": "Compressor", "stock": 3, "price": 1200.5,
  "stockStatus": "low",
  "recentMovements": [
    { "id": 7, "type": "out", "quantity": 7, "notes": "Sale", "movedAt": "2026-06-22T15:04:05+00:00" }
  ]
}
```
`stockStatus`: `out_of_stock` | `low` | `ok`.

### `GET /products/{product}/qrcode`
Gera o QR Code (parâmetro opcional `size`, padrão 300).
```json
{ "payload": "stockr://product/1/COOL-001", "dataUri": "data:image/svg+xml;base64,..." }
```

---

## Movimentos  *(auth + `X-Workspace-Id`)*

### `GET /products/{product}/movements`
Histórico do produto (`MovementResource[]`).

### `POST /products/{product}/movements`
```json
{ "type": "out", "quantity": 7, "notes": "Venda", "reference_code": "NF-123" }
```
`type`: `in` | `out` | `adjustment` | `transfer`. `201`:
```json
{
  "movementId": 7,
  "productId": "01J9Z8K7Q3M5X2P0R4T6V8W1Y3",
  "type": "out", "quantity": 7, "resultingStock": 3, "lowStockTriggered": true
}
```

`MovementResource` (na listagem):
```json
{
  "id": 7, "workspace_id": 1, "product_id": "01J9...", "user_id": 1,
  "type": "out", "type_label": "Saída",
  "quantity": 7, "signed_quantity": -7,
  "quantity_before": 10, "quantity_after": 3,
  "notes": "Venda", "reference_code": "NF-123",
  "moved_at": "2026-06-22T15:04:05+00:00"
}
```

---

## Relatórios  *(auth + `X-Workspace-Id`)*

### `GET /reports/summary`
```json
{
  "workspaceId": 1, "totalProducts": 1, "totalUnits": 3,
  "totalStockValue": 3601.5, "lowStockCount": 1,
  "lines": [
    { "productId": "01J9...", "sku": "COOL-001", "name": "Compressor",
      "stock": 3, "reorderLevel": 4, "unitPrice": 1200.5, "lineValue": 3601.5, "isLowStock": true }
  ]
}
```

### `GET /reports/chart`
Série pronta para gráfico (top 20 por valor):
```json
{ "workspace_id": 1, "total_stock_value": 3601.5,
  "series": [ { "label": "COOL-001", "stock": 3, "value": 3601.5 } ] }
```

### `GET /reports/low-stock`
`ProductResource[]` dos produtos no/abaixo do mínimo.

### `GET /reports/export`
Download de CSV (`Content-Type: text/csv`):
```
sku,name,stock,minimum_stock,unit_price,line_value,low_stock
COOL-001,Compressor,3,4,1200.50,3601.50,1
```

---

## Códigos de erro

| Status | Quando |
|---|---|
| `401` | Sem token / token inválido |
| `403` | Token válido, mas não é membro do workspace (Policy ou middleware) |
| `404` | Produto inexistente |
| `409` | SKU duplicado no workspace |
| `422` | Falha de validação (Form Request) ou invariante de Value Object |

Próximo: **[05 · Autenticação & Autorização →](05-authentication.md)**
