<?php

namespace App\Services\Github;

use App\Enums\AnalysisSource\AnalysisSource;
use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Enums\AnalyzerType\AnalyzerType;
use App\Jobs\Analysis\RunAnalysis;
use App\Models\Analysis\Analysis;
use App\Repositories\Analysis\AnalysisRepositoryInterface;
use App\Repositories\Repository\RepositoryRepositoryInterface;

class GithubWebhookService
{
    public function __construct(
        private readonly RepositoryRepositoryInterface $repositories,
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly GithubClient $github,
    ) {}

    public function handlePullRequest(int $githubId, string $fullName, int $prNumber, string $commitSha, string $language): ?Analysis
    {
        $name = str($fullName)->afterLast('/')->toString();

        $repository = $this->repositories->findOrCreateByGithubId($githubId, $fullName, $name);

        if (! $repository->is_active) {
            return null;
        }

        $diff = $this->github->fetchPullRequestDiff($fullName, $prNumber);

        if ($diff === '' || strlen($diff) > (int) config('github.max_diff_length')) {
            return null;
        }

        $analysis = $this->analyses->create([
            'repository_id' => $repository->id,
            'analyzer' => AnalyzerType::Quality,
            'status' => AnalysisStatus::Pending,
            'source_type' => AnalysisSource::PullRequest,
            'pr_number' => $prNumber,
            'commit_sha' => $commitSha,
            'input_code' => $diff,
        ]);

        RunAnalysis::dispatch($analysis, $language);

        return $analysis;
    }
}
