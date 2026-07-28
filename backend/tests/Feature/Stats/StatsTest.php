<?php

namespace Tests\Feature\Stats;

use App\Enums\AnalysisSource\AnalysisSource;
use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Enums\AnalyzerType\AnalyzerType;
use App\Enums\Severity\Severity;
use App\Models\Analysis\Analysis;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requires_authentication(): void
    {
        $this->getJson('/api/v1/stats')->assertStatus(401);
    }

    public function test_it_returns_aggregated_stats(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->createCompletedAnalysis(score: 60, daysAgo: 10, findings: [
            Severity::High, Severity::Medium,
        ]);

        $this->createCompletedAnalysis(score: 80, daysAgo: 2, findings: [
            Severity::Critical, Severity::Critical, Severity::Low,
        ]);

        // Pending analysis has no score and should not affect the aggregates.
        Analysis::create([
            'analyzer' => AnalyzerType::Quality,
            'status' => AnalysisStatus::Pending,
            'source_type' => AnalysisSource::Manual,
            'input_code' => '<?php echo 1;',
        ]);

        $response = $this->getJson('/api/v1/stats?days=30');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_analyses', 2)
            ->assertJsonPath('data.summary.current_score', 80)
            ->assertJsonPath('data.summary.score_delta', 20)
            ->assertJsonPath('data.summary.open_findings', 5)
            ->assertJsonPath('data.summary.critical_count', 2)
            ->assertJsonPath('data.summary.critical_delta', null)
            ->assertJsonPath('data.score_over_time.0.score', 60)
            ->assertJsonPath('data.score_over_time.1.score', 80)
            ->assertJsonCount(2, 'data.score_over_time');

        $bySeverity = collect($response->json('data.findings_by_severity'))->keyBy('severity');

        $this->assertSame(2, $bySeverity['critical']['count']);
        $this->assertSame(1, $bySeverity['high']['count']);
        $this->assertSame(1, $bySeverity['medium']['count']);
        $this->assertSame(1, $bySeverity['low']['count']);
        $this->assertSame(0, $bySeverity['info']['count']);
    }

    public function test_it_returns_empty_aggregates_with_no_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/v1/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_analyses', 0)
            ->assertJsonPath('data.summary.current_score', null)
            ->assertJsonPath('data.summary.score_delta', null)
            ->assertJsonPath('data.summary.critical_delta', null)
            ->assertJsonCount(0, 'data.score_over_time')
            ->assertJsonCount(5, 'data.findings_by_severity');
    }

    private function createCompletedAnalysis(int $score, int $daysAgo, array $findings): Analysis
    {
        $analysis = Analysis::create([
            'analyzer' => AnalyzerType::Quality,
            'status' => AnalysisStatus::Completed,
            'source_type' => AnalysisSource::Manual,
            'input_code' => '<?php echo 1;',
            'score' => $score,
        ]);

        $analysis->created_at = now()->subDays($daysAgo);
        $analysis->save();

        foreach ($findings as $severity) {
            $analysis->findings()->create([
                'severity' => $severity,
                'category' => 'style',
                'title' => 'Finding',
                'message' => 'Message',
            ]);
        }

        return $analysis;
    }
}
