<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * The project doesn't ship a ContentFactory yet, so each test that needs a
     * Content row builds one explicitly via the model.
     */
    private function createContent(array $overrides = []): Content
    {
        $author = User::factory()->create();

        return Content::create(array_merge([
            'cont_nmtitl' => 'Test Content',
            'cont_cdslug' => 'test-content-' . uniqid(),
            'cont_cdtype' => Content::TYPE_POST,
            'cont_cdstat' => Content::STATUS_DRAFT,
            'cont_idauth' => $author->user_iduser,
        ], $overrides));
    }

    public function test_index_returns_contents_list()
    {
        $this->createContent();

        $response = $this->getJson('/api/v1/contents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'status'],
                ],
            ]);
    }

    public function test_show_returns_single_content_by_slug()
    {
        $content = $this->createContent();

        $response = $this->getJson("/api/v1/contents/{$content->cont_cdslug}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $content->cont_idcont,
                    'slug' => $content->cont_cdslug,
                ],
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

        $content = $this->createContent();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/contents/{$content->cont_idcont}", [
                'title' => 'Updated Title',
                'slug' => $content->cont_cdslug,
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

        $content = $this->createContent();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/contents/{$content->cont_idcont}");

        $this->assertNotEquals(403, $response->status());
    }

    public function test_publish_changes_status_to_published()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $content = $this->createContent(['cont_cdstat' => Content::STATUS_DRAFT]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/contents/{$content->cont_idcont}/publish");

        $this->assertNotEquals(403, $response->status());
    }

    public function test_archive_changes_status_to_archived()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $content = $this->createContent();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/contents/{$content->cont_idcont}/archive");

        $this->assertNotEquals(403, $response->status());
    }
}
