<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_status_returns_ai_service_status()
    {
        $response = $this->getJson('/api/v1/ai/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'service',
                    'provider',
                    'status',
                    'features',
                    'message',
                ]
            ]);
    }

    public function test_generate_content_with_permission()
    {
        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($superAdminRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/ai/generate-content', [
                'prompt' => 'Write about technology',
                'type' => 'blog_post',
                'tone' => 'professional',
                'length' => 'short',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_generate_content_denies_without_permission()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/ai/generate-content', [
                'prompt' => 'Write about technology',
            ]);

        $response->assertStatus(403);
    }

    public function test_generate_seo_with_permission()
    {
        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($superAdminRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/ai/generate-seo', [
                'title' => 'Test Article',
                'content' => 'This is a test content for SEO generation.',
                'keywords' => ['test', 'seo'],
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_generate_seo_denies_without_permission()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/ai/generate-seo', [
                'title' => 'Test Article',
                'content' => 'This is a test content.',
            ]);

        $response->assertStatus(403);
    }

    public function test_translate_with_permission()
    {
        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($superAdminRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/ai/translate', [
                'content' => 'This is a test content to translate.',
                'target_language' => 'es',
                'source_language' => 'en',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_translate_denies_without_permission()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/ai/translate', [
                'content' => 'This is a test content.',
                'target_language' => 'es',
            ]);

        $response->assertStatus(403);
    }

    public function test_generate_content_returns_simulated_response_without_api_key()
    {
        // Ensure no API key is configured
        config(['services.ai.api_key' => null]);

        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($superAdminRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/ai/generate-content', [
                'prompt' => 'Write about technology',
                'type' => 'blog_post',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'content',
                    'type',
                    'tone',
                    'length',
                    'word_count',
                ],
                'message',
            ]);
    }
}
