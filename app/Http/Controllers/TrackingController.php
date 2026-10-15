<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request, ?string $awb = null)
    {
        $trackingQuery = $awb ?? $request->get('awb', '');
        $shipments = collect();
        $notFoundAwbs = [];

        if (!empty($trackingQuery)) {
            // Support multiple AWBs separated by commas, spaces, or newlines
            $awbList = preg_split('/[\s,]+/', trim($trackingQuery));
            $awbList = array_filter(array_unique(array_map('trim', $awbList)));

            $shipments = Shipment::with(['originBranch', 'destinationBranch', 'service', 'trackings.branch', 'trackings.user'])
                ->whereIn('tracking_number', $awbList)
                ->get();

            $foundAwbs = $shipments->pluck('tracking_number')->all();
            $notFoundAwbs = array_diff($awbList, $foundAwbs);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $shipments,
                'not_found' => array_values($notFoundAwbs),
            ]);
        }

        return view('tracking.index', compact('shipments', 'trackingQuery', 'notFoundAwbs'));
    }
}
