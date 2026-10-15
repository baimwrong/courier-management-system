@extends('layouts.admin')

@section('title', 'Logistics Operations Dashboard')
@section('page_title', 'Operations Dashboard')

@section('breadcrumbs')
<span class="text-secondary small">/ Real-time Operations & Logistics Hub</span>
@endsection

@section('content')
<!-- Quick Action Buttons Row -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.shipments.create') }}" class="btn btn-primary-custom shadow-sm">
                <i class="fa-solid fa-plus-circle me-1"></i> Book New Shipment
            </a>
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-success shadow-sm">
                <i class="fa-solid fa-file-csv me-1"></i> Bulk Import Center (CSV)
            </a>
            <a href="{{ route('admin.manifests.create') }}" class="btn btn-warning text-dark shadow-sm fw-bold">
                <i class="fa-solid fa-truck-ramp-box me-1"></i> Create Manifest / Run-Sheet
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-chart-pie me-1"></i> Operational Reports
            </a>
        </div>
    </div>
</div>

<!-- Primary Metric KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase">Total Shipments</span>
                    <h3 class="fw-bolder mb-0 text-dark">{{ number_format($totalShipments) }}</h3>
                    <span class="badge bg-primary-subtle text-primary small mt-1">
                        <i class="fa-solid fa-calendar-day me-1"></i> Today: {{ $todayBookings }}
                    </span>
                </div>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase">Active In-Transit</span>
                    <h3 class="fw-bolder mb-0 text-primary">{{ number_format($inTransit) }}</h3>
                    <span class="badge bg-info-subtle text-info small mt-1">
                        <i class="fa-solid fa-truck-fast me-1"></i> Trunk Line Moving
                    </span>
                </div>
                <div class="kpi-icon bg-info bg-opacity-10 text-info">
                    <i class="fa-solid fa-truck-moving"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase">Out for Delivery</span>
                    <h3 class="fw-bolder mb-0 text-warning">{{ number_format($outForDelivery) }}</h3>
                    <span class="badge bg-warning-subtle text-warning small mt-1">
                        <i class="fa-solid fa-motorcycle me-1"></i> With Couriers
                    </span>
                </div>
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fa-solid fa-dolly"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase">Delivered (Success)</span>
                    <h3 class="fw-bolder mb-0 text-success">{{ number_format($deliveredCount) }}</h3>
                    <span class="badge bg-success-subtle text-success small mt-1">
                        <i class="fa-solid fa-circle-check me-1"></i> POD Confirmed
                    </span>
                </div>
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Financial KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase">Total Shipping Revenue</div>
                    <div class="h3 fw-bold text-dark mb-0">Rp {{ number_format($totalRevenue) }}</div>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="fa-solid fa-coins fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase">Pending COD Collection</div>
                    <div class="h3 fw-bold text-warning mb-0">Rp {{ number_format($pendingCodAmount) }}</div>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                    <i class="fa-solid fa-hand-holding-dollar fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row (Chart.js Offline) -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-line text-primary me-2"></i> 7-Day Shipment Volume Trend</h6>
                <span class="badge bg-light text-dark border">Last 7 Days</span>
            </div>
            <div style="height: 260px;">
                <canvas id="volumeChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-pie text-warning me-2"></i> Status Distribution</h6>
            <div style="height: 220px;" class="d-flex align-items-center justify-content-center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-2 text-center small text-secondary">
                Real-time snapshot across all active operational waybills
            </div>
        </div>
    </div>
</div>

<!-- Recent Shipments & Top Corridors -->
<div class="row g-4">
    <!-- Recent Shipments -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Recent Shipments</h6>
                <a href="{{ route('admin.shipments.index') }}" class="btn btn-outline-primary btn-sm fw-bold">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary small text-uppercase">
                            <th class="ps-3">AWB</th>
                            <th>Route</th>
                            <th>Service</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentShipments as $rs)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('admin.shipments.show', $rs->id) }}" class="fw-bold text-primary text-decoration-none">
                                        {{ $rs->tracking_number }}
                                    </a>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ $rs->booking_date->format('d M H:i') }}</div>
                                </td>
                                <td>
                                    <div class="small fw-bold">{{ $rs->sender_city }} <i class="fa-solid fa-arrow-right text-muted mx-1"></i> {{ $rs->recipient_city }}</div>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ $rs->chargeable_weight_kg }} Kg</div>
                                </td>
                                <td>
                                    <span class="badge {{ $rs->service->badge_class }}">{{ $rs->service->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold small">Rp {{ number_format($rs->shipping_cost) }}</div>
                                    @if($rs->isCod())
                                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">COD Rp {{ number_format($rs->cod_amount) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $rs->status_badge_class }} small">{{ $rs->status_label }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.shipments.show', $rs->id) }}" class="btn btn-outline-secondary" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.shipments.print', $rs->id) }}" target="_blank" class="btn btn-outline-dark" title="Print 4x6 Label">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No shipments booked yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Corridors & Services -->
    <div class="col-lg-4">
        <!-- Top Corridors -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-route text-success me-2"></i> Top Volume Corridors</h6>
            <div class="d-flex flex-column gap-2">
                @forelse($topRoutes as $tr)
                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light small">
                        <div>
                            <strong>{{ $tr->sender_city }}</strong> <i class="fa-solid fa-arrow-right text-muted mx-1"></i> <strong>{{ $tr->recipient_city }}</strong>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $tr->total }} pkgs</span>
                    </div>
                @empty
                    <div class="text-muted small">No corridor data yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Service Products Volume -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-box text-primary me-2"></i> Product Breakdown</h6>
            <div class="d-flex flex-column gap-2">
                @foreach($services as $srv)
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="badge {{ $srv->badge_class }} px-2 py-1">{{ $srv->code }} - {{ $srv->name }}</span>
                        <span class="fw-bold">{{ $srv->shipments_count }} pkgs</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (typeof Chart === 'undefined') return;

    // 1. Volume Trend Chart
    const ctxTrend = document.getElementById('volumeChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendDates) !!},
            datasets: [{
                label: 'Shipment Volume',
                data: {!! json_encode($trendCounts) !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#2563eb',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Status Doughnut Chart
    const statusData = {!! json_encode($statusCounts) !!};
    const labels = Object.keys(statusData).map(s => s.replace(/_/g, ' ').toUpperCase());
    const dataValues = Object.values(statusData);

    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: labels.length > 0 ? labels : ['No Data'],
            datasets: [{
                data: dataValues.length > 0 ? dataValues : [1],
                backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#6366f1', '#ef4444', '#64748b'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
            }
        }
    });
});
</script>
@endpush
