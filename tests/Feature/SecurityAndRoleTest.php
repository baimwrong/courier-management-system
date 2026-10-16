<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAndRoleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_guest_is_redirected_to_login_from_admin_portal(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/tariffs');
        $response->assertRedirect('/login');
    }

    public function test_customer_role_cannot_access_back_office_portal(): void
    {
        $customer = User::where('role', 'customer')->first();
        $this->assertNotNull($customer);

        $response = $this->actingAs($customer)->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($customer)->get('/admin/shipments');
        $response->assertStatus(403);

        $response = $this->actingAs($customer)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_courier_cannot_access_tariff_matrix_or_user_management(): void
    {
        $courier = User::where('role', 'courier')->first();
        $this->assertNotNull($courier);

        // Courier can access dashboard and shipments
        $response = $this->actingAs($courier)->get('/admin/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($courier)->get('/admin/shipments');
        $response->assertStatus(200);

        // Courier is forbidden from users, tariffs, branches
        $response = $this->actingAs($courier)->get('/admin/users');
        $response->assertStatus(403);

        $response = $this->actingAs($courier)->get('/admin/tariffs');
        $response->assertStatus(403);

        $response = $this->actingAs($courier)->get('/admin/branches');
        $response->assertStatus(403);
    }

    public function test_superadmin_can_access_all_administrative_modules(): void
    {
        $admin = User::where('role', 'superadmin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/tariffs');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/branches');
        $response->assertStatus(200);
    }

    public function test_branch_manager_cannot_create_or_elevate_superadmin(): void
    {
        $manager = User::where('role', 'branch_manager')->first();
        $this->assertNotNull($manager);

        // Manager tries to create a superadmin
        $response = $this->actingAs($manager)->post('/admin/users', [
            'name' => 'Hacker Admin',
            'email' => 'hacker@test.com',
            'password' => 'secret123',
            'role' => 'superadmin',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'hacker@test.com']);
    }

    public function test_public_tracking_api_masks_pii_and_does_not_leak_user_credentials(): void
    {
        $shipment = Shipment::first();
        $this->assertNotNull($shipment);

        $response = $this->getJson('/track/' . $shipment->tracking_number);
        $response->assertStatus(200);

        $data = $response->json('data.0');
        $this->assertNotNull($data);

        // Check that full recipient phone/email is not directly exposed
        $this->assertArrayNotHasKey('recipient_phone', $data);
        $this->assertArrayNotHasKey('sender_phone', $data);
        $this->assertArrayNotHasKey('recipient_address', $data);

        // Check masked parties
        $this->assertStringContainsString('*', $data['parties']['recipient']);

        // Check that internal tracking history does not leak internal user models
        if (!empty($data['history'])) {
            foreach ($data['history'] as $historyItem) {
                $this->assertArrayNotHasKey('user', $historyItem);
                $this->assertArrayNotHasKey('user_id', $historyItem);
            }
        }
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@courier.test',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@courier.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
