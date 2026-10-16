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

            // Eager load only non-sensitive relations (exclude staff/user models)
            $shipments = Shipment::with(['originBranch', 'destinationBranch', 'service', 'trackings.branch'])
                ->whereIn('tracking_number', $awbList)
                ->get();

            $foundAwbs = $shipments->pluck('tracking_number')->all();
            $notFoundAwbs = array_diff($awbList, $foundAwbs);
        }

        if ($request->wantsJson()) {
            $sanitized = $shipments->map(function ($s) {
                return [
                    'tracking_number' => $s->tracking_number,
                    'booking_date' => $s->booking_date?->toIso8601String(),
                    'current_status' => $s->current_status,
                    'status_label' => $s->status_label,
                    'service' => [
                        'code' => $s->service?->code,
                        'name' => $s->service?->name,
                        'etd_range' => $s->service?->etd_range,
                    ],
                    'origin' => [
                        'city' => $s->sender_city,
                        'hub' => $s->originBranch?->name,
                    ],
                    'destination' => [
                        'city' => $s->recipient_city,
                        'province' => $s->recipient_province,
                    ],
                    'parties' => [
                        'sender' => $s->masked_sender_name,
                        'recipient' => $s->masked_recipient_name,
                    ],
                    'package' => [
                        'type' => $s->package_type,
                        'chargeable_weight_kg' => (float) $s->chargeable_weight_kg,
                        'piece_count' => (int) $s->piece_count,
                    ],
                    'is_cod' => $s->isCod(),
                    'cod_amount' => $s->isCod() ? (float) $s->cod_amount : null,
                    'pod' => $s->isDelivered() ? [
                        'recipient_name' => $s->pod_recipient_name,
                        'relationship' => $s->pod_relationship,
                        'delivered_at' => $s->pod_delivered_at?->toIso8601String(),
                    ] : null,
                    'history' => $s->trackings->map(function ($t) {
                        return [
                            'status' => $t->status,
                            'title' => $t->title,
                            'location_city' => $t->location_city,
                            'location_details' => $t->location_details,
                            'description' => $t->description,
                            'occurred_at' => $t->occurred_at?->toIso8601String(),
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $sanitized,
                'not_found' => array_values($notFoundAwbs),
            ]);
        }

        return view('tracking.index', compact('shipments', 'trackingQuery', 'notFoundAwbs'));
    }
}
