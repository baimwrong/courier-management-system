@extends('layouts.admin')

@section('title', 'Create Manifest / Run-Sheet')
@section('page_title', 'Create Manifest / Courier Run-Sheet')

@section('breadcrumbs')
<span class="text-secondary small">/ Staging Batch Dispatch</span>
@endsection

@section('content')
<form action="{{ route('admin.manifests.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <!-- Configuration Column -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="fa-solid fa-sliders text-primary me-2"></i> Manifest Parameters
                </h6>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase">Manifest Type *</label>
                    <select name="type" class="form-select" required>
                        <option value="delivery_run_sheet">Courier Delivery Run-Sheet (Last-Mile)</option>
                        <option value="inter_hub_transit">Inter-Hub Line-Haul Transit (Cargo Truck)</option>
                        <option value="pickup">First-Mile Pickup Run-Sheet</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase">Origin Hub / Branch *</label>
                    <select name="origin_branch_id" class="form-select" required>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ auth()->user()->branch_id == $b->id ? 'selected' : '' }}>
                                {{ $b->code }} - {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase">Destination Hub (For Transit)</label>
                    <select name="destination_branch_id" class="form-select">
                        <option value="">-- Local Delivery Area --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->code }} - {{ $b->name }} ({{ $b->city }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase">Assigned Courier / Driver</label>
                    <select name="courier_id" class="form-select">
                        <option value="">-- Select Courier / Driver --</option>
                        @foreach($couriers as $cr)
                            <option value="{{ $cr->id }}">
                                {{ $cr->name }} ({{ $cr->role_label }} &bull; {{ $cr->vehicle_type ?? 'Motorcycle' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Vehicle Type</label>
                        <select name="vehicle_type" class="form-select">
                            <option value="motorcycle">Motorcycle</option>
                            <option value="van">Delivery Van</option>
                            <option value="box_truck">Box Truck</option>
                            <option value="wingbox">Heavy Wingbox Truck</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Vehicle Plate #</label>
                        <input type="text" name="vehicle_plate" class="form-control" placeholder="e.g. B 9876 XYZ">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase">Manifest Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Morning last mile delivery route #4"></textarea>
                </div>

                <button type="submit" class="btn btn-warning text-dark w-100 fw-bold py-2 shadow-sm">
                    <i class="fa-solid fa-truck-ramp-box me-1"></i> Generate & Seal Manifest
                </button>
            </div>
        </div>

        <!-- Available Shipments Selection Column -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-boxes-packing text-primary me-2"></i> Select Shipments to Load</h6>
                        <span class="text-secondary small">Check shipments or scan AWBs to include in this dispatch batch</span>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSelectAll">Select All</button>
                </div>

                <!-- Continuous Barcode Gun Staging Input -->
                <div class="p-3 bg-light border-bottom">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-barcode text-primary"></i></span>
                        <input type="text" id="barcodeGunInput" class="form-control" placeholder="Scan AWB with barcode gun or type tracking number..." autofocus autocomplete="off">
                        <button class="btn btn-outline-primary fw-bold" type="button" id="btnScanAdd">Stage AWB</button>
                    </div>
                    <div id="scanFeedback" class="small mt-1 text-muted">Ready for barcode scanner input (Code128).</div>
                </div>

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="manifestShipmentsTable">
                        <thead class="table-light sticky-top">
                            <tr class="text-secondary small text-uppercase">
                                <th style="width: 40px;" class="ps-3"></th>
                                <th>AWB</th>
                                <th>Route / Dest</th>
                                <th>Weight</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($availableShipments as $as)
                                <tr data-awb="{{ strtoupper(trim($as->tracking_number)) }}" id="row-shipment-{{ $as->id }}">
                                    <td class="ps-3">
                                        <input type="checkbox" name="shipment_ids[]" value="{{ $as->id }}" class="form-check-input shipment-check" checked>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $as->tracking_number }}</div>
                                        <div class="text-muted small" style="font-size: 0.7rem;">{{ $as->recipient_name }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold">{{ $as->recipient_city }}</div>
                                        <div class="text-muted small" style="font-size: 0.7rem;">Code: {{ $as->routing_code }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $as->chargeable_weight_kg }} Kg</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $as->status_badge_class }}" style="font-size: 0.65rem;">
                                            {{ $as->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No pending shipments ready for manifest dispatch.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnAll = document.getElementById('btnSelectAll');
    if (btnAll) {
        let allChecked = true;
        btnAll.addEventListener('click', function() {
            allChecked = !allChecked;
            document.querySelectorAll('.shipment-check').forEach(chk => chk.checked = allChecked);
            btnAll.textContent = allChecked ? 'Deselect All' : 'Select All';
        });
    }

    // Barcode Scanner Gun Buffer Handler
    const gunInput = document.getElementById('barcodeGunInput');
    const scanBtn = document.getElementById('btnScanAdd');
    const feedback = document.getElementById('scanFeedback');

    function handleScan() {
        if (!gunInput) return;
        const scannedCode = gunInput.value.trim().toUpperCase();
        if (!scannedCode) return;

        const row = document.querySelector(`tr[data-awb="${scannedCode}"]`);
        if (row) {
            const checkbox = row.querySelector('.shipment-check');
            if (checkbox) {
                checkbox.checked = true;
            }
            row.classList.add('table-success');
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            feedback.innerHTML = `<span class="text-success fw-bold"><i class="fa-solid fa-check-circle me-1"></i> Staged Waybill ${scannedCode}</span>`;
            setTimeout(() => row.classList.remove('table-success'), 2000);
        } else {
            feedback.innerHTML = `<span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Waybill ${scannedCode} not in pending list</span>`;
        }

        gunInput.value = '';
        gunInput.focus();
    }

    if (gunInput) {
        gunInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleScan();
            }
        });
    }
    if (scanBtn) {
        scanBtn.addEventListener('click', handleScan);
    }
});
</script>
@endpush
