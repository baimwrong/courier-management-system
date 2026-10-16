<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use App\Services\BulkImportService;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentAndBulkImportTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Courier');
    }

    public function test_public_tracking_works_for_existing_awb(): void
    {
        $shipment = Shipment::first();
        $this->assertNotNull($shipment);

        $response = $this->get('/track/' . $shipment->tracking_number);
        $response->assertStatus(200);
        $response->assertSee($shipment->tracking_number);
        $response->assertSee($shipment->masked_sender_name);
        $response->assertSee($shipment->sender_city);
    }

    public function test_shipping_rates_calculator_computes_tariffs(): void
    {
        $response = $this->post('/rates/calculate', [
            'origin_city' => 'Jakarta',
            'destination_city' => 'Bandung',
            'weight' => 2.0,
            'length' => 20,
            'width' => 15,
            'height' => 10,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Jakarta');
        $response->assertSee('Bandung');
    }

    public function test_bulk_shipment_import_service(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        $importService = new BulkImportService(new ShipmentService());
        
        $templatePath = public_path('templates/shipments_sample_template.csv');
        $this->assertFileExists($templatePath);

        $result = $importService->importShipments($templatePath, $user, 'shipments_sample_template.csv');

        $this->assertEquals(4, $result['total']);
        $this->assertEquals(4, $result['successful']);
        $this->assertEquals(0, $result['failed']);
    }

    public function test_bulk_template_download(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/admin/bulk-import/template/shipments');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
