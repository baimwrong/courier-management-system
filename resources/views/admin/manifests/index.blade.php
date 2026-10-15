@extends('layouts.admin')

@section('title', 'Manifests & Delivery Run-Sheets')
@section('page_title', 'Dispatch Manifests & Courier Run-Sheets')

@section('breadcrumbs')
<span class="text-secondary small">/ Hub Transit & Last-Mile Run Sheets</span>
@endsection

@section('content')
<!-- Actions & Filters -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.manifests.index') }}" method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Manifest number or vehicle plate..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Manifest Types</option>
                        <option value="delivery_run_sheet" {{ request('type') == 'delivery_run_sheet' ? 'selected' : '' }}>Courier Delivery Run-Sheet</option>
                        <option value="inter_hub_transit" {{ request('type') == 'inter_hub_transit' ? 'selected' : '' }}>Inter-Hub Transit Cargo</option>
                        <option value="pickup" {{ request('type') == 'pickup' ? 'selected' : '' }}>First-Mile Pickup Run-Sheet</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="created" {{ request('status') == 'created' ? 'selected' : '' }}>Created / Staged</option>
                        <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>Dispatched / In Transit</option>
                        <option value="arrived" {{ request('status') == 'arrived' ? 'selected' : '' }}>Arrived at Destination</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed / Closed</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Manifests List Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark">Manifest Log</span>
        <a href="{{ route('admin.manifests.create') }}" class="btn btn-warning text-dark btn-sm fw-bold">
            <i class="fa-solid fa-plus me-1"></i> Create Manifest / Run-Sheet
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th class="ps-3">Manifest #</th>
                    <th>Type</th>
                    <th>Origin &rarr; Destination</th>
                    <th>Courier / Vehicle</th>
                    <th>Packages</th>
                    <th>Total Weight</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($manifests as $m)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('admin.manifests.show', $m->id) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $m->manifest_number }}
                            </a>
                            <div class="text-muted small" style="font-size: 0.7rem;">{{ $m->created_at->format('d M Y, H:i') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $m->type_label }}</span>
                        </td>
                        <td>
                            <div class="small fw-bold">
                                {{ $m->originBranch?->code ?? 'N/A' }}
                                <i class="fa-solid fa-arrow-right text-muted mx-1"></i>
                                {{ $m->destinationBranch?->code ?? ($m->type === 'delivery_run_sheet' ? 'Local Area' : 'N/A') }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.7rem;">{{ $m->originBranch?->city }}</div>
                        </td>
                        <td>
                            <div class="small fw-bold">{{ $m->courier?->name ?? 'Unassigned' }}</div>
                            <div class="text-muted small" style="font-size: 0.7rem;">
                                {{ $m->vehicle_type ?? 'Van' }} &bull; {{ $m->vehicle_plate ?? 'No Plate' }}
                            </div>
                        </td>
                        <td><span class="fw-bold">{{ $m->total_shipments }}</span> pkgs</td>
                        <td><span class="fw-bold">{{ $m->total_weight_kg }}</span> Kg</td>
                        <td>
                            <span class="badge {{ $m->status_badge_class }}">{{ ucfirst($m->status) }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.manifests.show', $m->id) }}" class="btn btn-outline-primary" title="View Manifest">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.manifests.print', $m->id) }}" target="_blank" class="btn btn-outline-dark" title="Print Run-Sheet">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No manifests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($manifests->hasPages())
        <div class="p-3 border-top">
            {{ $manifests->links() }}
        </div>
    @endif
</div>
@endsection
