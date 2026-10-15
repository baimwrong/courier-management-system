@extends('layouts.admin')

@section('title', 'Book New Shipment')
@section('page_title', 'Create Airway Bill (AWB Booking)')

@section('breadcrumbs')
<span class="text-secondary small">/ New Booking Dispatch</span>
@endsection

@section('content')
<form action="{{ route('admin.shipments.store') }}" method="POST" id="shipmentForm">
    @csrf
    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <!-- 1. Origin & Shipper Details -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-user-arrow-up text-primary me-2"></i> 1. Shipper (Sender / Pengirim)</h6>
                    <span class="badge bg-primary-subtle text-primary small">Origin Details</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Sender Full Name *</label>
                            <input type="text" name="sender_name" class="form-control" placeholder="e.g. Toko Jaya Abadi" value="{{ old('sender_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Sender Contact Phone *</label>
                            <input type="text" name="sender_phone" class="form-control" placeholder="e.g. 081299887766" value="{{ old('sender_phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Origin City *</label>
                            <input type="text" name="sender_city" id="senderCity" class="form-control" placeholder="e.g. Jakarta" value="{{ old('sender_city', auth()->user()->branch?->city ?? 'Jakarta') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Origin Branch / Counter *</label>
                            <select name="origin_branch_id" class="form-select">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ old('origin_branch_id', $userBranchId) == $b->id ? 'selected' : '' }}>
                                        {{ $b->code }} - {{ $b->name }} ({{ $b->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Sender Complete Address *</label>
                            <textarea name="sender_address" class="form-control" rows="2" placeholder="Street, block, unit number..." required>{{ old('sender_address') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Sender Province</label>
                            <input type="text" name="sender_province" class="form-control" placeholder="e.g. DKI Jakarta" value="{{ old('sender_province') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Sender Postal Code</label>
                            <input type="text" name="sender_postal_code" class="form-control" placeholder="e.g. 10730" value="{{ old('sender_postal_code') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Consignee Details -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-user-arrow-down text-success me-2"></i> 2. Consignee (Recipient / Penerima)</h6>
                    <span class="badge bg-success-subtle text-success small">Destination Details</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Recipient Full Name *</label>
                            <input type="text" name="recipient_name" class="form-control" placeholder="e.g. Budi Santoso" value="{{ old('recipient_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Recipient Phone Number *</label>
                            <input type="text" name="recipient_phone" class="form-control" placeholder="e.g. 081912345678" value="{{ old('recipient_phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Destination City *</label>
                            <input type="text" name="recipient_city" id="recipientCity" class="form-control" placeholder="e.g. Bandung" value="{{ old('recipient_city') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Destination Province</label>
                            <input type="text" name="recipient_province" class="form-control" placeholder="e.g. Jawa Barat" value="{{ old('recipient_province') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Delivery Address *</label>
                            <textarea name="recipient_address" class="form-control" rows="2" placeholder="Full delivery address, street, landmark..." required>{{ old('recipient_address') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Destination Postal Code</label>
                            <input type="text" name="recipient_postal_code" class="form-control" placeholder="e.g. 40135" value="{{ old('recipient_postal_code') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Package & Dimensions -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-box text-warning me-2"></i> 3. Package Specs & Volumetric Dimensions</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Package Category</label>
                            <select name="package_type" class="form-select">
                                <option value="parcel">Parcel / General Goods</option>
                                <option value="document">Document / Letter</option>
                                <option value="electronics">Electronics / Gadget</option>
                                <option value="fragile">Fragile / Glassware</option>
                                <option value="clothing">Clothing / Apparel</option>
                                <option value="food">Dry Food / Snack</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Package Description</label>
                            <input type="text" name="package_description" class="form-control" placeholder="e.g. Keyboard & accessories" value="{{ old('package_description') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Piece Count</label>
                            <input type="number" name="piece_count" class="form-control" value="1" min="1">
                        </div>

                        <!-- Weights & Dimensions -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Actual Weight (Kg) *</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="actual_weight_kg" id="actualWeight" class="form-control calc-trigger" value="1.0" min="0.1" required>
                                <span class="input-group-text bg-light">KG</span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Length (cm) *</label>
                            <input type="number" step="1" name="length_cm" id="lengthCm" class="form-control calc-trigger" value="10" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Width (cm) *</label>
                            <input type="number" step="1" name="width_cm" id="widthCm" class="form-control calc-trigger" value="10" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Height (cm) *</label>
                            <input type="number" step="1" name="height_cm" id="heightCm" class="form-control calc-trigger" value="10" min="1" required>
                        </div>

                        <!-- Live Volumetric Display -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex flex-wrap justify-content-between align-items-center small">
                                <div>
                                    <span class="text-secondary">Volumetric Weight:</span>
                                    <strong id="displayVolumetric" class="text-primary fs-6 ms-1">0.20 Kg</strong>
                                    <span class="text-muted ms-2">(L*W*H / 5000)</span>
                                </div>
                                <div>
                                    <span class="text-secondary">Billable / Chargeable:</span>
                                    <strong id="displayChargeable" class="text-success fs-5 ms-1">1.00 Kg</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Summary & Service Options Column -->
        <div class="col-lg-4">
            <!-- Service Selection Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-truck-fast text-primary me-2"></i> Shipping Service Tier</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Select Service Tier *</label>
                        <select name="service_id" id="serviceSelect" class="form-select form-select-lg calc-trigger" required>
                            @foreach($services as $srv)
                                <option value="{{ $srv->id }}" {{ $srv->code === 'REG' ? 'selected' : '' }}>
                                    {{ $srv->code }} - {{ $srv->name }} ({{ $srv->etd_range }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    <!-- Extra Services (Insurance & COD) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Goods Declared Value (Insurance)</label>
                        <div class="input-group mb-1">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="declared_goods_value" id="declaredValue" class="form-control calc-trigger" placeholder="0" value="0">
                        </div>
                        <div class="form-text small" style="font-size: 0.72rem;">Premium coverage: 0.2% of value</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Cash on Delivery (COD) Amount</label>
                        <div class="input-group mb-1">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="cod_amount" id="codAmount" class="form-control calc-trigger" placeholder="0" value="0">
                        </div>
                        <div class="form-text small" style="font-size: 0.72rem;">COD handling fee: 2.5% of collection amount</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash Counter</option>
                            <option value="bank_transfer">Bank Transfer / QRIS</option>
                            <option value="cod">Cash On Delivery (COD)</option>
                            <option value="corporate_account">Corporate Credit Account</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Special Handling Notes</label>
                        <input type="text" name="special_instructions" class="form-control" placeholder="e.g. Fragile, call recipient">
                    </div>
                </div>
            </div>

            <!-- Billing Cost Breakdown Sticky Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 20px;">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="fa-solid fa-receipt text-primary me-2"></i> Cost Estimation Breakdown
                </h6>

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-secondary">Shipping Cost:</span>
                    <strong id="displayShippingCost">Rp 0</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-secondary">Insurance (0.2%):</span>
                    <strong id="displayInsuranceCost">Rp 0</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-secondary">COD Fee (2.5%):</span>
                    <strong id="displayCodFee">Rp 0</strong>
                </div>
                <div class="d-flex justify-content-between mb-3 small">
                    <span class="text-secondary">Estimated Arrival:</span>
                    <strong id="displayEtd" class="text-success">1-3 Days</strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-dark">TOTAL CHARGE:</span>
                    <div class="h3 fw-bolder text-primary mb-0" id="displayTotalCost">Rp 0</div>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 fw-bold py-3 shadow">
                    <i class="fa-solid fa-print me-1"></i> Confirm & Generate Airway Bill
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    function recalc() {
        const length = parseFloat(document.getElementById('lengthCm').value) || 10;
        const width = parseFloat(document.getElementById('widthCm').value) || 10;
        const height = parseFloat(document.getElementById('heightCm').value) || 10;
        const actual = parseFloat(document.getElementById('actualWeight').value) || 1;

        const vol = (length * width * height) / 5000;
        const chargeable = Math.max(actual, vol);

        document.getElementById('displayVolumetric').textContent = vol.toFixed(2) + ' Kg';
        document.getElementById('displayChargeable').textContent = chargeable.toFixed(2) + ' Kg';

        // AJAX tariff lookup if cities entered
        const orig = document.getElementById('senderCity').value.trim();
        const dest = document.getElementById('recipientCity').value.trim();
        const serviceId = document.getElementById('serviceSelect').value;
        const decVal = parseFloat(document.getElementById('declaredValue').value) || 0;
        const cod = parseFloat(document.getElementById('codAmount').value) || 0;

        if (orig && dest && serviceId) {
            const url = `{{ route('api.calculate_tariff') }}?origin_city=${encodeURIComponent(orig)}&destination_city=${encodeURIComponent(dest)}&service_id=${serviceId}&actual_weight_kg=${actual}&length_cm=${length}&width_cm=${width}&height_cm=${height}&declared_goods_value=${decVal}&cod_amount=${cod}`;
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.charges) {
                        const c = data.charges;
                        document.getElementById('displayShippingCost').textContent = 'Rp ' + Number(c.shipping_cost).toLocaleString();
                        document.getElementById('displayInsuranceCost').textContent = 'Rp ' + Number(c.insurance_cost).toLocaleString();
                        document.getElementById('displayCodFee').textContent = 'Rp ' + Number(c.cod_fee).toLocaleString();
                        document.getElementById('displayTotalCost').textContent = 'Rp ' + Number(c.total_cost).toLocaleString();
                        document.getElementById('displayEtd').textContent = c.estimated_etd;
                    }
                })
                .catch(err => console.log('Live tariff calculate error:', err));
        }
    }

    document.querySelectorAll('.calc-trigger').forEach(el => {
        el.addEventListener('input', recalc);
        el.addEventListener('change', recalc);
    });

    document.getElementById('senderCity').addEventListener('blur', recalc);
    document.getElementById('recipientCity').addEventListener('blur', recalc);

    recalc();
});
</script>
@endpush
