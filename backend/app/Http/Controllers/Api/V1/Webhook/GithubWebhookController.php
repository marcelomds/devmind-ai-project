<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Http\Controllers\Controller;
use App\Http\Resources\Analysis\AnalysisResource;
use App\Services\Github\GithubWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GithubWebhookController extends Controller
{
    private const HANDLED_ACTIONS = ['opened', 'synchronize'];

    public function __construct(
        private readonly GithubWebhookService $webhookService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if ($request->header('X-GitHub-Event') !== 'pull_request') {
            return response()->json(null, Response::HTTP_NO_CONTENT);
        }

        $payload = $request->json()->all();

        if (! in_array($payload['action'] ?? null, self::HANDLED_ACTIONS, true)) {
            return response()->json(null, Response::HTTP_NO_CONTENT);
        }

        $analysis = $this->webhookService->handlePullRequest(
            githubId: (int) $payload['repository']['id'],
            fullName: (string) $payload['repository']['full_name'],
            prNumber: (int) $payload['pull_request']['number'],
            commitSha: (string) $payload['pull_request']['head']['sha'],
            language: (string) config('ai.language'),
        );

        if (! $analysis) {
            return response()->json(null, Response::HTTP_NO_CONTENT);
        }

        return AnalysisResource::make($analysis)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }
}
