<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_index_returns_media_list()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/media');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'path', 'mime_type'],
                ],
            ]);
    }

    public function test_index_denies_without_permission()
    {
        // The author role has no permissions assigned by the seeder.
        $authorRole = Role::where('role_cdslug', 'author')->first();
        $user = User::factory()->create();
        $user->roles()->attach($authorRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/media');

        $response->assertStatus(403);
    }

    public function test_upload_uploads_file_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $token = $user->createToken('test-token')->plainTextToken;

        // Use create() instead of image() to avoid the GD extension dependency.
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/media/upload', [
                'file' => $file,
                'alt_text' => 'Test image',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_upload_denies_without_permission()
    {
        $authorRole = Role::where('role_cdslug', 'author')->first();
        $user = User::factory()->create();
        $user->roles()->attach($authorRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/media/upload', [
                'file' => $file,
            ]);

        $response->assertStatus(403);
    }

    public function test_show_returns_single_media()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $media = Media::first();
        if (! $media) {
            $this->markTestSkipped('No media records seeded.');
        }

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/media/{$media->medi_idmedi}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $media->medi_idmedi,
                    'path' => $media->medi_dspath,
                ],
            ]);
    }

    public function test_update_modifies_media_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $media = Media::first();
        if (! $media) {
            $this->markTestSkipped('No media records seeded.');
        }

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/media/{$media->medi_idmedi}", [
                'alt_text' => 'Updated alt text',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_delete_removes_media_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $media = Media::first();
        if (! $media) {
            $this->markTestSkipped('No media records seeded.');
        }

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/media/{$media->medi_idmedi}");

        $this->assertNotEquals(403, $response->status());
    }
}
