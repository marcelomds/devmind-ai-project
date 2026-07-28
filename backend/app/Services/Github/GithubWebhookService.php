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

    public function handlePullRequest(
        int $githubId,
        string $fullName,
        int $prNumber,
        string $commitSha,
        string $language,
        ?string $prTitle = null,
        ?string $prAuthorLogin = null,
        ?string $prAuthorAvatarUrl = null,
        ?int $prAuthorGithubId = null,
    ): ?Analysis {
        $name = str($fullName)->afterLast('/')->toString();

        $repository = $this->repositories->findOrCreateByGithubId($githubId, $fullName, $name);

        if (! $repository->is_active) {
            return null;
        }

        $diff = $this->github->fetchPullRequestDiff($fullName, $prNumber);

        if ($diff === '' || strlen($diff) > (int) config('github.max_diff_length')) {
            return null;
        }

        // Owner is the DevMind account that connected the repository, NOT the PR author —
        // the webhook has no logged-in user, so ownership must come from the repository.
        $analysis = $this->analyses->create([
            'repository_id' => $repository->id,
            'user_id' => $repository->user_id,
            'analyzer' => AnalyzerType::Quality,
            'status' => AnalysisStatus::Pending,
            'source_type' => AnalysisSource::PullRequest,
            'pr_number' => $prNumber,
            'pr_title' => $prTitle,
            'commit_sha' => $commitSha,
            'pr_author_login' => $prAuthorLogin,
            'pr_author_avatar_url' => $prAuthorAvatarUrl,
            'pr_author_github_id' => $prAuthorGithubId,
            'input_code' => $diff,
        ]);

        RunAnalysis::dispatch($analysis, $language);

        return $analysis;
    }
}
