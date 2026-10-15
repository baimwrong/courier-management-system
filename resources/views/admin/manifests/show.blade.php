@extends('layouts.admin')

@section('title', 'Manifest ' . $manifest->manifest_number)
@section('page_title', 'Manifest: ' . $manifest->manifest_number)

@section('breadcrumbs')
<span class="text-secondary small">/ Dispatch Manifest Details</span>
@endsection

@section('content')
<!-- Header Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge {{ $manifest->status_badge_class }} fs-6 px-3 py-2 fw-bold text-uppercase">
                        {{ ucfirst($manifest->status) }}
                    </span>
                    <span class="badge bg-secondary fs-6 px-3 py-2">
                        {{ $manifest->type_label }}
                    </span>
                </div>
                <h2 class="fw-bolder text-primary mb-1">{{ $manifest->manifest_number }}</h2>
                <div class="text-secondary small">
                    Created on {{ $manifest->created_at->format('d M Y, H:i') }} WIB &bull; Handled by {{ $manifest->creator?->name ?? 'Dispatcher' }}
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.manifests.print', $manifest->id) }}" target="_blank" class="btn btn-outline-dark fw-bold btn-sm shadow-sm">
                    <i class="fa-solid fa-print me-1"></i> Print Courier Run-Sheet
                </a>

                @if($manifest->status === 'created')
                    <form action="{{ route('admin.manifests.update_status', $manifest->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="dispatched">
                        <button type="submit" class="btn btn-primary-custom fw-bold btn-sm shadow-sm">
                            <i class="fa-solid fa-truck-fast me-1"></i> Dispatch / Start Route
                        </button>
                    </form>
                @elseif($manifest->status === 'dispatched' || $manifest->status === 'in_transit')
                    <form action="{{ route('admin.manifests.update_status', $manifest->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="arrived">
                        <button type="submit" class="btn btn-info text-white fw-bold btn-sm shadow-sm">
                            <i class="fa-solid fa-building-circle-check me-1"></i> Mark Arrived at Destination
                        </button>
                    </form>
                    <form action="{{ route('admin.manifests.update_status', $manifest->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-success fw-bold btn-sm shadow-sm">
                            <i class="fa-solid fa-circle-check me-1"></i> Complete Manifest Run
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Key Metadata Strip -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-secondary small fw-bold text-uppercase">Origin Hub</span>
            <div class="fw-bold fs-6 text-dark">{{ $manifest->originBranch?->name ?? 'N/A' }}</div>
            <div class="small text-muted">{{ $manifest->originBranch?->city }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-secondary small fw-bold text-uppercase">Destination Hub / Area</span>
            <div class="fw-bold fs-6 text-dark">{{ $manifest->destinationBranch?->name ?? 'Local Delivery Territory' }}</div>
            <div class="small text-muted">{{ $manifest->destinationBranch?->city ?? 'Local Zone' }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-secondary small fw-bold text-uppercase">Assigned Courier / Driver</span>
            <div class="fw-bold fs-6 text-dark">{{ $manifest->courier?->name ?? 'Unassigned' }}</div>
            <div class="small text-muted">{{ $manifest->vehicle_type ?? 'Van' }} &bull; {{ $manifest->vehicle_plate ?? 'No Plate' }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-secondary small fw-bold text-uppercase">Total Load</span>
            <div class="fw-bold fs-6 text-primary">{{ $manifest->total_shipments }} Packages</div>
            <div class="small text-muted">{{ $manifest->total_weight_kg }} Kg Total Weight</div>
        </div>
    </div>
</div>

<!-- Manifest Items Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom p-3">
        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Loaded Shipments ({{ $manifest->items->count() }})</h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th class="ps-3">#</th>
                    <th>AWB / Tracking Number</th>
                    <th>Recipient & Address</th>
                    <th>Weight</th>
                    <th>Service</th>
                    <th>COD Value</th>
                    <th>Current AWB Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($manifest->items as $idx => $it)
                    <tr>
                        <td class="ps-3 fw-bold text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <a href="{{ route('admin.shipments.show', $it->shipment->id) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $it->shipment->tracking_number }}
                            </a>
                            <div class="text-muted small" style="font-size: 0.7rem;">Routing: {{ $it->shipment->routing_code }}</div>
                        </td>
                        <td>
                            <div class="fw-bold small">{{ $it->shipment->recipient_name }} ({{ $it->shipment->recipient_phone }})</div>
                            <div class="text-muted small text-truncate" style="max-width: 280px;">{{ $it->shipment->recipient_address }}, {{ $it->shipment->recipient_city }}</div>
                        </td>
                        <td>
                            <span class="fw-bold small">{{ $it->shipment->chargeable_weight_kg }} Kg</span>
                        </td>
                        <td>
                            <span class="badge {{ $it->shipment->service->badge_class }}">{{ $it->shipment->service->code }}</span>
                        </td>
                        <td>
                            @if($it->shipment->isCod())
                                <span class="badge bg-warning text-dark fw-bold">Rp {{ number_format($it->shipment->cod_amount) }}</span>
                            @else
                                <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $it->shipment->status_badge_class }} small">{{ $it->shipment->status_label }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No items in this manifest batch.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
