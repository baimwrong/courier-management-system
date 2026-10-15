<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManifestController extends Controller
{
    public function index(Request $request)
    {
        $query = Manifest::with(['originBranch', 'destinationBranch', 'courier', 'creator'])->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('manifest_number', 'like', $s)
                    ->orWhere('vehicle_plate', 'like', $s);
            });
        }

        $manifests = $query->paginate(15)->withQueryString();

        return view('admin.manifests.index', compact('manifests'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $couriers = User::whereIn('role', ['courier', 'hub_operator'])->where('is_active', true)->get();
        
        // Available shipments not yet delivered
        $availableShipments = Shipment::whereNotIn('current_status', ['delivered', 'cancelled', 'returned_to_sender'])
            ->latest('id')
            ->take(50)
            ->get();

        return view('admin.manifests.create', compact('branches', 'couriers', 'availableShipments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'origin_branch_id' => 'required|exists:branches,id',
            'destination_branch_id' => 'nullable|exists:branches,id',
            'courier_id' => 'nullable|exists:users,id',
            'vehicle_plate' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'exists:shipments,id',
        ]);

        $manifest = DB::transaction(function () use ($validated) {
            $shipments = Shipment::whereIn('id', $validated['shipment_ids'])->get();
            $totalWeight = $shipments->sum('chargeable_weight_kg');

            $manifestNumber = Manifest::generateManifestNumber();

            $manifest = Manifest::create([
                'manifest_number' => $manifestNumber,
                'type' => $validated['type'],
                'origin_branch_id' => $validated['origin_branch_id'],
                'destination_branch_id' => $validated['destination_branch_id'] ?? null,
                'courier_id' => $validated['courier_id'] ?? null,
                'created_by' => auth()->id(),
                'vehicle_plate' => $validated['vehicle_plate'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'status' => 'created',
                'total_shipments' => $shipments->count(),
                'total_weight_kg' => $totalWeight,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($shipments as $shipment) {
                ManifestItem::create([
                    'manifest_id' => $manifest->id,
                    'shipment_id' => $shipment->id,
                    'status' => 'loaded',
                    'scanned_at' => Carbon::now(),
                ]);

                // Update shipment tracking milestone
                $statusType = $validated['type'] === 'delivery_run_sheet' ? 'out_for_delivery' : 'manifested_in_transit';
                $title = $validated['type'] === 'delivery_run_sheet'
                    ? "Assigned to Delivery Run-Sheet [{$manifestNumber}]"
                    : "Loaded on Inter-Hub Transit Container [{$manifestNumber}]";

                $shipment->addTracking(
                    status: $statusType,
                    title: $title,
                    locationCity: $manifest->originBranch?->city,
                    locationDetails: "Manifest #{$manifestNumber}",
                    description: "Waybill included in manifest batch. Driver/Courier: " . ($manifest->courier?->name ?? 'Unassigned'),
                    userId: auth()->id(),
                    branchId: $manifest->origin_branch_id,
                    manifestCode: $manifestNumber
                );
            }

            return $manifest;
        });

        return redirect()->route('admin.manifests.show', $manifest->id)
            ->with('success', "Manifest {$manifest->manifest_number} created with {$manifest->total_shipments} packages.");
    }

    public function show($id)
    {
        $manifest = Manifest::with([
            'originBranch',
            'destinationBranch',
            'courier',
            'creator',
            'items.shipment.originBranch',
            'items.shipment.destinationBranch',
            'items.shipment.service',
        ])->findOrFail($id);

        return view('admin.manifests.show', compact('manifest'));
    }

    public function printManifest($id)
    {
        $manifest = Manifest::with([
            'originBranch',
            'destinationBranch',
            'courier',
            'creator',
            'items.shipment.service',
        ])->findOrFail($id);

        return view('admin.manifests.print', compact('manifest'));
    }

    public function updateStatus(Request $request, $id)
    {
        $manifest = Manifest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:dispatched,in_transit,arrived,completed,cancelled',
        ]);

        $status = $validated['status'];
        $data = ['status' => $status];

        if ($status === 'dispatched' || $status === 'in_transit') {
            $data['dispatched_at'] = Carbon::now();
        } elseif ($status === 'arrived' || $status === 'completed') {
            $data['received_at'] = Carbon::now();
        }

        $manifest->update($data);

        return back()->with('success', "Manifest status updated to " . ucfirst(str_replace('_', ' ', $status)));
    }
}
