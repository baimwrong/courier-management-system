@extends('layouts.app')

@section('title', 'Waybill Tracking | Courier & Logistics Management')

@section('content')
<div class="bg-primary text-white py-4 shadow-sm" style="background: linear-gradient(135deg, #0d47a1, #1e3a8a);">
    <div class="container">
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-magnifying-glass-location me-2"></i> Track Air Waybill (Resi)</h2>
        <p class="text-light text-opacity-75 mb-0">Real-time status updates and delivery milestones across our nationwide sorting network.</p>
    </div>
</div>

<div class="container my-5">
    <!-- Search Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('tracking.search') }}" method="POST">
                @csrf
                <label class="form-label fw-bold text-secondary text-uppercase small">Enter Tracking Numbers (Airway Bill / Resi)</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-barcode text-primary"></i></span>
                    <input type="text" name="awb" class="form-control border-start-0" placeholder="e.g. CR-202610-884912, CR-202610-771234" value="{{ $trackingQuery }}" required>
                    <button class="btn btn-primary-custom px-4 fw-bold" type="submit">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Track
                    </button>
                </div>
                <div class="form-text small mt-2">
                    Separate multiple tracking numbers using commas, spaces, or line breaks. Demo: <code>CR-202610-884912</code>, <code>CR-202610-771234</code>, <code>CR-202610-339811</code>.
                </div>
            </form>
        </div>
    </div>

    @if(!empty($notFoundAwbs))
        <div class="alert alert-warning shadow-sm rounded-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                <div>
                    <strong>Waybill Not Found:</strong> The tracking number(s) 
                    @foreach($notFoundAwbs as $nf)
                        <code class="bg-white px-2 py-1 rounded text-danger fw-bold">{{ $nf }}</code>{{ !$loop->last ? ',' : '' }}
                    @endforeach
                    could not be located in our database. Please double check the number.
                </div>
            </div>
        </div>
    @endif

    @forelse($shipments as $s)
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
            <!-- Header -->
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge {{ $s->status_badge_class }} fs-6 px-3 py-2 fw-bold text-uppercase">
                                <i class="fa-solid fa-circle-dot me-1"></i> {{ $s->status_label }}
                            </span>
                            <span class="badge {{ $s->service->badge_class }} fs-6 px-3 py-2">
                                {{ $s->service->name }} ({{ $s->service->code }})
                            </span>
                            @if($s->isCod())
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2 fw-bold">
                                    <i class="fa-solid fa-hand-holding-dollar me-1"></i> COD: Rp {{ number_format($s->cod_amount) }}
                                </span>
                            @endif
                        </div>
                        <h3 class="fw-bolder mb-0 text-primary mt-2">
                            {{ $s->tracking_number }}
                        </h3>
                    </div>

                    <div class="text-md-end">
                        <svg class="barcode-render" jsbarcode-value="{{ $s->tracking_number }}" jsbarcode-height="40" jsbarcode-width="1.8" jsbarcode-fontSize="12"></svg>
                        <div class="small text-secondary mt-1">Booked: {{ $s->booking_date->format('d M Y, H:i') }} WIB</div>
                    </div>
                </div>
            </div>

            <!-- Summary Details Strip -->
            <div class="bg-light p-4 border-bottom">
                <div class="row g-3 text-center text-md-start">
                    <div class="col-md-3">
                        <span class="text-secondary small text-uppercase fw-bold">Origin</span>
                        <div class="fw-bold fs-6 text-dark">{{ $s->sender_city }}</div>
                        <div class="small text-muted">{{ $s->originBranch?->name ?? 'Origin Hub' }}</div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-secondary small text-uppercase fw-bold">Destination</span>
                        <div class="fw-bold fs-6 text-dark">{{ $s->recipient_city }}</div>
                        <div class="small text-muted">{{ $s->public_recipient_address }}</div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-secondary small text-uppercase fw-bold">Package Specs</span>
                        <div class="fw-bold fs-6 text-dark">{{ $s->chargeable_weight_kg }} Kg ({{ $s->piece_count }} Pcs)</div>
                        <div class="small text-muted">{{ ucfirst($s->package_type) }}: {{ $s->package_description ?? 'General Goods' }}</div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-secondary small text-uppercase fw-bold">Estimated Arrival</span>
                        <div class="fw-bold fs-6 text-success">{{ $s->service->etd_range }}</div>
                        <div class="small text-muted">Routing: <span class="badge bg-secondary">{{ $s->routing_code }}</span></div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Tracking Timeline -->
                    <div class="col-lg-8">
                        <h5 class="fw-bold text-dark mb-4"><i class="fa-solid fa-timeline me-2 text-primary"></i> Tracking History & Milestones</h5>
                        
                        <div class="tracking-timeline">
                            @foreach($s->trackings as $index => $t)
                                <div class="timeline-item {{ $index === 0 ? 'active' : '' }} {{ $t->status === 'delivered' ? 'delivered' : '' }}">
                                    <div class="timeline-marker">
                                        <i class="fa-solid {{ $t->icon }}"></i>
                                    </div>
                                    <div class="bg-white p-3 rounded-3 border shadow-sm">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-1">
                                            <h6 class="fw-bold mb-0 {{ $index === 0 ? 'text-primary' : 'text-dark' }}">
                                                {{ $t->title }}
                                            </h6>
                                            <span class="small text-muted fw-bold">
                                                <i class="fa-regular fa-clock me-1"></i> {{ $t->occurred_at->format('d M Y, H:i') }} WIB
                                            </span>
                                        </div>
                                        @if($t->location_city || $t->location_details)
                                            <div class="small text-secondary mb-1">
                                                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                                {{ $t->location_details ? $t->location_details . ', ' : '' }}{{ $t->location_city }}
                                            </div>
                                        @endif
                                        @if($t->description)
                                            <p class="small text-muted mb-0">{{ $t->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Side Card (POD & Shipper/Consignee Info) -->
                    <div class="col-lg-4">
                        @if($s->isDelivered())
                            <div class="card border-success border-2 rounded-4 mb-4 bg-success bg-opacity-10 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-success">
                                        <i class="fa-solid fa-circle-check fs-3"></i>
                                        <h6 class="fw-bold mb-0">Proof of Delivery (POD)</h6>
                                    </div>
                                    <div class="small">
                                        <div class="mb-1"><strong>Received By:</strong> {{ $s->pod_recipient_name }}</div>
                                        <div class="mb-1"><strong>Relationship:</strong> {{ $s->pod_relationship ?? 'Self' }}</div>
                                        <div class="mb-1"><strong>Date & Time:</strong> {{ $s->pod_delivered_at?->format('d M Y, H:i') }} WIB</div>
                                        @if($s->pod_notes)
                                            <div class="text-muted fst-italic">"{{ $s->pod_notes }}"</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="card border rounded-4 shadow-sm p-3 mb-3">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <i class="fa-solid fa-user-tag me-1 text-primary"></i> Parties Involved
                            </h6>
                            <div class="mb-3 small">
                                <span class="text-secondary fw-bold text-uppercase" style="font-size: 0.7rem;">Shipper / Pengirim</span>
                                <div class="fw-bold text-dark">{{ $s->masked_sender_name }}</div>
                                <div class="text-muted">{{ $s->sender_city }} ({{ $s->masked_sender_phone }})</div>
                            </div>
                            <div class="small">
                                <span class="text-secondary fw-bold text-uppercase" style="font-size: 0.7rem;">Consignee / Penerima</span>
                                <div class="fw-bold text-dark">{{ $s->masked_recipient_name }}</div>
                                <div class="text-muted">{{ $s->recipient_city }} ({{ $s->masked_recipient_phone }})</div>
                            </div>
                        </div>

                        <div class="card border rounded-4 shadow-sm p-3 bg-light">
                            <div class="d-flex align-items-center gap-2 small text-secondary">
                                <i class="fa-solid fa-headset text-primary fs-4"></i>
                                <div>
                                    <div class="fw-bold text-dark">Need assistance?</div>
                                    <div>Contact Support at 1500-888 with AWB: <strong>{{ $s->tracking_number }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        @if(empty($notFoundAwbs))
            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                <div class="card-body">
                    <i class="fa-solid fa-magnifying-glass-location text-muted display-4 mb-3"></i>
                    <h4 class="fw-bold text-secondary">No Tracking Number Entered</h4>
                    <p class="text-muted">Enter your shipment AWB / Resi in the search box above to track its current progress.</p>
                </div>
            </div>
        @endif
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof JsBarcode !== 'undefined') {
            JsBarcode(".barcode-render").init();
        }
    });
</script>
@endpush
