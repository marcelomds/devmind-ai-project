# DevMind AI — Phase 1: Database schema (migrations, enums, models)

## Context
Laravel 12 API (PostgreSQL 17) inside Docker. This is the "code intelligence" engine:
it receives code, runs asynchronous AI analyses via Redis queue, and stores structured
findings. Build the persistence layer for four domain tables. Auth comes later, so
`user_id` is nullable for now.

Run all artisan commands inside the container: `docker compose exec php php artisan ...`

## Key decisions already made (follow these exactly)

1. **Dual identifier strategy.** Every domain table keeps a `bigint` auto-increment `id`
   as primary key (used for internal FKs and joins) AND a secondary `uuid` column that is
   unique and indexed, exposed in the public API. Do NOT make UUID the primary key.
   Use **UUIDv7** (`Str::uuid7()`), generated in the model's `creating` event via a shared
   trait, because v7 is time-ordered and doesn't fragment the index like v4.

2. **No ENUM tables, no native Postgres ENUM.** Use **PHP 8.1 backed enums** cast on the
   models. The DB column is a plain `string`. Enums that need UI metadata (label, color)
   expose methods for the dashboard. This gives type-safety without joins or seeders.

3. **Indexes** are explicit (see each migration).

## Tables

### repositories
- id (bigint PK), uuid (uuid, unique)
- user_id (FK -> users, nullable, cascade on delete)
- github_id (bigint, unique) — stable GitHub id, survives repo rename
- name (string), full_name (string) — e.g. "marcelomds/devmind-ai"
- webhook_secret (string, nullable) — used in Phase 3
- is_active (boolean, default true)
- timestamps
- Index: github_id (unique), user_id

### analyses
- id (bigint PK), uuid (uuid, unique)
- repository_id (FK -> repositories, nullable, cascade on delete) — nullable for Phase 1 manual input
- analyzer (string) -> AnalyzerType enum
- status (string, default 'pending') -> AnalysisStatus enum
- source_type (string) -> AnalysisSource enum
- pr_number (integer, nullable)
- commit_sha (string, nullable)
- input_code (text, nullable) — the pasted/diff code
- summary (text, nullable) — AI-produced summary
- score (smallint, nullable) — 0-100, for the trend chart
- error_message (text, nullable)
- started_at (timestamp, nullable), finished_at (timestamp, nullable)
- timestamps
- Indexes: composite (repository_id, status); created_at; uuid (unique)

### findings
- id (bigint PK), uuid (uuid, unique)
- analysis_id (FK -> analyses, cascade on delete)
- severity (string) -> Severity enum
- category (string) — free string (performance, security, style, documentation...)
- title (string)
- message (text)
- suggestion (text, nullable)
- file_path (string, nullable)
- line_start (integer, nullable), line_end (integer, nullable)
- timestamps
- Index: analysis_id; severity

## Enums (app/Enums)

- **AnalysisStatus**: Pending, Processing, Completed, Failed
  - method `label(): string`
  - method `color(): string` (tailwind-friendly token, e.g. 'yellow', 'blue', 'green', 'red')
- **AnalyzerType**: Quality, Docs
  - method `label(): string`
- **AnalysisSource**: Manual, PullRequest
- **Severity**: Critical, High, Medium, Low, Info
  - method `label(): string`
  - method `color(): string`
  - method `weight(): int` (Critical=5 ... Info=1, for scoring/sorting)

All backed enums use string values (e.g. `case Pending = 'pending';`).

## Trait

Create `app/Models/Concerns/HasUuidV7.php`:
- boots on `creating`, sets `$model->uuid = Str::uuid7()` if empty
- provides `getRouteKeyName()` returning `'uuid'` so route-model binding uses the uuid, not the id

## Models
Repository, Analysis, Finding — each:
- uses HasUuidV7 trait
- declares `$fillable`
- casts the enum columns to their enum classes, and started_at/finished_at to datetime
- defines the Eloquent relationships (Repository hasMany Analysis; Analysis belongsTo Repository, hasMany Finding; Finding belongsTo Analysis)

## Tasks
1. Create the four migrations with columns + indexes above.
2. Create the four enums with their methods.
3. Create the HasUuidV7 trait.
4. Create/adjust the three models with fillable, casts, relationships.
5. Run `php artisan migrate` inside the container and confirm the tables exist.
6. Do NOT create controllers, jobs, or routes yet — schema only.

## Acceptance
- `php artisan migrate` runs clean against Postgres.
- Creating an Analysis in tinker auto-populates a UUIDv7 and casts `status` to AnalysisStatus enum.
- Route key is uuid (getRouteKeyName returns 'uuid').
