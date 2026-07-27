<?php

namespace App\Services\Github;

use App\Services\Github\Exceptions\GithubRequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GithubClient
{
    public function __construct(
        private readonly string $token,
    ) {}

    public function fetchRepository(string $fullName): array
    {
        try {
            $response = $this->client()
                ->get("https://api.github.com/repos/{$fullName}");
        } catch (ConnectionException $e) {
            throw new GithubRequestException("GitHub request failed: {$e->getMessage()}", previous: $e);
        }

        if ($response->failed()) {
            throw new GithubRequestException("GitHub request failed with status {$response->status()}: {$response->body()}");
        }

        return [
            'id' => (int) $response->json('id'),
            'name' => (string) $response->json('name'),
            'full_name' => (string) $response->json('full_name'),
        ];
    }

    public function fetchPullRequestDiff(string $fullName, int $number): string
    {
        try {
            $response = $this->client()
                ->withHeaders(['Accept' => 'application/vnd.github.v3.diff'])
                ->get("https://api.github.com/repos/{$fullName}/pulls/{$number}");
        } catch (ConnectionException $e) {
            throw new GithubRequestException("GitHub request failed: {$e->getMessage()}", previous: $e);
        }

        if ($response->failed()) {
            throw new GithubRequestException("GitHub request failed with status {$response->status()}: {$response->body()}");
        }

        return $response->body();
    }

    private function client(): PendingRequest
    {
        return $this->token === '' ? Http::asJson() : Http::withToken($this->token);
    }
}
