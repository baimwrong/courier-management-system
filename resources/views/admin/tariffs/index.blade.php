@extends('layouts.admin')

@section('title', 'Tariff Rate Matrix')
@section('page_title', 'Shipping Tariff Rate Matrix')

@section('breadcrumbs')
<span class="text-secondary small">/ Nationwide Pricing Grid</span>
@endsection

@section('content')
<!-- Filter & Add Button Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.tariffs.index') }}" method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="origin" class="form-control" placeholder="Origin city..." value="{{ request('origin') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="destination" class="form-control" placeholder="Destination city..." value="{{ request('destination') }}">
                </div>
                <div class="col-md-3">
                    <select name="service_id" class="form-select">
                        <option value="">All Services</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}" {{ request('service_id') == $srv->id ? 'selected' : '' }}>{{ $srv->code }} - {{ $srv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom fw-bold flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddTariff">
                        <i class="fa-solid fa-plus me-1"></i> Add Route
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tariffs Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold text-dark">Route Pricing Grid</span>
            <span class="badge bg-secondary ms-2">{{ $tariffs->total() }} Routes</span>
        </div>
        <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-success btn-sm fw-bold">
            <i class="fa-solid fa-file-csv me-1"></i> Bulk Import Tariffs (CSV)
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th class="ps-3">Origin</th>
                    <th>Destination</th>
                    <th>Service Tier</th>
                    <th>Base Weight</th>
                    <th>Base Rate (IDR)</th>
                    <th>Per-Kg Rate (IDR)</th>
                    <th>Estimated ETD</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tariffs as $t)
                    <tr>
                        <td class="ps-3 fw-bold text-dark">{{ $t->origin_city }}</td>
                        <td class="fw-bold text-dark">{{ $t->destination_city }}</td>
                        <td>
                            <span class="badge {{ $t->service->badge_class }}">{{ $t->service->code }}</span>
                            <span class="small text-muted ms-1">{{ $t->service->name }}</span>
                        </td>
                        <td>{{ $t->base_weight_kg }} Kg</td>
                        <td class="fw-bold text-primary">Rp {{ number_format($t->base_rate) }}</td>
                        <td>Rp {{ number_format($t->per_kg_rate) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $t->estimated_etd }}</span></td>
                        <td class="text-end pe-3">
                            <form action="{{ route('admin.tariffs.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Delete this tariff route?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Tariff">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No tariff rates found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tariffs->hasPages())
        <div class="p-3 border-top">
            {{ $tariffs->links() }}
        </div>
    @endif
</div>

<!-- Modal: Add Tariff -->
<div class="modal fade" id="modalAddTariff" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.tariffs.store') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-calculator text-primary me-2"></i> Add Route Tariff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Origin City *</label>
                            <input type="text" name="origin_city" class="form-control" placeholder="e.g. Jakarta" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Origin Province</label>
                            <input type="text" name="origin_province" class="form-control" placeholder="e.g. DKI Jakarta">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Destination City *</label>
                            <input type="text" name="destination_city" class="form-control" placeholder="e.g. Bandung" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Destination Province</label>
                            <input type="text" name="destination_province" class="form-control" placeholder="e.g. Jawa Barat">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Service Product *</label>
                        <select name="service_id" class="form-select" required>
                            @foreach($services as $srv)
                                <option value="{{ $srv->id }}">{{ $srv->code }} - {{ $srv->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Base Wt (Kg)</label>
                            <input type="number" step="0.1" name="base_weight_kg" class="form-control" value="1.0" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Base Rate</label>
                            <input type="number" name="base_rate" class="form-control" placeholder="e.g. 15000" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Per-Kg Rate</label>
                            <input type="number" name="per_kg_rate" class="form-control" placeholder="e.g. 10000" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Estimated ETD *</label>
                        <input type="text" name="estimated_etd" class="form-control" placeholder="e.g. 1-2 Days" value="1-2 Days" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom fw-bold">Save Rate Route</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
