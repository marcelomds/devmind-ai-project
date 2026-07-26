# DevMind AI — Config: default model + response language

## Context
Laravel backend, AI engine already working with a real OpenAI provider (HTTP client,
Structured Outputs / json_schema strict). The prompt is built in an `AnalysisPromptBuilder`
class and the model comes from `config/ai.php` (env-driven). Frontend lives in ./frontend,
backend in ./backend. Run artisan in the container.

## Goal
Two small, low-risk changes — both driven by environment variables, no hardcoding:

### 1. Cheaper default model
- Change the default model to `gpt-4o-mini` (≈16x cheaper than gpt-4o, fine for dev/testing).
- Keep it env-driven: `config('ai.openai.model')` should read `OPENAI_MODEL` with default
  `gpt-4o-mini`.
- Update `backend/.env.example`:
    OPENAI_MODEL=gpt-4o-mini
- Do NOT touch the app's own key in .env (leave the real key as-is).

### 2. Response language (global, env-driven)
- Add a new config value: `config('ai.language')` reading env `AI_RESPONSE_LANGUAGE`
  with default `pt-BR`.
- In `AnalysisPromptBuilder`, append a clear instruction to the system prompt telling the
  model to write ALL human-readable finding fields (title, message, suggestion, and the
  analysis summary) in that language. Keep the JSON structure/schema and the enum values
  (severity, category keys) unchanged — only the natural-language text is translated.
  Example instruction: "Write every title, message, suggestion and summary in Brazilian
  Portuguese (pt-BR). Do not translate code, identifiers, enum values, or field names."
- Make the language label human-friendly in the prompt (map the code to a readable name,
  e.g. pt-BR -> "Brazilian Portuguese", en -> "English"). A tiny match/array is fine.
- Update `backend/.env.example`:
    AI_RESPONSE_LANGUAGE=pt-BR

## Important
- Structured Outputs must keep working: the schema stays identical; only the text content
  inside fields changes language. Don't alter the json_schema.
- The Fake driver does not call OpenAI, so it doesn't need translation — leave it as is
  (its findings can stay in English; they're only for pipeline testing).
- After changes, remind (in the PR/commit notes) that `php artisan config:clear` and a
  worker restart are required for env changes to take effect.

## Tasks
1. config/ai.php: default model `gpt-4o-mini`; add `language` from `AI_RESPONSE_LANGUAGE` (default pt-BR).
2. AnalysisPromptBuilder: inject the language instruction into the system prompt.
3. backend/.env.example: add/adjust OPENAI_MODEL and AI_RESPONSE_LANGUAGE.
4. Make sure existing tests still pass (they use the Fake driver): `php artisan test`.

## Acceptance
- With AI_PROVIDER=openai and AI_RESPONSE_LANGUAGE=pt-BR, a real analysis returns findings
  whose title/message/suggestion/summary are in Portuguese, with the JSON schema intact.
- Switching AI_RESPONSE_LANGUAGE=en (and config:clear + worker restart) makes them English.
- Switching OPENAI_MODEL back to gpt-4o still works (no code change needed).
- `php artisan test` passes.
