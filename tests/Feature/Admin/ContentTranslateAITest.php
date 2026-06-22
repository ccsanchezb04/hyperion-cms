<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Services\AITranslator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTranslateAITest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $this->viewer = User::where('user_dsemai', 'viewer@hyperion.local')->first();
        // Asegurar simulation mode (sin API key real en tests)
        config(['services.ai.api_key' => null]);
    }

    public function test_endpoint_requires_authentication(): void
    {
        $this->post('/admin/contents/translate', [
            'source_title' => 'Hola',
            'target_language' => 'en',
        ])->assertRedirect('/admin/login');
    }

    public function test_endpoint_requires_use_ai_permission(): void
    {
        $this->actingAs($this->viewer)
            ->postJson('/admin/contents/translate', [
                'source_title' => 'Hola mundo',
                'target_language' => 'en',
            ])
            ->assertStatus(403);
    }

    public function test_admin_can_translate_title_and_body(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/contents/translate', [
                'source_title'    => 'Seguro de Vida',
                'source_body'     => 'Protege a tu familia con nuestra cobertura integral.',
                'source_language' => 'es',
                'target_language' => 'en',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['title', 'body', 'target_language']]);

        // En simulation mode (sin API key) el output viene prefixed
        $data = $response->json('data');
        $this->assertStringContainsString('English', $data['title']);
        $this->assertStringContainsString('Seguro de Vida', $data['title']);
        $this->assertSame('en', $data['target_language']);
    }

    public function test_can_translate_title_only_without_body(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/contents/translate', [
                'source_title'    => 'Solo título',
                'target_language' => 'en',
            ]);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.title'));
        $this->assertSame('', $response->json('data.body'));
    }

    public function test_empty_payload_rejected(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/contents/translate', [
                'target_language' => 'en',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('source_title');
    }

    public function test_invalid_target_language_rejected(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/contents/translate', [
                'source_title' => 'Hola',
                'target_language' => 'xx',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('target_language');
    }

    public function test_ai_translator_service_simulates_without_api_key(): void
    {
        config(['services.ai.api_key' => null]);
        $translator = new AITranslator();

        $this->assertFalse($translator->isConfigured());
        $output = $translator->translate('Hola mundo', 'en');
        $this->assertStringContainsString('English', $output);
        $this->assertStringContainsString('Hola mundo', $output);
    }
}
