# Changelog

All notable changes to this project are documented here. The format is based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Offline-first batch sync endpoint for stock movements (consumed by the
  [stockr-app](https://github.com/ThQMS/Stockr-app-) Flutter client).

## [1.0.0] - 2026-06-22

### Added
- DDD architecture with 4 explicit layers (Domain, Application, Infrastructure,
  Presentation) under the `Stockr\` namespace.
- Inventory domain: `Product` aggregate (ULID identity), immutable `Movement`
  ledger with before/after snapshots, value objects (`Money`, `ProductSku`,
  `StockQuantity`, `MovementType`, `ProductStatus`).
- Auth domain: multi-workspace (tenant) model with `Email` / `WorkspaceSlug`
  value objects.
- Use cases: create/update product, register movement, scan, generate QR code,
  inventory report; register/authenticate/select-workspace.
- Sanctum authentication; workspace selection via `X-Workspace-Id` header;
  authorization through `ProductPolicy` / `MovementPolicy`.
- REST API under `/api/v1` (products, movements, reports, auth, workspaces).
- Report endpoints: summary, chart, low-stock and CSV export.
- Auto-generated OpenAPI 3.1 docs via Scramble at `/docs/api`.
- Redis-backed inventory cache (per-workspace prefix with invalidation).
- Quality gate: PHPStan level 8 (Larastan), Pest tests including executable
  architecture tests, 28 tests / 77 assertions green.
- Full documentation under `docs/`.

[Unreleased]: https://github.com/ThQMS/stockr-api/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/ThQMS/stockr-api/releases/tag/v1.0.0
