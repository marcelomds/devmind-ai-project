<?php

namespace Tests\Feature\Analysis;

use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Models\Analysis\Analysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_analysis_and_completes_it_with_fake_findings(): void
    {
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
}
