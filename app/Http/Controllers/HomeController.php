<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Service;
use App\Models\Shipment;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->orderBy('display_order')->get();
        $branches = Branch::where('is_active', true)->take(6)->get();
        $cities = Branch::where('is_active', true)->distinct()->pluck('city')->sort()->values();

        $stats = [
            'total_shipments' => Shipment::count(),
            'active_hubs' => Branch::where('is_active', true)->count(),
            'sla_rate' => '99.4%',
            'provinces_covered' => Branch::where('is_active', true)->distinct('province')->count('province') ?: 38,
        ];

        return view('home', compact('services', 'branches', 'cities', 'stats'));
    }
}
