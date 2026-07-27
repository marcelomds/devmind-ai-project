<?php

namespace App\Providers;

use App\Repositories\Analysis\AnalysisRepository;
use App\Repositories\Analysis\AnalysisRepositoryInterface;
use App\Repositories\Finding\FindingRepository;
use App\Repositories\Finding\FindingRepositoryInterface;
use App\Repositories\Repository\RepositoryRepository;
use App\Repositories\Repository\RepositoryRepositoryInterface;
use App\Services\Ai\AiProvider;
use App\Services\Ai\AnalysisPromptBuilder;
use App\Services\Ai\FakeAiProvider;
use App\Services\Ai\OpenAiProvider;
use App\Services\Github\GithubClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiProvider::class, function ($app) {
            return match (config('ai.default')) {
                'fake' => new FakeAiProvider,
                default => new OpenAiProvider(
                    $app->make(AnalysisPromptBuilder::class),
                    (string) config('ai.openai.api_key'),
                    (string) config('ai.openai.model'),
                    (string) config('ai.openai.base_url'),
                    (int) config('ai.openai.timeout'),
                ),
            };
        });

        $this->app->bind(AnalysisRepositoryInterface::class, AnalysisRepository::class);
        $this->app->bind(FindingRepositoryInterface::class, FindingRepository::class);
        $this->app->bind(RepositoryRepositoryInterface::class, RepositoryRepository::class);

        $this->app->bind(GithubClient::class, function () {
            return new GithubClient((string) config('github.token'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
