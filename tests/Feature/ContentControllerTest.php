<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Content;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_returns_contents_list()
    {
        $response = $this->getJson('/api/v1/contents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'cove_idcont',
                        'cove_cdslug',
                        'cove_dstitl',
                        'cove_cdstat',
                    ]
                ]
            ]);
    }

    public function test_show_returns_single_content_by_slug()
    {
        $content = Content::first();

        $response = $this->getJson("/api/v1/contents/{$content->cove_cdslug}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'cove_idcont' => $content->cove_idcont,
                    'cove_cdslug' => $content->cove_cdslug,
                ]
            ]);
    }

    public function test_store_creates_new_content_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/contents', [
                'title' => 'Test Content',
                'slug' => 'test-content',
                'content' => 'Test content body',
                'status' => 'draft',
                'cove_idcate' => 1,
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_store_denies_without_permission()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/contents', [
                'title' => 'Test Content',
                'slug' => 'test-content',
                'content' => 'Test content body',
                'status' => 'draft',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_modifies_content_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $content = Content::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/contents/{$content->cove_idcont}", [
                'title' => 'Updated Title',
                'slug' => $content->cove_cdslug,
                'content' => 'Updated content',
                'status' => 'published',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_delete_removes_content_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $content = Content::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/contents/{$content->cove_idcont}");

        $this->assertNotEquals(403, $response->status());
    }

    public function test_publish_changes_status_to_published()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $content = Content::where('cove_cdstat', 'draft')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/contents/{$content->cove_idcont}/publish");

        $this->assertNotEquals(403, $response->status());
    }

    public function test_archive_changes_status_to_archived()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $content = Content::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/contents/{$content->cove_idcont}/archive");

        $this->assertNotEquals(403, $response->status());
    }
}
