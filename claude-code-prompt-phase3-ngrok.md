# DevMind AI — Phase 3: GitHub webhook integration

## Context
The analysis engine already works end to end: `POST /api/v1/analyses` creates an Analysis,
dispatches a queued job (Redis), a worker calls the AI provider (OpenAI real + Fake), and
findings are stored. Enums exist (AnalysisStatus, AnalyzerType, AnalysisSource with values
`manual` and `pull_request`, Severity). The `repositories` table already has `github_id`,
`full_name`, `webhook_secret`, `is_active`. Models use HasUuidV7, route key is uuid.

Now add a SECOND entry point: GitHub Pull Request webhooks. When a PR is opened/updated,
GitHub calls our backend; we validate the signature, fetch the PR diff, create an Analysis
(source_type = pull_request) and dispatch the SAME RunAnalysis job. The engine is reused —
do NOT rewrite it. Backend in ./backend, run artisan in the container.

## Key decisions already made (follow exactly)

1. **Reuse the engine.** The webhook path must end by creating an Analysis and dispatching
   the existing RunAnalysis job — same job, same worker, same findings pipeline. Only the
   INPUT differs (PR diff instead of pasted code).

2. **Analyze the DIFF first (not whole files).** For now, send only the PR diff (the changed
   lines) as the code to analyze. Keep it simple; whole-file context can come later.

3. **Signature validation is mandatory.** GitHub signs each webhook with HMAC-SHA256 using a
   shared secret, in the `X-Hub-Signature-256` header. Recompute and compare using
   `hash_equals` (timing-safe). Reject with 401 if it doesn't match. Never process an
   unverified webhook.

4. **Tunnel-agnostic.** Local dev uses ngrok with a fixed dev domain (documented below), but
   the webhook controller must NOT assume any fixed host — it just handles the incoming POST.

## Config
`config/services.php` (or a new `config/github.php`):
- `github.token` from env `GITHUB_TOKEN` (a PAT to fetch the diff via the GitHub API).
- `github.webhook_secret` from env `GITHUB_WEBHOOK_SECRET` (the shared secret; for a single
  test repo this global secret is fine now; per-repo `webhook_secret` on the repositories
  table can be used later).

Add to `backend/.env.example`:
    GITHUB_TOKEN=
    GITHUB_WEBHOOK_SECRET=

## Route
`POST /api/v1/webhooks/github` (public, no auth middleware, but signature-verified inside).
Keep it OUT of any auth-protected group.

## Signature verification (middleware)
- Read the RAW request body (not the parsed array — the signature is over the raw bytes).
- Compute `'sha256=' . hash_hmac('sha256', $rawBody, $secret)`.
- Compare to header `X-Hub-Signature-256` with `hash_equals`. Mismatch -> 401.
- Implement as dedicated middleware `VerifyGithubSignature` applied to the webhook route.

## Controller: GithubWebhookController
- Only act on the `pull_request` event (header `X-GitHub-Event`) with action
  `opened` or `synchronize` (new commits). Ignore other events/actions with a 204.
- Extract from payload: repo full_name, repo github id, PR number, head commit sha,
  and the info needed to fetch the diff.
- Find or create the Repository row by github_id (store full_name). If `is_active` is false,
  ignore with 204.
- Fetch the PR diff via the GitHub API using GITHUB_TOKEN:
  `GET /repos/{owner}/{repo}/pulls/{number}` with header
  `Accept: application/vnd.github.v3.diff` returns the raw diff text. Use Laravel Http.
- Create an Analysis: repository_id set, source_type = pull_request, analyzer = quality
  (default for now), status = pending, pr_number, commit_sha, input_code = the diff text.
- Dispatch RunAnalysis for that analysis. Return 202 with the analysis uuid.
- Guard: if the diff is empty or huge (e.g. > a sane char limit), skip gracefully
  (mark analysis failed with a clear message, or don't create it) — don't blow up the job.

## Service: keep GitHub API calls in a small class
Put the diff-fetching in `app/Services/Github/GithubClient.php` (Http-based), so the
controller stays thin and it's testable/mock-able. One method: `fetchPullRequestDiff(
string $fullName, int $number): string`.

## Tasks
1. Config + .env.example additions (GITHUB_TOKEN, GITHUB_WEBHOOK_SECRET).
2. VerifyGithubSignature middleware (raw body, hash_hmac sha256, hash_equals, 401 on fail).
3. GithubClient service (fetch PR diff).
4. GithubWebhookController (handle pull_request opened/synchronize -> repo -> diff ->
   Analysis(pull_request) -> dispatch RunAnalysis).
5. Route POST /api/v1/webhooks/github with the middleware.
6. Feature test: fake a `pull_request` `opened` payload with a valid signature (compute HMAC
   in the test), mock GithubClient to return a sample diff, assert an Analysis with
   source_type=pull_request is created and the job dispatched. Also assert a bad signature
   returns 401. Use Http::fake / a mocked GithubClient so no real network is used.

## Local testing docs (put in the PR/commit notes or a docs/ file)
Uses ngrok with a FIXED dev domain (no need to update the GitHub webhook URL between runs).

- Start stack + worker:
    docker compose up -d
    docker compose exec php php artisan queue:work
- One-time ngrok setup (already has an account): copy the authtoken from the ngrok dashboard
  and register it once:
    ngrok config add-authtoken <YOUR_AUTHTOKEN>
  Then grab the free dev domain from the ngrok dashboard (Domains) — e.g.
  `your-name.ngrok-free.dev`.
- Start the tunnel with the fixed domain (separate terminal):
    ngrok http --url=your-name.ngrok-free.dev 8080
  The URL stays the same across restarts, so the GitHub webhook is configured only once.
- In the TEST GitHub repo: Settings -> Webhooks -> Add webhook:
    Payload URL: https://your-name.ngrok-free.dev/api/v1/webhooks/github
    Content type: application/json
    Secret: same value as GITHUB_WEBHOOK_SECRET
    Events: "Let me select individual events" -> Pull requests only
- Open a PR in the test repo -> GitHub delivers the webhook -> analysis runs.
- Debugging: GitHub's webhook page has a "Recent Deliveries" tab showing each payload and the
  response code — use it to confirm 202 (success) vs 401 (bad signature) vs 500 (server error).
  ngrok also has a local inspector at http://localhost:4040 showing every request.

## Acceptance
- A valid `pull_request` opened/synchronize webhook (correct signature) creates an Analysis
  with source_type=pull_request and dispatches RunAnalysis; findings appear once processed.
- Invalid signature -> 401, nothing created.
- Non-PR events or is_active=false repos -> 204, ignored gracefully.
- `php artisan test` passes (using fakes; no real GitHub/network calls in tests).
- The engine (job/worker/AI/findings) is reused unchanged.
