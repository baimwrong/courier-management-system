<?php

namespace App\Services;

use App\Models\BulkImport;
use App\Models\Service;
use App\Models\Shipment;
use App\Models\Tariff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkImportService
{
    public function __construct(protected ShipmentService $shipmentService)
    {
    }

    /**
     * Parse CSV file into rows with headers.
     */
    protected function parseCsv($file): array
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $rows = [];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("Cannot read uploaded CSV file.");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception("Failed to open CSV stream.");
        }

        // Read header
        $rawHeader = fgetcsv($handle, 4096, ',');
        if (!$rawHeader) {
            fclose($handle);
            return [];
        }

        // Remove UTF-8 BOM if present
        $rawHeader[0] = preg_replace('/[\x{EF}\x{BB}\x{BF}]/u', '', $rawHeader[0]);
        $headers = array_map(fn($h) => strtolower(trim($h)), $rawHeader);

        $lineNumber = 1;
        while (($data = fgetcsv($handle, 4096, ',')) !== false) {
            $lineNumber++;
            // Skip empty rows
            if (count(array_filter($data, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = isset($data[$index]) ? trim($data[$index]) : '';
            }
            $row['_line'] = $lineNumber;
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Process bulk shipments import from CSV.
     */
    public function importShipments($file, User $user, string $originalFilename): array
    {
        $rows = $this->parseCsv($file);
        $totalRows = count($rows);
        $successful = 0;
        $failed = 0;
        $errors = [];
        $createdShipments = [];

        $servicesByCode = Service::all()->keyBy(fn($s) => strtoupper($s->code));
        $defaultService = $servicesByCode->get('REG') ?? Service::first();

        foreach ($rows as $row) {
            $line = $row['_line'];
            try {
                // Validation
                if (empty($row['sender_name']) || empty($row['recipient_name'])) {
                    throw new \Exception("Sender and Recipient names are required.");
                }
                if (empty($row['sender_city']) || empty($row['recipient_city'])) {
                    throw new \Exception("Origin and Destination cities are required.");
                }
                if (empty($row['sender_phone']) || empty($row['recipient_phone'])) {
                    throw new \Exception("Contact numbers are required.");
                }

                // Resolve service
                $serviceCode = strtoupper($row['service_code'] ?? 'REG');
                $service = $servicesByCode->get($serviceCode, $defaultService);

                $data = [
                    'sender_name' => $row['sender_name'],
                    'sender_phone' => $row['sender_phone'],
                    'sender_address' => $row['sender_address'] ?? $row['sender_city'],
                    'sender_city' => $row['sender_city'],
                    'sender_province' => $row['sender_province'] ?? null,
                    'sender_postal_code' => $row['sender_postal_code'] ?? null,

                    'recipient_name' => $row['recipient_name'],
                    'recipient_phone' => $row['recipient_phone'],
                    'recipient_address' => $row['recipient_address'] ?? $row['recipient_city'],
                    'recipient_city' => $row['recipient_city'],
                    'recipient_province' => $row['recipient_province'] ?? null,
                    'recipient_postal_code' => $row['recipient_postal_code'] ?? null,

                    'package_type' => $row['package_type'] ?? 'parcel',
                    'package_description' => $row['package_description'] ?? 'General Cargo',
                    'actual_weight_kg' => (float) ($row['actual_weight_kg'] ?? 1.0),
                    'length_cm' => (float) ($row['length_cm'] ?? 10.0),
                    'width_cm' => (float) ($row['width_cm'] ?? 10.0),
                    'height_cm' => (float) ($row['height_cm'] ?? 10.0),

                    'service_id' => $service->id,
                    'declared_goods_value' => (float) ($row['declared_value'] ?? 0.0),
                    'cod_amount' => (float) ($row['cod_amount'] ?? 0.0),
                    'payment_method' => !empty($row['payment_method']) ? strtolower($row['payment_method']) : ((float) ($row['cod_amount'] ?? 0) > 0 ? 'cod' : 'cash'),
                    'special_instructions' => $row['special_instructions'] ?? null,
                ];

                $shipment = $this->shipmentService->createShipment($data, $user);
                $createdShipments[] = $shipment->tracking_number;
                $successful++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = [
                    'line' => $line,
                    'error' => $e->getMessage(),
                    'row' => array_intersect_key($row, array_flip(['sender_name', 'recipient_name', 'sender_city', 'recipient_city'])),
                ];
            }
        }

        // Save log
        BulkImport::create([
            'user_id' => $user->id,
            'import_type' => 'shipments',
            'original_filename' => $originalFilename,
            'total_rows' => $totalRows,
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'status' => $failed === 0 ? 'completed' : ($successful > 0 ? 'completed_with_errors' : 'failed'),
            'errors_log' => $errors,
        ]);

        return [
            'total' => $totalRows,
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
            'sample_created' => array_slice($createdShipments, 0, 5),
        ];
    }

    /**
     * Process bulk status scan updates from CSV or text list.
     */
    public function importStatusUpdates(
        $fileOrList,
        User $user,
        string $originalFilename,
        ?string $defaultStatus = null,
        ?string $defaultCity = null,
        ?string $defaultNotes = null
    ): array {
        $successful = 0;
        $failed = 0;
        $errors = [];
        $total = 0;

        // If file provided
        if ($fileOrList instanceof UploadedFile || (is_string($fileOrList) && file_exists($fileOrList))) {
            $rows = $this->parseCsv($fileOrList);
            $total = count($rows);

            foreach ($rows as $row) {
                $line = $row['_line'];
                $awb = trim($row['tracking_number'] ?? '');
                $status = trim($row['status'] ?? $defaultStatus);
                $city = trim($row['location_city'] ?? $defaultCity);
                $details = trim($row['location_details'] ?? '');
                $desc = trim($row['description'] ?? $defaultNotes);
                $manifest = trim($row['manifest_code'] ?? '');

                try {
                    if (empty($awb)) {
                        throw new \Exception("Tracking number is empty.");
                    }
                    if (empty($status)) {
                        throw new \Exception("Status is required for tracking update.");
                    }

                    $shipment = Shipment::where('tracking_number', $awb)->first();
                    if (!$shipment) {
                        throw new \Exception("Tracking number [{$awb}] not found in system.");
                    }

                    $statusTitle = match ($status) {
                        'picked_up' => 'Picked Up from Shipper',
                        'received_at_origin_hub' => 'Received at Origin Sorting Hub',
                        'manifested_in_transit' => 'Manifested & Dispatched In Transit',
                        'received_at_transit_hub' => 'Received at Transit Hub',
                        'received_at_dest_branch' => 'Received at Destination Delivery Branch',
                        'out_for_delivery' => 'With Delivery Courier (Out for Delivery)',
                        'delivered' => 'Shipment Delivered',
                        'failed_attempt' => 'Delivery Attempt Unsuccessful',
                        'returned_to_sender' => 'Returned to Sender',
                        default => ucfirst(str_replace('_', ' ', $status)),
                    };

                    $shipment->addTracking(
                        status: $status,
                        title: $statusTitle,
                        locationCity: $city ?: $shipment->origin_city,
                        locationDetails: $details,
                        description: $desc ?: "Status scanned as {$statusTitle}.",
                        userId: $user->id,
                        branchId: $user->branch_id,
                        manifestCode: $manifest ?: null
                    );

                    $successful++;
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = [
                        'line' => $line,
                        'awb' => $awb,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        } elseif (is_array($fileOrList)) {
            // Array of AWBs submitted via text box
            $total = count($fileOrList);
            foreach ($fileOrList as $index => $awb) {
                $awb = trim($awb);
                if (empty($awb)) continue;

                try {
                    $shipment = Shipment::where('tracking_number', $awb)->first();
                    if (!$shipment) {
                        throw new \Exception("Tracking number [{$awb}] not found.");
                    }

                    $status = $defaultStatus ?? 'received_at_origin_hub';
                    $statusTitle = ucfirst(str_replace('_', ' ', $status));

                    $shipment->addTracking(
                        status: $status,
                        title: $statusTitle,
                        locationCity: $defaultCity ?: ($user->branch?->city ?? 'Central Hub'),
                        locationDetails: $user->branch?->name,
                        description: $defaultNotes ?: "Batch scan updated to {$statusTitle}.",
                        userId: $user->id,
                        branchId: $user->branch_id
                    );

                    $successful++;
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = [
                        'line' => $index + 1,
                        'awb' => $awb,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        BulkImport::create([
            'user_id' => $user->id,
            'import_type' => 'status_updates',
            'original_filename' => $originalFilename,
            'total_rows' => $total,
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'status' => $failed === 0 ? 'completed' : ($successful > 0 ? 'completed_with_errors' : 'failed'),
            'errors_log' => $errors,
        ]);

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Process bulk tariffs import from CSV.
     */
    public function importTariffs($file, User $user, string $originalFilename): array
    {
        $rows = $this->parseCsv($file);
        $totalRows = count($rows);
        $successful = 0;
        $failed = 0;
        $errors = [];

        $servicesByCode = Service::all()->keyBy(fn($s) => strtoupper($s->code));

        foreach ($rows as $row) {
            $line = $row['_line'];
            try {
                if (empty($row['origin_city']) || empty($row['destination_city'])) {
                    throw new \Exception("Origin and Destination cities are required.");
                }

                $serviceCode = strtoupper(trim($row['service_code'] ?? 'REG'));
                $service = $servicesByCode->get($serviceCode);
                if (!$service) {
                    throw new \Exception("Unknown service code [{$serviceCode}].");
                }

                Tariff::updateOrCreate(
                    [
                        'origin_city' => trim($row['origin_city']),
                        'destination_city' => trim($row['destination_city']),
                        'service_id' => $service->id,
                    ],
                    [
                        'origin_province' => $row['origin_province'] ?? null,
                        'destination_province' => $row['destination_province'] ?? null,
                        'base_weight_kg' => (float) ($row['base_weight_kg'] ?? 1.0),
                        'base_rate' => (float) ($row['base_rate'] ?? 10000.0),
                        'per_kg_rate' => (float) ($row['per_kg_rate'] ?? 8000.0),
                        'estimated_etd' => $row['estimated_etd'] ?? '1-3 Days',
                        'is_active' => true,
                    ]
                );

                $successful++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = [
                    'line' => $line,
                    'error' => $e->getMessage(),
                ];
            }
        }

        BulkImport::create([
            'user_id' => $user->id,
            'import_type' => 'tariffs',
            'original_filename' => $originalFilename,
            'total_rows' => $totalRows,
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'status' => $failed === 0 ? 'completed' : ($successful > 0 ? 'completed_with_errors' : 'failed'),
            'errors_log' => $errors,
        ]);

        return [
            'total' => $totalRows,
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }
}
