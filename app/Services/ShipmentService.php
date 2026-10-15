<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Service;
use App\Models\Shipment;
use App\Models\Tariff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    /**
     * Calculate all shipping charges, weights and insurance.
     */
    public function calculateCharges(
        string $originCity,
        string $destCity,
        int $serviceId,
        float $actualWeight,
        float $length,
        float $width,
        float $height,
        float $declaredValue = 0.0,
        float $codAmount = 0.0
    ): array {
        $service = Service::findOrFail($serviceId);
        
        $volumetricWeight = Shipment::calculateVolumetricWeight(
            $length,
            $width,
            $height,
            $service->volumetric_divisor ?? 5000
        );

        $chargeableWeight = max($actualWeight, $volumetricWeight, (float) ($service->min_weight_kg ?? 1.0));

        // Find matching tariff
        $tariff = Tariff::whereRaw('LOWER(origin_city) = ?', [strtolower(trim($originCity))])
            ->whereRaw('LOWER(destination_city) = ?', [strtolower(trim($destCity))])
            ->where('service_id', $serviceId)
            ->where('is_active', true)
            ->first();

        if ($tariff) {
            $shippingCost = $tariff->calculateCost($chargeableWeight);
            $etd = $tariff->estimated_etd;
        } else {
            // Fallback base formula if specific tariff route isn't entered yet
            $baseRate = 15000.0 * (float) $service->price_multiplier;
            $perKg = 10000.0 * (float) $service->price_multiplier;
            $extra = ceil(max(0, $chargeableWeight - 1.0));
            $shippingCost = $baseRate + ($extra * $perKg);
            $etd = $service->etd_range;
        }

        // Insurance fee: 0.2% of declared goods value
        $insuranceCost = $declaredValue > 0 ? round($declaredValue * 0.002, 2) : 0.0;

        // COD fee: 2.5% of COD amount if COD
        $codFee = $codAmount > 0 ? round($codAmount * 0.025, 2) : 0.0;

        $totalCost = $shippingCost + $insuranceCost + $codFee;

        return [
            'service' => $service,
            'actual_weight_kg' => round($actualWeight, 2),
            'volumetric_weight_kg' => $volumetricWeight,
            'chargeable_weight_kg' => round($chargeableWeight, 2),
            'shipping_cost' => round($shippingCost, 2),
            'insurance_cost' => round($insuranceCost, 2),
            'cod_fee' => round($codFee, 2),
            'total_cost' => round($totalCost, 2),
            'estimated_etd' => $etd,
        ];
    }

    /**
     * Create a new shipment booking and log the initial event.
     */
    public function createShipment(array $data, ?User $creator = null): Shipment
    {
        return DB::transaction(function () use ($data, $creator) {
            $trackingNumber = Shipment::generateTrackingNumber();

            // Resolve branches
            $originBranchId = $data['origin_branch_id'] ?? null;
            if (!$originBranchId && !empty($data['sender_city'])) {
                $originBranch = Branch::whereRaw('LOWER(city) = ?', [strtolower(trim($data['sender_city']))])->first()
                    ?? Branch::first();
                $originBranchId = $originBranch->id;
            }

            $destinationBranchId = $data['destination_branch_id'] ?? null;
            if (!$destinationBranchId && !empty($data['recipient_city'])) {
                $destBranch = Branch::whereRaw('LOWER(city) = ?', [strtolower(trim($data['recipient_city']))])->first();
                $destinationBranchId = $destBranch?->id;
            }

            $service = Service::findOrFail($data['service_id']);

            // Calculate weight & pricing
            $actualWeight = (float) ($data['actual_weight_kg'] ?? 1.0);
            $length = (float) ($data['length_cm'] ?? 10.0);
            $width = (float) ($data['width_cm'] ?? 10.0);
            $height = (float) ($data['height_cm'] ?? 10.0);
            $declaredValue = (float) ($data['declared_goods_value'] ?? 0.0);
            $codAmount = (float) ($data['cod_amount'] ?? 0.0);

            $calc = $this->calculateCharges(
                $data['sender_city'],
                $data['recipient_city'],
                $service->id,
                $actualWeight,
                $length,
                $width,
                $height,
                $declaredValue,
                $codAmount
            );

            $routingCode = $this->determineRoutingCode(
                $data['sender_city'],
                $data['recipient_city'],
                $service->code
            );

            $shipment = Shipment::create([
                'tracking_number' => $trackingNumber,
                'booking_date' => Carbon::now(),
                'origin_branch_id' => $originBranchId,
                'destination_branch_id' => $destinationBranchId,
                'service_id' => $service->id,
                'creator_id' => $creator?->id,
                'current_branch_id' => $originBranchId,
                'customer_id' => $data['customer_id'] ?? null,

                'sender_name' => $data['sender_name'],
                'sender_phone' => $data['sender_phone'],
                'sender_address' => $data['sender_address'],
                'sender_city' => $data['sender_city'],
                'sender_province' => $data['sender_province'] ?? null,
                'sender_postal_code' => $data['sender_postal_code'] ?? null,

                'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'],
                'recipient_address' => $data['recipient_address'],
                'recipient_city' => $data['recipient_city'],
                'recipient_province' => $data['recipient_province'] ?? null,
                'recipient_postal_code' => $data['recipient_postal_code'] ?? null,

                'package_type' => $data['package_type'] ?? 'parcel',
                'package_description' => $data['package_description'] ?? null,
                'actual_weight_kg' => $calc['actual_weight_kg'],
                'length_cm' => $length,
                'width_cm' => $width,
                'height_cm' => $height,
                'volumetric_weight_kg' => $calc['volumetric_weight_kg'],
                'chargeable_weight_kg' => $calc['chargeable_weight_kg'],
                'piece_count' => (int) ($data['piece_count'] ?? 1),

                'shipping_cost' => $calc['shipping_cost'],
                'insurance_cost' => $calc['insurance_cost'],
                'declared_goods_value' => $declaredValue,
                'cod_amount' => $codAmount,
                'cod_fee' => $calc['cod_fee'],
                'other_fee' => (float) ($data['other_fee'] ?? 0.0),
                'discount_amount' => (float) ($data['discount_amount'] ?? 0.0),
                'total_cost' => $calc['total_cost'],
                'payment_method' => $data['payment_method'] ?? ($codAmount > 0 ? 'cod' : 'cash'),
                'payment_status' => $data['payment_status'] ?? ($codAmount > 0 ? 'pending_cod' : 'paid'),

                'current_status' => 'booking_created',
                'routing_code' => $routingCode,
                'special_instructions' => $data['special_instructions'] ?? null,
            ]);

            // Add initial tracking milestone
            $originBranchName = Branch::find($originBranchId)?->name ?? 'Origin Branch';
            $shipment->trackings()->create([
                'status' => 'booking_created',
                'title' => 'Shipment Booking Created',
                'location_city' => $data['sender_city'],
                'location_details' => $originBranchName,
                'description' => "Waybill {$trackingNumber} registered. Ready for origin sorting.",
                'user_id' => $creator?->id,
                'branch_id' => $originBranchId,
                'occurred_at' => Carbon::now(),
            ]);

            return $shipment;
        });
    }

    /**
     * Generate clear routing code e.g. CGK-BDG-01
     */
    public function determineRoutingCode(string $originCity, string $destCity, string $serviceCode): string
    {
        $origPrefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $originCity), 0, 3));
        $destPrefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $destCity), 0, 3));
        $serv = strtoupper($serviceCode);

        return "{$origPrefix}-{$destPrefix}-{$serv}";
    }
}
