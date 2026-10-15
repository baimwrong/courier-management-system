<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        $query = Shipment::whereDate('booking_date', '>=', $dateFrom)
            ->whereDate('booking_date', '<=', $dateTo);

        $totalCount = (clone $query)->count();
        $deliveredCount = (clone $query)->where('current_status', 'delivered')->count();
        $inTransitCount = (clone $query)->whereIn('current_status', ['manifested_in_transit', 'received_at_origin_hub', 'received_at_transit_hub', 'received_at_dest_branch', 'out_for_delivery'])->count();
        $failedCount = (clone $query)->whereIn('current_status', ['failed_attempt', 'returned_to_sender'])->count();

        $totalRevenue = (clone $query)->sum('shipping_cost');
        $totalCodCollected = (clone $query)->where('current_status', 'delivered')->where('payment_method', 'cod')->sum('cod_amount');
        $totalCodPending = (clone $query)->where('payment_status', 'pending_cod')->sum('cod_amount');

        // COD Courier balance summary
        $courierCod = Shipment::with('courier')
            ->whereNotNull('assigned_courier_id')
            ->where('payment_method', 'cod')
            ->select('assigned_courier_id', DB::raw('sum(cod_amount) as total_cod'), DB::raw('count(*) as count'))
            ->groupBy('assigned_courier_id')
            ->get();

        // Branch throughput
        $branchStats = Branch::withCount(['originShipments', 'destinationShipments'])->get();

        return view('admin.reports.index', compact(
            'dateFrom',
            'dateTo',
            'totalCount',
            'deliveredCount',
            'inTransitCount',
            'failedCount',
            'totalRevenue',
            'totalCodCollected',
            'totalCodPending',
            'courierCod',
            'branchStats'
        ));
    }

    public function exportCsv(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        $shipments = Shipment::with(['originBranch', 'destinationBranch', 'service', 'courier'])
            ->whereDate('booking_date', '>=', $dateFrom)
            ->whereDate('booking_date', '<=', $dateTo)
            ->latest('id')
            ->get();

        $filename = "shipments_export_{$dateFrom}_to_{$dateTo}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($shipments) {
            $handle = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($handle, [
                'Tracking Number',
                'Booking Date',
                'Origin Hub',
                'Destination Hub',
                'Service Code',
                'Sender Name',
                'Sender Phone',
                'Sender City',
                'Recipient Name',
                'Recipient Phone',
                'Recipient City',
                'Chargeable Weight (KG)',
                'Shipping Cost (IDR)',
                'COD Amount (IDR)',
                'Payment Method',
                'Payment Status',
                'Current Status',
                'POD Recipient',
                'POD Delivered At',
            ]);

            foreach ($shipments as $s) {
                fputcsv($handle, [
                    $s->tracking_number,
                    $s->booking_date->toDateTimeString(),
                    $s->originBranch?->name ?? 'N/A',
                    $s->destinationBranch?->name ?? 'N/A',
                    $s->service?->code ?? 'N/A',
                    $s->sender_name,
                    $s->sender_phone,
                    $s->sender_city,
                    $s->recipient_name,
                    $s->recipient_phone,
                    $s->recipient_city,
                    $s->chargeable_weight_kg,
                    $s->shipping_cost,
                    $s->cod_amount,
                    $s->payment_method,
                    $s->payment_status,
                    $s->status_label,
                    $s->pod_recipient_name,
                    $s->pod_delivered_at ? $s->pod_delivered_at->toDateTimeString() : '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
