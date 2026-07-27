<?php

namespace Tests\Feature\Repository;

use App\Models\Repository\Repository;
use App\Services\Github\GithubClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function mockGithubRepository(string $fullName = 'acme/widgets', int $id = 123456): void
    {
        $this->mock(GithubClient::class, function ($mock) use ($fullName, $id) {
            $mock->shouldReceive('fetchRepository')
                ->with($fullName)
                ->andReturn(['id' => $id, 'name' => 'widgets', 'full_name' => $fullName]);
        });
    }

    public function test_it_connects_a_repository_by_full_name(): void
    {
        $this->mockGithubRepository();

        $response = $this->postJson('/api/v1/repositories', ['full_name' => 'acme/widgets']);

        $response->assertStatus(201)
            ->assertJsonPath('data.full_name', 'acme/widgets')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('repositories', [
            'github_id' => 123456,
            'full_name' => 'acme/widgets',
            'is_active' => true,
        ]);
    }

    public function test_connecting_an_existing_inactive_repository_reactivates_it(): void
    {
        $this->mockGithubRepository();

        $repository = Repository::create([
            'github_id' => 123456,
            'name' => 'widgets',
            'full_name' => 'acme/widgets',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/repositories', ['full_name' => 'acme/widgets']);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseCount('repositories', 1);
        $this->assertTrue($repository->fresh()->is_active);
    }

    public function test_it_lists_repositories(): void
    {
        Repository::create(['github_id' => 1, 'name' => 'a', 'full_name' => 'acme/a', 'is_active' => true]);
        Repository::create(['github_id' => 2, 'name' => 'b', 'full_name' => 'acme/b', 'is_active' => true]);

        $response = $this->getJson('/api/v1/repositories');

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_it_toggles_monitoring_on_a_repository(): void
    {
        $repository = Repository::create([
            'github_id' => 1, 'name' => 'a', 'full_name' => 'acme/a', 'is_active' => true,
        ]);

        $response = $this->patchJson("/api/v1/repositories/{$repository->uuid}", ['is_active' => false]);

        $response->assertStatus(200)->assertJsonPath('data.is_active', false);
        $this->assertFalse($repository->fresh()->is_active);
    }

    public function test_it_deletes_a_repository(): void
    {
        $repository = Repository::create([
            'github_id' => 1, 'name' => 'a', 'full_name' => 'acme/a', 'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/repositories/{$repository->uuid}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('repositories', ['id' => $repository->id]);
    }
}
