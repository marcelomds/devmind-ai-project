<?php

namespace Tests\Feature\Analysis;

use App\Enums\AnalysisSource\AnalysisSource;
use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Enums\AnalyzerType\AnalyzerType;
use App\Models\Analysis\Analysis;
use App\Models\Repository\Repository;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requires_authentication(): void
    {
        $this->getJson('/api/v1/analyses')->assertStatus(401);
    }

    public function test_it_creates_an_analysis_and_completes_it_with_fake_findings(): void
    {
        Sanctum::actingAs(User::factory()->create());
        config(['ai.default' => 'fake']);

        $response = $this->postJson('/api/v1/analyses', [
            'input_code' => '<?php echo "hello";',
            'analyzer' => 'quality',
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('data.status', 'pending');

        $uuid = $response->json('data.uuid');

        $analysis = Analysis::where('uuid', $uuid)->firstOrFail();

        $this->assertSame(AnalysisStatus::Completed, $analysis->status);
        $this->assertNotNull($analysis->finished_at);
        $this->assertSame(3, $analysis->findings()->count());

        $show = $this->getJson("/api/v1/analyses/{$uuid}");

        $show->assertStatus(200)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonCount(3, 'data.findings');
    }

    public function test_it_lists_analyses_with_the_source_repository_full_name(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());

        $repository = Repository::create([
            'github_id' => 1, 'name' => 'widgets', 'full_name' => 'acme/widgets', 'is_active' => true,
        ]);

        Analysis::create([
            'repository_id' => $repository->id,
            'user_id' => $user->id,
            'analyzer' => AnalyzerType::Quality,
            'status' => AnalysisStatus::Completed,
            'source_type' => AnalysisSource::PullRequest,
            'pr_number' => 7,
            'input_code' => '<?php echo "hi";',
        ]);

        $response = $this->getJson('/api/v1/analyses?source_type=pull_request');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.repository_full_name', 'acme/widgets')
            ->assertJsonPath('data.0.pr_number', 7);
    }

    public function test_it_does_not_list_another_users_analyses(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $other = User::factory()->create();

        Analysis::create([
            'user_id' => $other->id,
            'analyzer' => AnalyzerType::Quality,
            'status' => AnalysisStatus::Completed,
            'source_type' => AnalysisSource::Manual,
            'input_code' => '<?php echo "hi";',
        ]);

        $response = $this->getJson('/api/v1/analyses');

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_it_cannot_show_another_users_analysis(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $other = User::factory()->create();

        $analysis = Analysis::create([
            'user_id' => $other->id,
            'analyzer' => AnalyzerType::Quality,
            'status' => AnalysisStatus::Completed,
            'source_type' => AnalysisSource::Manual,
            'input_code' => '<?php echo "hi";',
        ]);

        $response = $this->getJson("/api/v1/analyses/{$analysis->uuid}");

        $response->assertStatus(404);
    }
}
