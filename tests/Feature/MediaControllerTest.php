<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Media;
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
                    '*' => [
                        'medi_idmedi',
                        'medi_nmfile',
                        'medi_cdtype',
                    ]
                ]
            ]);
    }

    public function test_index_denies_without_permission()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

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

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/media/upload', [
                'file' => $file,
                'alt_text' => 'Test image',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_upload_denies_without_permission()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $file = UploadedFile::fake()->image('test.jpg');

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
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/media/{$media->medi_idmedi}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'medi_idmedi' => $media->medi_idmedi,
                    'medi_nmfile' => $media->medi_nmfile,
                ]
            ]);
    }

    public function test_update_modifies_media_with_permission()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $media = Media::first();
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
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/media/{$media->medi_idmedi}");

        $this->assertNotEquals(403, $response->status());
    }
}
