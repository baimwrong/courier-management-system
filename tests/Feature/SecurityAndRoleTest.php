<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Manifest;
use App\Models\ManifestItem;
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

    public function test_courier_cannot_assign_courier(): void
    {
        $courier = User::where('role', 'courier')->first();
        $this->assertNotNull($courier);

        $otherCourier = User::where('role', 'courier')->where('id', '!=', $courier->id)->first()
            ?? User::factory()->create(['role' => 'courier', 'branch_id' => $courier->branch_id]);

        $shipment = Shipment::first();
        $this->assertNotNull($shipment);

        // Courier attempting to post to assign-courier must be rejected with 403
        $response = $this->actingAs($courier)->post('/admin/shipments/' . $shipment->id . '/assign-courier', [
            'courier_id' => $otherCourier->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_branch_scoping_prevents_unauthorized_cross_branch_access(): void
    {
        $branchA = Branch::first();
        $branchB = Branch::where('id', '!=', $branchA->id)->first() ?? Branch::create([
            'code' => 'BDG-01',
            'name' => 'Bandung Branch',
            'type' => 'branch',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'address' => 'Jl. Asia Afrika',
            'is_active' => true,
        ]);

        $managerA = User::factory()->create([
            'role' => 'branch_manager',
            'branch_id' => $branchA->id,
            'is_active' => true,
        ]);

        // Shipment strictly belongs to Branch B
        $shipmentB = Shipment::create([
            'tracking_number' => 'AWBTESTBRANCHB99',
            'sender_name' => 'Alice',
            'sender_phone' => '08123456789',
            'sender_address' => 'Bandung',
            'sender_city' => 'Bandung',
            'recipient_name' => 'Bob',
            'recipient_phone' => '08987654321',
            'recipient_address' => 'Bandung',
            'recipient_city' => 'Bandung',
            'origin_branch_id' => $branchB->id,
            'destination_branch_id' => $branchB->id,
            'current_branch_id' => $branchB->id,
            'service_id' => 1,
            'package_type' => 'parcel',
            'actual_weight_kg' => 1,
            'chargeable_weight_kg' => 1,
            'base_rate' => 10000,
            'total_cost' => 10000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'current_status' => 'booking_created',
            'booking_date' => now(),
            'created_by_user_id' => $managerA->id,
        ]);

        // Manager A cannot view details of shipment in Branch B
        $response = $this->actingAs($managerA)->get('/admin/shipments/' . $shipmentB->id);
        $response->assertStatus(403);

        // Manager A cannot print label of shipment in Branch B
        $response = $this->actingAs($managerA)->get('/admin/shipments/' . $shipmentB->id . '/print');
        $response->assertStatus(403);

        // Manager A cannot update status of shipment in Branch B
        $response = $this->actingAs($managerA)->post('/admin/shipments/' . $shipmentB->id . '/update-status', [
            'status' => 'received_at_dest_branch',
            'title' => 'Unauthorized update',
        ]);
        $response->assertStatus(403);
    }

    public function test_manifest_arrival_auto_cascades_status_to_shipments(): void
    {
        $admin = User::where('role', 'superadmin')->first();
        $this->assertNotNull($admin);

        $originBranch = Branch::first();
        $destBranch = Branch::where('id', '!=', $originBranch->id)->first();
        $this->assertNotNull($destBranch);

        $shipment = Shipment::create([
            'tracking_number' => 'AWBCASCADE001',
            'sender_name' => 'Origin Shipper',
            'sender_phone' => '0811111111',
            'sender_address' => 'Origin Address',
            'sender_city' => $originBranch->city,
            'recipient_name' => 'Dest Consignee',
            'recipient_phone' => '0822222222',
            'recipient_address' => 'Dest Address',
            'recipient_city' => $destBranch->city,
            'origin_branch_id' => $originBranch->id,
            'destination_branch_id' => $destBranch->id,
            'current_branch_id' => $originBranch->id,
            'service_id' => 1,
            'package_type' => 'parcel',
            'actual_weight_kg' => 2,
            'chargeable_weight_kg' => 2,
            'base_rate' => 20000,
            'total_cost' => 20000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'current_status' => 'manifested_in_transit',
            'booking_date' => now(),
            'created_by_user_id' => $admin->id,
        ]);

        $manifest = Manifest::create([
            'manifest_number' => 'MNF-TEST-CASCADE-01',
            'type' => 'inter_hub_transit',
            'status' => 'in_transit',
            'origin_branch_id' => $originBranch->id,
            'destination_branch_id' => $destBranch->id,
            'created_by' => $admin->id,
            'total_shipments' => 1,
            'total_weight_kg' => 2,
            'dispatched_at' => now(),
        ]);

        $item = ManifestItem::create([
            'manifest_id' => $manifest->id,
            'shipment_id' => $shipment->id,
            'status' => 'loaded',
        ]);

        // Mark manifest arrived
        $response = $this->actingAs($admin)->post('/admin/manifests/' . $manifest->id . '/status', [
            'status' => 'arrived',
        ]);

        $response->assertRedirect();

        // Refresh models
        $manifest->refresh();
        $shipment->refresh();
        $item->refresh();

        $this->assertEquals('arrived', $manifest->status);
        $this->assertNotNull($manifest->received_at);
        $this->assertEquals('received_at_dest_branch', $shipment->current_status);
        $this->assertEquals($destBranch->id, $shipment->current_branch_id);
        $this->assertEquals('received', $item->status);
        $this->assertNotNull($item->scanned_at);

        // Verify tracking record was inserted
        $this->assertDatabaseHas('shipment_trackings', [
            'shipment_id' => $shipment->id,
            'status' => 'received_at_dest_branch',
            'branch_id' => $destBranch->id,
            'manifest_code' => $manifest->manifest_number,
        ]);
    }

    public function test_batch_printing_scopes_shipments_by_user_branch(): void
    {
        $branchA = Branch::first();
        $branchB = Branch::where('id', '!=', $branchA->id)->first();

        $managerA = User::factory()->create([
            'role' => 'branch_manager',
            'branch_id' => $branchA->id,
            'is_active' => true,
        ]);

        $shipmentA = Shipment::create([
            'tracking_number' => 'AWBBATCHPRINTA',
            'sender_name' => 'Shipper A',
            'sender_phone' => '08123456789',
            'sender_address' => 'Branch A City',
            'sender_city' => $branchA->city,
            'recipient_name' => 'Consignee A',
            'recipient_phone' => '08987654321',
            'recipient_address' => 'Branch A City',
            'recipient_city' => $branchA->city,
            'origin_branch_id' => $branchA->id,
            'destination_branch_id' => $branchA->id,
            'current_branch_id' => $branchA->id,
            'service_id' => 1,
            'package_type' => 'document',
            'actual_weight_kg' => 1,
            'chargeable_weight_kg' => 1,
            'base_rate' => 10000,
            'total_cost' => 10000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'current_status' => 'booking_created',
            'booking_date' => now(),
            'created_by_user_id' => $managerA->id,
        ]);

        $shipmentB = Shipment::create([
            'tracking_number' => 'AWBBATCHPRINTB',
            'sender_name' => 'Shipper B',
            'sender_phone' => '08123456789',
            'sender_address' => 'Branch B City',
            'sender_city' => $branchB->city,
            'recipient_name' => 'Consignee B',
            'recipient_phone' => '08987654321',
            'recipient_address' => 'Branch B City',
            'recipient_city' => $branchB->city,
            'origin_branch_id' => $branchB->id,
            'destination_branch_id' => $branchB->id,
            'current_branch_id' => $branchB->id,
            'service_id' => 1,
            'package_type' => 'document',
            'actual_weight_kg' => 1,
            'chargeable_weight_kg' => 1,
            'base_rate' => 10000,
            'total_cost' => 10000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'current_status' => 'booking_created',
            'booking_date' => now(),
            'created_by_user_id' => $managerA->id,
        ]);

        // When Manager A requests batch print containing both shipments, only shipment A should be rendered
        $response = $this->actingAs($managerA)->post('/admin/shipments/print-batch', [
            'ids' => [$shipmentA->id, $shipmentB->id],
        ]);

        $response->assertStatus(200);
        $response->assertSee('AWBBATCHPRINTA');
        $response->assertDontSee('AWBBATCHPRINTB');
    }
}
