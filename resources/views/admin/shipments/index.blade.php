@extends('layouts.admin')

@section('title', 'All Shipments & Waybills')
@section('page_title', 'Shipments & Waybill Management')

@section('breadcrumbs')
<span class="text-secondary small">/ National Waybill Registry</span>
@endsection

@section('content')
<!-- Filter & Search Toolbar Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.shipments.index') }}" method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="AWB, Name, Phone, Route..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="booking_created" {{ request('status') == 'booking_created' ? 'selected' : '' }}>Booking Created</option>
                        <option value="received_at_origin_hub" {{ request('status') == 'received_at_origin_hub' ? 'selected' : '' }}>Origin Hub</option>
                        <option value="manifested_in_transit" {{ request('status') == 'manifested_in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="received_at_dest_branch" {{ request('status') == 'received_at_dest_branch' ? 'selected' : '' }}>Dest. Branch</option>
                        <option value="out_for_delivery" {{ request('status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed_attempt" {{ request('status') == 'failed_attempt' ? 'selected' : '' }}>Failed Attempt</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <select name="service_id" class="form-select">
                        <option value="">All Services</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}" {{ request('service_id') == $srv->id ? 'selected' : '' }}>{{ $srv->code }} - {{ $srv->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-6">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Date From">
                </div>

                <div class="col-lg-2 col-md-4 col-6">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="Date To">
                </div>

                <div class="col-lg-1 col-md-4 col-12">
                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Shipments Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold text-dark">Waybill Registry</span>
            <span class="badge bg-secondary ms-2">{{ $shipments->total() }} Total</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.shipments.create') }}" class="btn btn-primary-custom btn-sm fw-bold">
                <i class="fa-solid fa-plus me-1"></i> Book Shipment
            </a>
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-success btn-sm fw-bold">
                <i class="fa-solid fa-file-import me-1"></i> Bulk CSV
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th class="ps-3">AWB / Date</th>
                    <th>Shipper (Sender)</th>
                    <th>Consignee (Recipient)</th>
                    <th>Corridor / Route</th>
                    <th>Service / Weight</th>
                    <th>Billing / COD</th>
                    <th>Current Status</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipments as $s)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('admin.shipments.show', $s->id) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $s->tracking_number }}
                            </a>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                {{ $s->booking_date->format('d M Y, H:i') }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold small">{{ $s->sender_name }}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">{{ $s->sender_city }} &bull; {{ $s->sender_phone }}</div>
                        </td>
                        <td>
                            <div class="fw-bold small">{{ $s->recipient_name }}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">{{ $s->recipient_city }} &bull; {{ $s->recipient_phone }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-bold small">
                                {{ $s->sender_city }} <i class="fa-solid fa-arrow-right text-muted mx-1"></i> {{ $s->recipient_city }}
                            </span>
                            <div class="text-muted small" style="font-size: 0.7rem;">Code: {{ $s->routing_code }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $s->service->badge_class }}">{{ $s->service->code }}</span>
                            <div class="small fw-bold text-dark mt-1">{{ $s->chargeable_weight_kg }} Kg</div>
                        </td>
                        <td>
                            <div class="fw-bold small text-dark">Rp {{ number_format($s->total_cost) }}</div>
                            @if($s->isCod())
                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">
                                    COD Rp {{ number_format($s->cod_amount) }}
                                </span>
                            @else
                                <span class="badge {{ $s->payment_badge_class }}" style="font-size: 0.65rem;">{{ strtoupper($s->payment_status) }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $s->status_badge_class }} small">{{ $s->status_label }}</span>
                            @if($s->courier)
                                <div class="text-muted small" style="font-size: 0.7rem;">
                                    <i class="fa-solid fa-motorcycle text-success me-1"></i> {{ $s->courier->name }}
                                </div>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.shipments.show', $s->id) }}" class="btn btn-outline-primary" title="View Full Details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.shipments.print', $s->id) }}" target="_blank" class="btn btn-outline-dark" title="Print 4x6 Label">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-box-open fs-2 mb-2 d-block"></i>
                            No shipments found matching criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-3 border-top bg-light">
        {{ $shipments->links() }}
    </div>
</div>
@endsection
