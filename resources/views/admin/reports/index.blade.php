@extends('layouts.admin')

@section('title', 'Operational & COD Reports')
@section('page_title', 'Analytics & COD Reconciliation Reports')

@section('breadcrumbs')
<span class="text-secondary small">/ Performance Auditing & Finance</span>
@endsection

@section('content')
<!-- Filter & Export Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.reports.index') }}" method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Date Range (From)</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Date Range (To)</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2 mt-auto">
                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold">
                        <i class="fa-solid fa-filter me-1"></i> Apply Filter
                    </button>
                </div>
                <div class="col-md-2 mt-auto">
                    <a href="{{ route('admin.reports.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-success w-100 fw-bold shadow-sm">
                        <i class="fa-solid fa-file-csv me-1"></i> Export to CSV
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Performance KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-secondary small fw-bold text-uppercase">Total Period Shipments</span>
            <div class="h3 fw-bold text-dark mb-0">{{ number_format($totalCount) }}</div>
            <span class="small text-muted">{{ $dateFrom }} to {{ $dateTo }}</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-secondary small fw-bold text-uppercase">Delivered (Success)</span>
            <div class="h3 fw-bold text-success mb-0">{{ number_format($deliveredCount) }}</div>
            <span class="small text-muted">Rate: {{ $totalCount > 0 ? round(($deliveredCount / $totalCount) * 100, 1) : 0 }}%</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-secondary small fw-bold text-uppercase">Active In-Transit</span>
            <div class="h3 fw-bold text-primary mb-0">{{ number_format($inTransitCount) }}</div>
            <span class="small text-muted">Currently moving</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-secondary small fw-bold text-uppercase">Exceptions / Failed</span>
            <div class="h3 fw-bold text-danger mb-0">{{ number_format($failedCount) }}</div>
            <span class="small text-muted">Requires follow-up</span>
        </div>
    </div>
</div>

<!-- Financial & COD Reconciliation Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-sack-dollar text-primary me-2"></i> Revenue & Settlement
            </h6>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-secondary">Shipping Freight:</span>
                <strong class="text-dark">Rp {{ number_format($totalRevenue) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-secondary">COD Remitted (Delivered):</span>
                <strong class="text-success">Rp {{ number_format($totalCodCollected) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-secondary">COD Outstanding:</span>
                <strong class="text-warning">Rp {{ number_format($totalCodPending) }}</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">Total Managed Value:</span>
                <strong class="text-primary fs-5">Rp {{ number_format($totalRevenue + $totalCodCollected) }}</strong>
            </div>
        </div>
    </div>

    <!-- Courier COD Balances -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-hand-holding-dollar text-warning me-2"></i> Courier Cash Collection Reconciliation
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary small text-uppercase">
                            <th class="ps-3">Courier Name</th>
                            <th>Vehicle</th>
                            <th>Assigned COD Parcels</th>
                            <th class="text-end pe-3">Total COD Cash Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courierCod as $cc)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark">{{ $cc->courier?->name ?? 'Unassigned' }}</div>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ $cc->courier?->phone ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ ucfirst($cc->courier?->vehicle_type ?? 'Motorcycle') }} &bull; {{ $cc->courier?->vehicle_plate ?? 'No Plate' }}
                                    </span>
                                </td>
                                <td><span class="fw-bold">{{ $cc->count }}</span> packages</td>
                                <td class="text-end pe-3 fw-bold text-warning fs-6">
                                    Rp {{ number_format($cc->total_cod) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No COD transactions assigned to couriers in this timeframe.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Branch Node Throughput -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom p-3">
        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-network-wired text-primary me-2"></i> Branch Node Throughput</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th class="ps-3">Branch Code</th>
                    <th>Branch / Hub Name</th>
                    <th>City / Province</th>
                    <th>Outbound (Origin)</th>
                    <th>Inbound (Destination)</th>
                    <th class="text-end pe-3">Activity Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchStats as $bs)
                    <tr>
                        <td class="ps-3 fw-bold text-primary">{{ $bs->code }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $bs->name }}</div>
                            <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ $bs->type_label }}</span>
                        </td>
                        <td>{{ $bs->city }}, {{ $bs->province }}</td>
                        <td><span class="fw-bold">{{ $bs->origin_shipments_count }}</span> pkgs</td>
                        <td><span class="fw-bold">{{ $bs->destination_shipments_count }}</span> pkgs</td>
                        <td class="text-end pe-3">
                            <span class="badge bg-success">Operational</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
