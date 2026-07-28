<?php

namespace Tests\Feature\Repository;

use App\Models\Repository\Repository;
use App\Models\User\User;
use App\Services\Github\GithubClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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

    public function test_it_requires_authentication(): void
    {
        $this->getJson('/api/v1/repositories')->assertStatus(401);
    }

    public function test_it_connects_a_repository_by_full_name(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $this->mockGithubRepository();

        $response = $this->postJson('/api/v1/repositories', ['full_name' => 'acme/widgets']);

        $response->assertStatus(201)
            ->assertJsonPath('data.full_name', 'acme/widgets')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('repositories', [
            'github_id' => 123456,
            'full_name' => 'acme/widgets',
            'is_active' => true,
            'user_id' => $user->id,
        ]);
    }

    public function test_connecting_an_existing_inactive_repository_reactivates_it(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
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
        $this->assertSame($user->id, $repository->fresh()->user_id);
    }

    public function test_it_only_lists_the_authenticated_users_repositories(): void
    {
        $me = Sanctum::actingAs(User::factory()->create());
        $other = User::factory()->create();

        Repository::create(['user_id' => $me->id, 'github_id' => 1, 'name' => 'a', 'full_name' => 'acme/a', 'is_active' => true]);
        Repository::create(['user_id' => $me->id, 'github_id' => 2, 'name' => 'b', 'full_name' => 'acme/b', 'is_active' => true]);
        Repository::create(['user_id' => $other->id, 'github_id' => 3, 'name' => 'c', 'full_name' => 'acme/c', 'is_active' => true]);

        $response = $this->getJson('/api/v1/repositories');

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_it_toggles_monitoring_on_a_repository(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());

        $repository = Repository::create([
            'user_id' => $user->id, 'github_id' => 1, 'name' => 'a', 'full_name' => 'acme/a', 'is_active' => true,
        ]);

        $response = $this->patchJson("/api/v1/repositories/{$repository->uuid}", ['is_active' => false]);

        $response->assertStatus(200)->assertJsonPath('data.is_active', false);
        $this->assertFalse($repository->fresh()->is_active);
    }

    public function test_it_cannot_toggle_another_users_repository(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $repository = Repository::create([
            'user_id' => User::factory()->create()->id, 'github_id' => 1, 'name' => 'a', 'full_name' => 'acme/a', 'is_active' => true,
        ]);

        $response = $this->patchJson("/api/v1/repositories/{$repository->uuid}", ['is_active' => false]);

        $response->assertStatus(404);
        $this->assertTrue($repository->fresh()->is_active);
    }

    public function test_it_deletes_a_repository(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());

        $repository = Repository::create([
            'user_id' => $user->id, 'github_id' => 1, 'name' => 'a', 'full_name' => 'acme/a', 'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/repositories/{$repository->uuid}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('repositories', ['id' => $repository->id]);
    }
}
