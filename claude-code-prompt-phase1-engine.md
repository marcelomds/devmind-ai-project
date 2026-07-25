# DevMind AI — Phase 1: The analysis engine

## Context
Laravel 12 API (PostgreSQL 17, Redis) inside Docker. The domain schema already exists
(repositories, analyses, findings + PHP enums AnalysisStatus, AnalyzerType, AnalysisSource,
Severity; models use a HasUuidV7 trait; route key is `uuid`).

Now build the ENGINE: an endpoint receives code, creates a pending Analysis, dispatches a
queued Job to Redis, the Job calls an AI provider, parses structured findings, stores them,
and marks the Analysis completed (or failed). No GitHub, no auth yet.

All commands run in the container: `docker compose exec php php artisan ...`
The Laravel app lives in `./backend`.

## Key decisions already made (follow exactly)

1. **Provider-agnostic, minimal.** Define ONE interface and TWO implementations:
   an OpenAI driver (real) and a Fake driver (for testing without spending). Do NOT
   create drivers for other providers. Do NOT build a provider registry with fallback/
   retry/balancing — that's over-engineering. The Job depends on the INTERFACE, resolved
   via Laravel's service container; the concrete driver is chosen by a config value.

2. **HTTP client, not an SDK.** The OpenAI driver talks to the API using Laravel's `Http`
   client (`Illuminate\Support\Facades\Http`). No `openai-php` package.

3. **Structured Outputs (json_schema strict), not legacy JSON mode.** The OpenAI request
   MUST use `response_format: { type: "json_schema", json_schema: { ..., strict: true } }`
   so the API enforces the schema. Handle the `refusal` case: if the model returns a
   refusal instead of content, treat it as a failed analysis.

4. **Async.** The endpoint returns immediately (202-style) with the Analysis uuid and
   status `pending`. The Job does the slow work off-request.

## Config

`config/ai.php`:
- `default` => env('AI_PROVIDER', 'openai')   // 'openai' | 'fake'
- `openai` => [
    'api_key' => env('OPENAI_API_KEY'),
    'model'   => env('OPENAI_MODEL', 'gpt-4o-2024-08-06'),
    'base_url'=> env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'timeout' => env('OPENAI_TIMEOUT', 60),
  ]

Add to `backend/.env.example` (empty key):
  AI_PROVIDER=openai
  OPENAI_API_KEY=
  OPENAI_MODEL=gpt-4o-2024-08-06

## The AI layer (app/Services/Ai)

### Contract
`AiProvider` interface with one method:
```
public function analyze(string $code, AnalyzerType $analyzer): AnalysisResult;
```

### DTOs (app/Services/Ai/Data)
- `AnalysisResult`: readonly — `int $score`, `string $summary`, `FindingData[] $findings`.
- `FindingData`: readonly — `Severity $severity`, `string $category`, `string $title`,
  `string $message`, `?string $suggestion`, `?string $filePath`, `?int $lineStart`, `?int $lineEnd`.

### OpenAiProvider implements AiProvider
- Builds a system prompt (see "Prompt" below) + user message containing the code.
- POST `{base_url}/chat/completions` via `Http::withToken(...)->timeout(...)->post(...)`.
- Body: model, messages, and `response_format` = json_schema strict describing an object:
  `{ score:int(0-100), summary:string, findings: array<of finding> }`
  where each finding = `{ severity: enum[critical,high,medium,low,info], category:string,
  title:string, message:string, suggestion:string|null, file_path:string|null,
  line_start:int|null, line_end:int|null }`. All keys required; nullable ones use
  `["string","null"]` union types (Structured Outputs requires every property listed in
  `required`; optionals are expressed as nullable, not omitted).
- If `choices[0].message.refusal` is present → throw an `AiRefusalException`.
- Parse `choices[0].message.content` (already schema-valid JSON) into `AnalysisResult`.
- On HTTP failure (non-2xx / timeout) → throw an `AiRequestException`.

### FakeAiProvider implements AiProvider
- Returns a deterministic `AnalysisResult` with 2-3 invented findings of varied severity,
  no network call. Used when `AI_PROVIDER=fake`.

### Binding
A service provider (or AppServiceProvider) binds `AiProvider` to the concrete class based
on `config('ai.default')`.

## Prompt (system message)
Write a focused system prompt that instructs the model to act as a senior code reviewer for
the given analyzer type (quality vs docs), and to return findings that match the schema.
Keep it specific: for `quality`, look for bugs, performance issues (e.g. N+1), security
flaws, and bad patterns; for `docs`, flag undocumented public functions/classes and unclear
naming. Emphasize: be precise, actionable, one finding per issue, set `score` as overall
health 0-100. Put the prompt in a dedicated class/method (e.g. `AnalysisPromptBuilder`) so
it's versionable and testable, not inlined as a magic string in the provider.

## The Job (app/Jobs/RunAnalysis)
- `implements ShouldQueue`, uses the standard queue traits.
- Constructor takes the `Analysis` (or its id/uuid).
- `handle(AiProvider $ai)` — the provider is injected:
  1. mark Analysis `processing`, set `started_at`.
  2. call `$ai->analyze($analysis->input_code, $analysis->analyzer)`.
  3. persist findings (bulk insert into the findings relation), set `summary`, `score`.
  4. mark `completed`, set `finished_at`.
- `failed(Throwable $e)` — mark Analysis `failed`, store `error_message`, set `finished_at`.
- Set sensible `$tries = 3` and `$backoff`, and a `$timeout` a bit above the HTTP timeout.
- Wrap the AI call so AiRefusalException is a permanent fail (no retry), while
  AiRequestException may retry.

## The endpoint (Phase 1, manual input)
- Route: `POST /api/v1/analyses` (in the v1 group).
- FormRequest validates: `input_code` (required, string), `analyzer` (required, in AnalyzerType values, default 'quality').
- Controller: create Analysis with source_type=manual, status=pending; dispatch RunAnalysis;
  return the Analysis (uuid, status) with 202.
- Also add `GET /api/v1/analyses/{analysis}` (route-model bound by uuid) returning the
  analysis with its findings — so the frontend can poll for completion.
- Use API Resources (AnalysisResource, FindingResource) for the JSON shape; expose uuid, not id.

## Queue setup
- Confirm `QUEUE_CONNECTION=redis` in backend/.env.
- Document how to run the worker: `docker compose exec php php artisan queue:work`.
  (A dedicated worker container can come later; for now the manual command is fine.)

## Tasks
1. `config/ai.php` + .env.example additions.
2. AiProvider interface, DTOs, OpenAiProvider (Http + json_schema strict + refusal handling),
   FakeAiProvider, exceptions, container binding.
3. AnalysisPromptBuilder.
4. RunAnalysis job (handle + failed + retry policy).
5. StoreAnalysisRequest (FormRequest), AnalysisController (store + show), API Resources, routes.
6. A feature test that hits POST /analyses with `AI_PROVIDER=fake`, runs the queued job
   synchronously (sync driver in test), and asserts findings were stored and status completed.

## Acceptance
- With `AI_PROVIDER=fake`: POST /api/v1/analyses returns 202 + uuid; after the job runs, GET
  returns status `completed` with the fake findings. No network used.
- With `AI_PROVIDER=openai` and a real key: same flow produces real findings from the model,
  schema-valid, stored as rows.
- A refusal or HTTP error marks the analysis `failed` with an error_message; it never crashes
  the worker.
- Nothing depends on GitHub or auth.
```
