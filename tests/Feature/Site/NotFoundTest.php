<?php

namespace Tests\Feature\Site;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NotFoundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unknown_site_path_returns_inertia_404(): void
    {
        $response = $this->get('/this-path-does-not-exist');

        $response->assertStatus(404);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Errors/404')
            ->where('status', 404)
        );
    }

    public function test_unknown_solution_slug_returns_inertia_404(): void
    {
        $response = $this->get('/soluciones/no-existe-esta-solucion');

        $response->assertStatus(404);
        $response->assertInertia(fn (Assert $page) => $page->component('Errors/404'));
    }

    public function test_unknown_admin_path_returns_inertia_404_when_authenticated(): void
    {
        $admin = User::where('user_dsemai', 'admin@hyperion.local')->first();

        $response = $this->actingAs($admin)->get('/admin/this-does-not-exist');

        $response->assertStatus(404);
        $response->assertInertia(fn (Assert $page) => $page->component('Errors/404'));
    }

    public function test_inertia_request_to_missing_page_returns_inertia_json_404(): void
    {
        $response = $this->withHeader('X-Inertia', 'true')
            ->withHeader('X-Inertia-Version', 'unused')
            ->get('/no-existe');

        $response->assertStatus(404);
        $response->assertHeader('X-Inertia');
        $this->assertSame('Errors/404', $response->json('component'));
    }

    public function test_api_path_returns_json_not_inertia(): void
    {
        $response = $this->getJson('/api/v1/no-existe');

        $response->assertStatus(404);
        // No es Inertia: respuesta JSON estándar, sin la estructura Inertia
        $this->assertNull($response->json('component'));
    }
}
