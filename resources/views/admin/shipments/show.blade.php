@extends('layouts.admin')

@section('title', 'Shipment ' . $shipment->tracking_number)
@section('page_title', 'Waybill Details: ' . $shipment->tracking_number)

@section('breadcrumbs')
<span class="text-secondary small">/ AWB Inspection & Tracking Audit</span>
@endsection

@section('content')
<!-- Header Card with Barcode & Primary Actions -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge {{ $shipment->status_badge_class }} fs-6 px-3 py-2 fw-bold text-uppercase">
                        <i class="fa-solid fa-circle-dot me-1"></i> {{ $shipment->status_label }}
                    </span>
                    <span class="badge {{ $shipment->service->badge_class }} fs-6 px-3 py-2">
                        {{ $shipment->service->name }} ({{ $shipment->service->code }})
                    </span>
                    <span class="badge bg-dark fs-6 px-3 py-2">
                        <i class="fa-solid fa-route me-1 text-warning"></i> Route: {{ $shipment->routing_code }}
                    </span>
                    @if($shipment->isCod())
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2 fw-bold">
                            <i class="fa-solid fa-hand-holding-dollar me-1"></i> COD Rp {{ number_format($shipment->cod_amount) }}
                        </span>
                    @endif
                </div>

                <h2 class="fw-bolder text-primary mb-1">{{ $shipment->tracking_number }}</h2>
                <div class="text-secondary small">
                    Booked on {{ $shipment->booking_date->format('d M Y, H:i') }} WIB &bull; Created by {{ $shipment->creator?->name ?? 'Staff Counter' }}
                </div>
            </div>

            <!-- Barcode & Print Action -->
            <div class="d-flex flex-column align-items-lg-end gap-2">
                <svg class="barcode-render" jsbarcode-value="{{ $shipment->tracking_number }}" jsbarcode-height="45" jsbarcode-width="1.8" jsbarcode-fontSize="12"></svg>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a href="{{ route('admin.shipments.print', $shipment->id) }}" target="_blank" class="btn btn-outline-dark fw-bold btn-sm shadow-sm">
                        <i class="fa-solid fa-print me-1"></i> Print 4x6 Label
                    </a>
                    <button type="button" class="btn btn-primary-custom fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalStatusUpdate">
                        <i class="fa-solid fa-barcode me-1"></i> Log Milestone Scan
                    </button>
                    @if(!$shipment->isDelivered())
                        <button type="button" class="btn btn-warning text-dark fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAssignCourier">
                            <i class="fa-solid fa-motorcycle me-1"></i> Assign Courier
                        </button>
                        <button type="button" class="btn btn-success fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRecordPod">
                            <i class="fa-solid fa-signature me-1"></i> Record POD
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Specs, Parties, Financials -->
    <div class="col-lg-5">
        <!-- Shipper & Consignee -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-people-carry-box text-primary me-2"></i> Shipping Parties</h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3 pb-3 border-bottom">
                    <span class="text-secondary small fw-bold text-uppercase" style="font-size: 0.7rem;">Shipper / Pengirim</span>
                    <div class="fw-bold fs-6 text-dark">{{ $shipment->sender_name }}</div>
                    <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i> {{ $shipment->sender_phone }}</div>
                    <div class="small text-secondary mt-1"><i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $shipment->sender_address }}, {{ $shipment->sender_city }}</div>
                </div>

                <div>
                    <span class="text-secondary small fw-bold text-uppercase" style="font-size: 0.7rem;">Consignee / Penerima</span>
                    <div class="fw-bold fs-6 text-dark">{{ $shipment->recipient_name }}</div>
                    <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i> {{ $shipment->recipient_phone }}</div>
                    <div class="small text-secondary mt-1"><i class="fa-solid fa-location-dot text-success me-1"></i> {{ $shipment->recipient_address }}, {{ $shipment->recipient_city }}</div>
                </div>
            </div>
        </div>

        <!-- Package Specs -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-box-open text-warning me-2"></i> Package Specifications</h6>
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Type & Item:</span>
                    <strong class="text-dark">{{ ucfirst($shipment->package_type) }} ({{ $shipment->package_description ?? 'General' }})</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Pieces Count:</span>
                    <strong>{{ $shipment->piece_count }} Piece(s)</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Actual Weight:</span>
                    <strong>{{ $shipment->actual_weight_kg }} Kg</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Dimensions (L x W x H):</span>
                    <strong>{{ $shipment->length_cm }} x {{ $shipment->width_cm }} x {{ $shipment->height_cm }} cm</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Volumetric Weight:</span>
                    <strong>{{ $shipment->volumetric_weight_kg }} Kg</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-dark fw-bold">Charged Weight:</span>
                    <strong class="text-primary fs-6">{{ $shipment->chargeable_weight_kg }} Kg</strong>
                </div>
            </div>
        </div>

        <!-- Financials & COD -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-receipt text-success me-2"></i> Financial & Payment Ledger</h6>
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Shipping Freight Rate:</span>
                    <strong>Rp {{ number_format($shipment->shipping_cost) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Insurance Surcharge:</span>
                    <strong>Rp {{ number_format($shipment->insurance_cost) }}</strong>
                </div>
                @if($shipment->isCod())
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">COD Remittance Value:</span>
                        <strong class="text-warning">Rp {{ number_format($shipment->cod_amount) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">COD Handling Fee:</span>
                        <strong>Rp {{ number_format($shipment->cod_fee) }}</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Payment Method:</span>
                    <strong class="text-uppercase">{{ $shipment->payment_method }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Payment Status:</span>
                    <span class="badge {{ $shipment->payment_badge_class }}">{{ strtoupper($shipment->payment_status) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-dark fw-bold">Total Bill:</span>
                    <strong class="text-primary fs-5">Rp {{ number_format($shipment->total_cost) }}</strong>
                </div>
            </div>
        </div>

        <!-- Proof of Delivery Card (If Delivered) -->
        @if($shipment->isDelivered())
            <div class="card border-success border-2 rounded-4 shadow-sm bg-success bg-opacity-10 p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 text-success">
                    <i class="fa-solid fa-circle-check fs-2"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Delivered Successfully (POD)</h6>
                        <span class="small">Proof of Delivery Verified</span>
                    </div>
                </div>
                <div class="small">
                    <div class="mb-1"><strong>Received By:</strong> {{ $shipment->pod_recipient_name }}</div>
                    <div class="mb-1"><strong>Relationship:</strong> {{ $shipment->pod_relationship ?? 'Self' }}</div>
                    <div class="mb-1"><strong>Delivered At:</strong> {{ $shipment->pod_delivered_at?->format('d M Y, H:i') }} WIB</div>
                    @if($shipment->pod_notes)
                        <div class="text-muted mt-2"><strong>Notes:</strong> "{{ $shipment->pod_notes }}"</div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Interactive Audit Timeline -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-timeline text-primary me-2"></i> Tracking Checkpoints & Audit Log</h5>
                <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalStatusUpdate">
                    <i class="fa-solid fa-plus me-1"></i> Add Checkpoint
                </button>
            </div>

            <div class="tracking-timeline">
                @foreach($shipment->trackings as $index => $t)
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
                                <p class="small text-muted mb-2">{{ $t->description }}</p>
                            @endif

                            <div class="d-flex flex-wrap gap-2 text-muted small" style="font-size: 0.72rem;">
                                @if($t->user)
                                    <span><i class="fa-solid fa-user me-1"></i> Operator: {{ $t->user->name }}</span>
                                @endif
                                @if($t->branch)
                                    <span><i class="fa-solid fa-warehouse me-1"></i> Branch: {{ $t->branch->name }}</span>
                                @endif
                                @if($t->manifest_code)
                                    <span class="badge bg-secondary">Manifest: {{ $t->manifest_code }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Status Milestone Scan Update -->
<div class="modal fade" id="modalStatusUpdate" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.shipments.update_status', $shipment->id) }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-barcode text-primary me-2"></i> Log Checkpoint Scan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Milestone Status</label>
                        <select name="status" class="form-select" required>
                            <option value="received_at_origin_hub">Received at Origin Hub</option>
                            <option value="manifested_in_transit">In Transit (Dispatched to Destination)</option>
                            <option value="received_at_transit_hub">Received at Transit Hub</option>
                            <option value="received_at_dest_branch">Received at Destination DC</option>
                            <option value="out_for_delivery">Out for Delivery with Courier</option>
                            <option value="failed_attempt">Delivery Attempt Failed</option>
                            <option value="returned_to_sender">Returned to Sender</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Milestone Headline / Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Scanned at Sorting Gateway" value="Inbound Hub Scan" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Location City</label>
                        <input type="text" name="location_city" class="form-control" value="{{ auth()->user()->branch?->city ?? 'Jakarta' }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Location Details</label>
                        <input type="text" name="location_details" class="form-control" placeholder="e.g. Sorting Gate 4, Truck Dock #2" value="{{ auth()->user()->branch?->name }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Description / Log Remarks</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Container seal verified, parcel intact.">Package scanned and verified at distribution node.</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom fw-bold">Save Checkpoint Event</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Assign Courier -->
<div class="modal fade" id="modalAssignCourier" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.shipments.assign_courier', $shipment->id) }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-motorcycle text-warning me-2"></i> Assign Delivery Courier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Select Courier</label>
                        <select name="courier_id" class="form-select" required>
                            <option value="">-- Choose Assigned Courier --</option>
                            @foreach($couriers as $cr)
                                <option value="{{ $cr->id }}" {{ $shipment->assigned_courier_id == $cr->id ? 'selected' : '' }}>
                                    {{ $cr->name }} ({{ $cr->vehicle_type ?? 'Motorcycle' }} &bull; {{ $cr->vehicle_plate ?? 'No Plate' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="small text-secondary mb-0">
                        Assigning courier will automatically set the package status to <strong>Out for Delivery</strong> and log a milestone with courier vehicle info.
                    </p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Confirm Courier Assignment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Record Proof of Delivery (POD) -->
<div class="modal fade" id="modalRecordPod" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.shipments.pod', $shipment->id) }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-signature text-success me-2"></i> Record Proof of Delivery (POD)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Recipient Name *</label>
                        <input type="text" name="pod_recipient_name" class="form-control" value="{{ $shipment->recipient_name }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Relationship with Consignee *</label>
                        <select name="pod_relationship" class="form-select" required>
                            <option value="Self">Self (Direct Consignee)</option>
                            <option value="Family Member">Family Member</option>
                            <option value="Security / Guard">Building Security / Guard</option>
                            <option value="Receptionist">Office Receptionist</option>
                            <option value="Neighbor">Neighbor</option>
                            <option value="Household Assistant">Household Assistant</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Delivery Confirmation Notes</label>
                        <textarea name="pod_notes" class="form-control" rows="2" placeholder="Delivered at front porch in pristine condition.">Received directly in good condition.</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Confirm Delivery & Close Waybill</button>
                </div>
            </div>
        </form>
    </div>
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
