<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Service;
use App\Models\Tariff;
use App\Services\ShipmentService;
use Illuminate\Http\Request;

class RateCalculatorController extends Controller
{
    public function __construct(protected ShipmentService $shipmentService)
    {
    }

    public function index()
    {
        $originCities = Tariff::select('origin_city')->distinct()->orderBy('origin_city')->pluck('origin_city');
        $destCities = Tariff::select('destination_city')->distinct()->orderBy('destination_city')->pluck('destination_city');

        if ($originCities->isEmpty()) {
            $originCities = Branch::distinct()->orderBy('city')->pluck('city');
            $destCities = $originCities;
        }

        return view('rates.index', compact('originCities', 'destCities'));
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'origin_city' => 'required|string',
            'destination_city' => 'required|string',
            'weight' => 'nullable|numeric|min:0.1',
            'length' => 'nullable|numeric|min:1',
            'width' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'declared_value' => 'nullable|numeric|min:0',
            'cod_amount' => 'nullable|numeric|min:0',
        ]);

        $actualWeight = (float) ($validated['weight'] ?? 1.0);
        $length = (float) ($validated['length'] ?? 10.0);
        $width = (float) ($validated['width'] ?? 10.0);
        $height = (float) ($validated['height'] ?? 10.0);
        $declaredValue = (float) ($validated['declared_value'] ?? 0.0);
        $codAmount = (float) ($validated['cod_amount'] ?? 0.0);

        $services = Service::where('is_active', true)->orderBy('display_order')->get();
        $results = [];

        foreach ($services as $service) {
            $charges = $this->shipmentService->calculateCharges(
                $validated['origin_city'],
                $validated['destination_city'],
                $service->id,
                $actualWeight,
                $length,
                $width,
                $height,
                $declaredValue,
                $codAmount
            );

            $results[] = [
                'service_code' => $service->code,
                'service_name' => $service->name,
                'description' => $service->description,
                'etd' => $charges['estimated_etd'],
                'actual_weight' => $charges['actual_weight_kg'],
                'volumetric_weight' => $charges['volumetric_weight_kg'],
                'chargeable_weight' => $charges['chargeable_weight_kg'],
                'shipping_cost' => $charges['shipping_cost'],
                'insurance_cost' => $charges['insurance_cost'],
                'cod_fee' => $charges['cod_fee'],
                'total_cost' => $charges['total_cost'],
                'badge_class' => $service->badge_class,
            ];
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'origin' => $validated['origin_city'],
                'destination' => $validated['destination_city'],
                'results' => $results,
            ]);
        }

        $originCities = Tariff::select('origin_city')->distinct()->orderBy('origin_city')->pluck('origin_city');
        $destCities = Tariff::select('destination_city')->distinct()->orderBy('destination_city')->pluck('destination_city');

        return view('rates.index', [
            'originCities' => $originCities,
            'destCities' => $destCities,
            'selectedOrigin' => $validated['origin_city'],
            'selectedDest' => $validated['destination_city'],
            'params' => $validated,
            'results' => $results,
        ]);
    }
}
