<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Manifest;
use App\Models\Service;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        // Branch-level scoping if user is restricted to a branch
        $user = auth()->user();
        $branchQuery = Shipment::query();

        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $branchQuery->where(function ($q) use ($user) {
                $q->where('origin_branch_id', $user->branch_id)
                  ->orWhere('destination_branch_id', $user->branch_id)
                  ->orWhere('current_branch_id', $user->branch_id);
            });
        }

        // Key KPIs
        $totalShipments = (clone $branchQuery)->count();
        $todayBookings = (clone $branchQuery)->whereDate('booking_date', $today)->count();
        $inTransit = (clone $branchQuery)->whereIn('current_status', ['received_at_origin_hub', 'manifested_in_transit', 'received_at_transit_hub'])->count();
        $outForDelivery = (clone $branchQuery)->where('current_status', 'out_for_delivery')->count();
        $deliveredCount = (clone $branchQuery)->where('current_status', 'delivered')->count();
        $failedAttempts = (clone $branchQuery)->whereIn('current_status', ['failed_attempt', 'returned_to_sender'])->count();
        $totalRevenue = (clone $branchQuery)->sum('shipping_cost');
        $pendingCodAmount = (clone $branchQuery)->where('payment_status', 'pending_cod')->sum('cod_amount');

        // Recent Shipments
        $recentShipments = (clone $branchQuery)
            ->with(['originBranch', 'destinationBranch', 'service', 'courier'])
            ->latest('id')
            ->take(8)
            ->get();

        // Status breakdown for Doughnut Chart
        $statusCounts = (clone $branchQuery)
            ->select('current_status', DB::raw('count(*) as count'))
            ->groupBy('current_status')
            ->pluck('count', 'current_status')
            ->all();

        // 7-day trend
        $trendDates = [];
        $trendCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trendDates[] = $date->format('d M');
            $trendCounts[] = (clone $branchQuery)->whereDate('booking_date', $date)->count();
        }

        // Service distribution
        $services = Service::withCount('shipments')->orderBy('display_order')->get();

        // Top corridors
        $topRoutes = (clone $branchQuery)
            ->select('sender_city', 'recipient_city', DB::raw('count(*) as total'))
            ->groupBy('sender_city', 'recipient_city')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalShipments',
            'todayBookings',
            'inTransit',
            'outForDelivery',
            'deliveredCount',
            'failedAttempts',
            'totalRevenue',
            'pendingCodAmount',
            'recentShipments',
            'statusCounts',
            'trendDates',
            'trendCounts',
            'services',
            'topRoutes'
        ));
    }
}
