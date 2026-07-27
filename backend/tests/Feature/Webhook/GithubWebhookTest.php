<?php

namespace Tests\Feature\Webhook;

use App\Enums\AnalysisSource\AnalysisSource;
use App\Jobs\Analysis\RunAnalysis;
use App\Models\Analysis\Analysis;
use App\Models\Repository\Repository;
use App\Services\Github\GithubClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GithubWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'action' => 'opened',
            'number' => 42,
            'repository' => [
                'id' => 123456,
                'full_name' => 'acme/widgets',
                'name' => 'widgets',
            ],
            'pull_request' => [
                'number' => 42,
                'head' => ['sha' => 'abc123'],
            ],
        ], $overrides);
    }

    private function postWebhook(array $payload, ?string $secret = null): TestResponse
    {
        $body = json_encode($payload);
        $secret ??= config('github.webhook_secret');
        $signature = 'sha256='.hash_hmac('sha256', $body, $secret);

        return $this->postJson('/api/v1/webhooks/github', $payload, [
            'X-GitHub-Event' => 'pull_request',
            'X-Hub-Signature-256' => $signature,
        ]);
    }

    public function test_it_creates_an_analysis_and_dispatches_the_job_for_a_valid_signature(): void
    {
        config(['github.webhook_secret' => 'test-secret']);
        Queue::fake();
        $this->mock(GithubClient::class, function ($mock) {
            $mock->shouldReceive('fetchPullRequestDiff')
                ->once()
                ->with('acme/widgets', 42)
                ->andReturn("--- a/foo.php\n+++ b/foo.php\n+echo 'hi';");
        });

        $response = $this->postWebhook($this->payload(), 'test-secret');

        $response->assertStatus(202)
            ->assertJsonPath('data.source_type', 'pull_request')
            ->assertJsonPath('data.pr_number', 42);

        $this->assertDatabaseCount('analyses', 1);
        $this->assertDatabaseHas('analyses', [
            'source_type' => AnalysisSource::PullRequest->value,
            'pr_number' => 42,
            'commit_sha' => 'abc123',
        ]);
        $this->assertDatabaseHas('repositories', [
            'github_id' => 123456,
            'full_name' => 'acme/widgets',
        ]);

        Queue::assertPushed(RunAnalysis::class, fn (RunAnalysis $job) => $job->analysis->is(Analysis::first()));
    }

    public function test_it_rejects_an_invalid_signature(): void
    {
        config(['github.webhook_secret' => 'test-secret']);

        $response = $this->postWebhook($this->payload(), 'wrong-secret');

        $response->assertStatus(401);
        $this->assertDatabaseCount('analyses', 0);
    }

    public function test_it_ignores_non_pull_request_events(): void
    {
        config(['github.webhook_secret' => 'test-secret']);
        $body = json_encode($this->payload());
        $signature = 'sha256='.hash_hmac('sha256', $body, 'test-secret');

        $response = $this->postJson('/api/v1/webhooks/github', $this->payload(), [
            'X-GitHub-Event' => 'ping',
            'X-Hub-Signature-256' => $signature,
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseCount('analyses', 0);
    }

    public function test_it_ignores_inactive_repositories(): void
    {
        config(['github.webhook_secret' => 'test-secret']);
        Repository::create([
            'github_id' => 123456,
            'name' => 'widgets',
            'full_name' => 'acme/widgets',
            'is_active' => false,
        ]);

        $response = $this->postWebhook($this->payload(), 'test-secret');

        $response->assertStatus(204);
        $this->assertDatabaseCount('analyses', 0);
    }
}
