<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_returns_categories_list()
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);
    }

    public function test_tree_returns_hierarchical_categories()
    {
        $response = $this->getJson('/api/v1/categories/tree');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);
    }

    public function test_show_returns_single_category()
    {
        // GET /categories/{id} sits under the write-permission middleware group,
        // so the request needs to be authenticated with an editor token.
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);
        $token = $user->createToken('test-token')->plainTextToken;

        $category = Category::first();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/categories/{$category->cate_idcate}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $category->cate_idcate,
                    'name' => $category->cate_nmname,
                ],
            ]);
    }

    public function test_store_creates_new_category_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'slug' => 'test-category',
                'description' => 'Test description',
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
            ->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'slug' => 'test-category',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_modifies_category_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $category = Category::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/categories/{$category->cate_idcate}", [
                'name' => 'Updated Category',
                'slug' => $category->cate_cdslug,
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_delete_removes_category_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $category = Category::where('cate_idcate', '>', 1)->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/categories/{$category->cate_idcate}");

        $this->assertNotEquals(403, $response->status());
    }

    public function test_move_changes_category_parent()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $category = Category::where('cate_idcate', '>', 1)->first();
        $newParent = Category::where('cate_idcate', '!=', $category->cate_idcate)->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/categories/{$category->cate_idcate}/move", [
                'parent_id' => $newParent->cate_idcate,
            ]);

        $this->assertNotEquals(403, $response->status());
    }
}
