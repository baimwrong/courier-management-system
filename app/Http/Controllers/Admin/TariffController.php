<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index(Request $request)
    {
        $query = Tariff::with('service')->latest('id');

        if ($request->filled('origin')) {
            $query->whereRaw('LOWER(origin_city) like ?', ['%' . strtolower(trim($request->origin)) . '%']);
        }
        if ($request->filled('destination')) {
            $query->whereRaw('LOWER(destination_city) like ?', ['%' . strtolower(trim($request->destination)) . '%']);
        }
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $tariffs = $query->paginate(20)->withQueryString();
        $services = Service::where('is_active', true)->get();

        return view('admin.tariffs.index', compact('tariffs', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin_city' => 'required|string|max:100',
            'origin_province' => 'nullable|string|max:100',
            'destination_city' => 'required|string|max:100',
            'destination_province' => 'nullable|string|max:100',
            'service_id' => 'required|exists:services,id',
            'base_weight_kg' => 'required|numeric|min:0.1',
            'base_rate' => 'required|numeric|min:0',
            'per_kg_rate' => 'required|numeric|min:0',
            'estimated_etd' => 'required|string|max:50',
        ]);

        $tariff = Tariff::create($validated);

        return back()->with('success', "Tariff route {$tariff->origin_city} -> {$tariff->destination_city} saved.");
    }

    public function destroy($id)
    {
        $tariff = Tariff::findOrFail($id);
        $tariff->delete();

        return back()->with('success', 'Tariff entry deleted successfully.');
    }
}
