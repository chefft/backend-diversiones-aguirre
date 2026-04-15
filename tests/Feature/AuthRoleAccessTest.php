<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_both_login_pages(): void
    {
        $this->get('/login/admin')->assertOk();
        $this->get('/login/cliente')->assertOk();
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'admin12345',
        ]);

        $response = $this->post('/login/admin', [
            'email' => $admin->email,
            'password' => 'admin12345',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_client_login_redirects_to_client_dashboard(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'password' => 'cliente12345',
        ]);

        $response = $this->post('/login/cliente', [
            'email' => $client->email,
            'password' => 'cliente12345',
        ]);

        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($client);
    }

    public function test_client_cannot_access_admin_dashboard(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $response = $this->actingAs($client)->get('/admin/dashboard');

        $response->assertRedirect(route('client.dashboard'));
    }

    public function test_admin_cannot_access_client_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/cliente/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }
}
