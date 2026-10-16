<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Shipment;
use App\Models\Tariff;
use App\Models\User;
use App\Services\ShipmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function __construct(protected ShipmentService $shipmentService)
    {
    }

    public function index(Request $request)
    {
        $query = Shipment::with(['originBranch', 'destinationBranch', 'service', 'courier'])->latest('id');
        $query = $this->scopeShipmentsForUser($query, auth()->user());

        // Search
        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('tracking_number', 'like', $s)
                    ->orWhere('sender_name', 'like', $s)
                    ->orWhere('recipient_name', 'like', $s)
                    ->orWhere('sender_phone', 'like', $s)
                    ->orWhere('recipient_phone', 'like', $s)
                    ->orWhere('routing_code', 'like', $s);
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        // Service Filter
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Origin Branch Filter
        if ($request->filled('branch_id')) {
            $query->where('origin_branch_id', $request->branch_id);
        }

        // Payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }

        $shipments = $query->paginate(15)->withQueryString();

        $services = Service::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.shipments.index', compact('shipments', 'services', 'branches'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)->orderBy('display_order')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $userBranchId = auth()->user()->branch_id;

        $tariffCities = Tariff::select('origin_city as city')
            ->union(Tariff::select('destination_city as city'))
            ->union(Branch::select('city as city'))
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('admin.shipments.create', compact('services', 'branches', 'customers', 'userBranchId', 'tariffCities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sender_name' => 'required|string|max:150',
            'sender_phone' => 'required|string|max:50',
            'sender_address' => 'required|string',
            'sender_city' => 'required|string|max:100',
            'sender_province' => 'nullable|string|max:100',
            'sender_postal_code' => 'nullable|string|max:20',

            'recipient_name' => 'required|string|max:150',
            'recipient_phone' => 'required|string|max:50',
            'recipient_address' => 'required|string',
            'recipient_city' => 'required|string|max:100',
            'recipient_province' => 'nullable|string|max:100',
            'recipient_postal_code' => 'nullable|string|max:20',

            'service_id' => 'required|exists:services,id',
            'origin_branch_id' => 'nullable|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',

            'package_type' => 'required|string',
            'package_description' => 'nullable|string',
            'actual_weight_kg' => 'required|numeric|min:0.01',
            'length_cm' => 'required|numeric|min:1',
            'width_cm' => 'required|numeric|min:1',
            'height_cm' => 'required|numeric|min:1',
            'piece_count' => 'nullable|integer|min:1',

            'declared_goods_value' => 'nullable|numeric|min:0',
            'cod_amount' => 'nullable|numeric|min:0',
            'other_fee' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'special_instructions' => 'nullable|string',
        ]);

        $shipment = $this->shipmentService->createShipment($validated, auth()->user());

        return redirect()->route('admin.shipments.show', $shipment->id)
            ->with('success', "Shipment {$shipment->tracking_number} created successfully!");
    }

    public function show($id)
    {
        $shipment = Shipment::with([
            'originBranch',
            'destinationBranch',
            'currentBranch',
            'service',
            'creator',
            'courier',
            'customer',
            'trackings.branch',
            'trackings.user',
        ])->findOrFail($id);

        $this->authorizeShipmentAccess($shipment, auth()->user(), 'view');

        $couriers = User::where('role', 'courier')->where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.shipments.show', compact('shipment', 'couriers', 'branches'));
    }

    public function printLabel($id)
    {
        $shipment = Shipment::with(['originBranch', 'destinationBranch', 'service'])->findOrFail($id);
        $this->authorizeShipmentAccess($shipment, auth()->user(), 'print');

        return view('admin.shipments.print', compact('shipment'));
    }

    public function printBatch(Request $request)
    {
        $rawIds = $request->input('ids');
        if (is_string($rawIds)) {
            $ids = array_filter(explode(',', $rawIds));
        } elseif (is_array($rawIds)) {
            $ids = array_filter($rawIds);
        } else {
            $ids = [];
        }

        if (empty($ids)) {
            return redirect()->route('admin.shipments.index')->with('error', 'No shipments selected for batch printing.');
        }

        $query = Shipment::with(['originBranch', 'destinationBranch', 'service'])
            ->whereIn('id', $ids);

        $query = $this->scopeShipmentsForUser($query, auth()->user());
        $shipments = $query->get();

        if ($shipments->isEmpty()) {
            return redirect()->route('admin.shipments.index')->with('error', 'No authorized shipments found for the selected IDs.');
        }

        return view('admin.shipments.print_batch', compact('shipments'));
    }

    public function updateStatus(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        $this->authorizeShipmentAccess($shipment, auth()->user(), 'update_status');

        $validated = $request->validate([
            'status' => 'required|string',
            'title' => 'required|string',
            'location_city' => 'nullable|string',
            'location_details' => 'nullable|string',
            'description' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        DB::transaction(function () use ($shipment, $validated) {
            $shipment->addTracking(
                status: $validated['status'],
                title: $validated['title'],
                locationCity: $validated['location_city'] ?: ($shipment->currentBranch?->city ?? 'Central Hub'),
                locationDetails: $validated['location_details'],
                description: $validated['description'],
                userId: auth()->id(),
                branchId: $validated['branch_id'] ?? auth()->user()->branch_id
            );
        });

        return back()->with('success', "Tracking milestone logged and status updated to {$shipment->status_label}.");
    }

    public function recordPod(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        $this->authorizeShipmentAccess($shipment, auth()->user(), 'pod');

        $validated = $request->validate([
            'pod_recipient_name' => 'required|string|max:150',
            'pod_relationship' => 'required|string|max:50',
            'pod_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($shipment, $validated) {
            $updateData = [
                'current_status' => 'delivered',
                'pod_recipient_name' => $validated['pod_recipient_name'],
                'pod_relationship' => $validated['pod_relationship'],
                'pod_notes' => $validated['pod_notes'],
                'pod_delivered_at' => Carbon::now(),
            ];

            // If COD, mark paid upon proof of delivery
            if ($shipment->isCod()) {
                $updateData['payment_status'] = 'paid';
            }

            $shipment->update($updateData);

            $shipment->addTracking(
                status: 'delivered',
                title: "Delivered to [{$validated['pod_recipient_name']}]",
                locationCity: $shipment->recipient_city,
                locationDetails: 'Consignee Delivery Address',
                description: "Package received by {$validated['pod_recipient_name']} ({$validated['pod_relationship']}). Notes: " . ($validated['pod_notes'] ?? 'None'),
                userId: auth()->id(),
                branchId: auth()->user()->branch_id
            );
        });

        return back()->with('success', "Proof of Delivery successfully recorded for {$shipment->tracking_number}.");
    }

    public function assignCourier(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        $this->authorizeShipmentAccess($shipment, auth()->user(), 'assign');

        $validated = $request->validate([
            'courier_id' => 'required|exists:users,id',
        ]);

        $courier = User::findOrFail($validated['courier_id']);

        DB::transaction(function () use ($shipment, $courier) {
            $shipment->update([
                'assigned_courier_id' => $courier->id,
                'current_status' => 'out_for_delivery',
            ]);

            $shipment->addTracking(
                status: 'out_for_delivery',
                title: "Out for Delivery with [{$courier->name}]",
                locationCity: $courier->branch?->city ?? $shipment->recipient_city,
                locationDetails: $courier->vehicle_plate ? "Vehicle: {$courier->vehicle_type} ({$courier->vehicle_plate})" : null,
                description: "Shipment assigned to courier {$courier->name} for immediate delivery run.",
                userId: auth()->id(),
                branchId: auth()->user()->branch_id
            );
        });

        return back()->with('success', "Courier {$courier->name} assigned for delivery.");
    }

    public function calculateAjax(Request $request)
    {
        $validated = $request->validate([
            'origin_city' => 'required|string',
            'destination_city' => 'required|string',
            'service_id' => 'required|exists:services,id',
            'actual_weight_kg' => 'required|numeric|min:0.01',
            'length_cm' => 'required|numeric|min:1',
            'width_cm' => 'required|numeric|min:1',
            'height_cm' => 'required|numeric|min:1',
            'declared_goods_value' => 'nullable|numeric|min:0',
            'cod_amount' => 'nullable|numeric|min:0',
        ]);

        $charges = $this->shipmentService->calculateCharges(
            $validated['origin_city'],
            $validated['destination_city'],
            (int) $validated['service_id'],
            (float) $validated['actual_weight_kg'],
            (float) $validated['length_cm'],
            (float) $validated['width_cm'],
            (float) $validated['height_cm'],
            (float) ($validated['declared_goods_value'] ?? 0.0),
            (float) ($validated['cod_amount'] ?? 0.0)
        );

        return response()->json([
            'success' => true,
            'charges' => $charges,
        ]);
    }

    /**
     * Scope shipment query based on authenticated user's role and branch.
     */
    protected function scopeShipmentsForUser($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isCourier()) {
            return $query->where(function ($q) use ($user) {
                $q->where('assigned_courier_id', $user->id);
                if ($user->branch_id) {
                    $q->orWhere('origin_branch_id', $user->branch_id)
                      ->orWhere('destination_branch_id', $user->branch_id)
                      ->orWhere('current_branch_id', $user->branch_id);
                }
            });
        }

        if ($user->branch_id) {
            return $query->where(function ($q) use ($user) {
                $q->where('origin_branch_id', $user->branch_id)
                  ->orWhere('destination_branch_id', $user->branch_id)
                  ->orWhere('current_branch_id', $user->branch_id);
            });
        }

        return $query;
    }

    /**
     * Authorize that the user has permission to access this shipment.
     */
    protected function authorizeShipmentAccess(Shipment $shipment, User $user, string $action = 'view'): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isCourier()) {
            $isAssigned = $shipment->assigned_courier_id === $user->id;
            $isSameBranch = $user->branch_id && in_array($user->branch_id, [
                $shipment->origin_branch_id,
                $shipment->destination_branch_id,
                $shipment->current_branch_id,
            ]);

            if (!$isAssigned && !$isSameBranch) {
                abort(403, 'Unauthorized access: You are not assigned to this shipment nor does it belong to your branch.');
            }
            return;
        }

        if ($user->branch_id) {
            $isBranchRelated = in_array($user->branch_id, [
                $shipment->origin_branch_id,
                $shipment->destination_branch_id,
                $shipment->current_branch_id,
            ]);

            if (!$isBranchRelated) {
                abort(403, 'Unauthorized access: This shipment belongs to another operational branch.');
            }
        }
    }
}
