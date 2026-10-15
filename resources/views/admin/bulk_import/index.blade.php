@extends('layouts.admin')

@section('title', 'Bulk Data Import Center')
@section('page_title', 'Bulk Data Import Engine')

@section('breadcrumbs')
<span class="text-secondary small">/ High-Throughput CSV Import & Batch Scanning</span>
@endsection

@section('content')
<!-- Import Error Alerts if any -->
@if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="alert alert-danger shadow-sm rounded-4 mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-triangle-exclamation fs-4"></i>
            <h6 class="fw-bold mb-0">Import Processing Issues Encountered:</h6>
        </div>
        <p class="small mb-2">The following rows failed validation and were skipped to protect data integrity:</p>
        <div class="bg-white p-3 rounded-3 border overflow-auto" style="max-height: 200px;">
            <table class="table table-sm table-bordered mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Line #</th>
                        <th>Error Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(session('import_errors') as $err)
                        <tr>
                            <td class="fw-bold text-danger text-center">{{ $err['line'] ?? 'N/A' }}</td>
                            <td>{{ $err['error'] ?? 'Unknown error' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Tab Navigation Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs nav-fill card-header-tabs m-0" id="importTabs">
            <li class="nav-item">
                <button class="nav-link active py-3 fw-bold" data-bs-toggle="tab" data-bs-target="#tabShipments">
                    <i class="fa-solid fa-boxes-packing text-primary me-2"></i> 1. Bulk Shipments Booking (CSV)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3 fw-bold" data-bs-toggle="tab" data-bs-target="#tabStatus">
                    <i class="fa-solid fa-barcode text-success me-2"></i> 2. Bulk Status / Scanning Import
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3 fw-bold" data-bs-toggle="tab" data-bs-target="#tabTariffs">
                    <i class="fa-solid fa-calculator text-warning me-2"></i> 3. Bulk Tariffs Matrix (CSV)
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content">
            <!-- TAB 1: Bulk Shipments Booking -->
            <div class="tab-pane fade show active" id="tabShipments">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="p-3 bg-light rounded-4 border mb-4">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> Upload Shipments CSV File</h6>
                            <p class="text-secondary small mb-3">
                                Import hundreds of client orders at once. The engine will automatically compute volumetric weight, lookup matching tariff rates, generate sequential AWB barcodes, and log the initial manifest event.
                            </p>

                            <form action="{{ route('admin.bulk-import.shipments') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Select CSV File</label>
                                    <input type="file" name="csv_file" class="form-control form-control-lg" accept=".csv,text/csv" required>
                                    <div class="form-text small">Max file size: 10MB (approx. 20,000 shipments per batch).</div>
                                </div>

                                <button type="submit" class="btn btn-primary-custom fw-bold px-4 py-2 shadow-sm">
                                    <i class="fa-solid fa-file-import me-1"></i> Start Bulk Processing
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card border rounded-4 shadow-sm p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-file-lines text-success me-2"></i> CSV Template</h6>
                                <a href="{{ route('admin.bulk-import.template', 'shipments') }}" class="btn btn-outline-success btn-sm fw-bold">
                                    <i class="fa-solid fa-download me-1"></i> Download Sample CSV
                                </a>
                            </div>

                            <p class="text-secondary small mb-2">
                                Please format your CSV with the standard columns shown below:
                            </p>
                            
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item px-0 py-1"><code>sender_name, sender_phone, sender_city</code></li>
                                <li class="list-group-item px-0 py-1"><code>recipient_name, recipient_phone, recipient_city</code></li>
                                <li class="list-group-item px-0 py-1"><code>actual_weight_kg, length_cm, width_cm, height_cm</code></li>
                                <li class="list-group-item px-0 py-1"><code>service_code</code> (REG, EXP, CARGO, SAMEDAY)</li>
                                <li class="list-group-item px-0 py-1"><code>cod_amount, declared_value, payment_method</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Bulk Status Update / Scanning Import -->
            <div class="tab-pane fade" id="tabStatus">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="p-3 bg-light rounded-4 border mb-4">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-barcode text-success me-2"></i> Batch Status Milestone Scan</h6>
                            <p class="text-secondary small mb-3">
                                Update tracking status for batches of shipments arriving at your sorting hub or out for courier delivery.
                            </p>

                            <form action="{{ route('admin.bulk-import.status') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary text-uppercase">Target Milestone Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="received_at_origin_hub">Received at Sorting Hub</option>
                                            <option value="manifested_in_transit">Manifested In Transit (Truck/Flight)</option>
                                            <option value="received_at_transit_hub">Received at Transit Hub</option>
                                            <option value="received_at_dest_branch">Received at Destination DC</option>
                                            <option value="out_for_delivery">Out for Delivery with Courier</option>
                                            <option value="delivered">Delivered (Success)</option>
                                            <option value="failed_attempt">Delivery Attempt Failed</option>
                                            <option value="returned_to_sender">Returned to Sender (RTS)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary text-uppercase">Scanning City / Branch</label>
                                        <input type="text" name="location_city" class="form-control" value="{{ auth()->user()->branch?->city ?? 'Jakarta' }}" placeholder="Location city..." required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Check-In Notes / Details</label>
                                    <input type="text" name="notes" class="form-control" placeholder="e.g. Inbound sorting container batch #12 verified." value="Batch inbound scan processed.">
                                </div>

                                <!-- Method A: Paste AWBs -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">
                                        Method A: Scan / Paste AWBs (One per line or comma-separated)
                                    </label>
                                    <textarea name="awb_text" class="form-control font-monospace" rows="4" placeholder="LP-202610-884912&#10;LP-202610-771234&#10;LP-202610-339811"></textarea>
                                </div>

                                <div class="text-center my-2 text-muted small fw-bold">&mdash; OR &mdash;</div>

                                <!-- Method B: Upload CSV -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">
                                        Method B: Upload Status CSV
                                    </label>
                                    <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv">
                                </div>

                                <button type="submit" class="btn btn-success fw-bold px-4 py-2 shadow-sm">
                                    <i class="fa-solid fa-check-double me-1"></i> Apply Batch Checkpoint Update
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card border rounded-4 shadow-sm p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-file-lines text-success me-2"></i> Status CSV Template</h6>
                                <a href="{{ route('admin.bulk-import.template', 'status') }}" class="btn btn-outline-success btn-sm fw-bold">
                                    <i class="fa-solid fa-download me-1"></i> Download Template
                                </a>
                            </div>

                            <p class="text-secondary small mb-2">
                                For automated barcode gun manifests, format with these fields:
                            </p>
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item px-0 py-1"><code>tracking_number</code> (Mandatory)</li>
                                <li class="list-group-item px-0 py-1"><code>status</code> (e.g. out_for_delivery, delivered)</li>
                                <li class="list-group-item px-0 py-1"><code>location_city, location_details</code></li>
                                <li class="list-group-item px-0 py-1"><code>description, manifest_code</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Bulk Tariffs Matrix -->
            <div class="tab-pane fade" id="tabTariffs">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="p-3 bg-light rounded-4 border mb-4">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-calculator text-warning me-2"></i> Upload Shipping Rates Matrix</h6>
                            <p class="text-secondary small mb-3">
                                Quickly seed or update hundreds of route combinations (Origin City &rarr; Destination City + Service Code). Existing routes will be updated and new routes inserted.
                            </p>

                            <form action="{{ route('admin.bulk-import.tariffs') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Select Tariff CSV File</label>
                                    <input type="file" name="csv_file" class="form-control form-control-lg" accept=".csv,text/csv" required>
                                </div>

                                <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2 shadow-sm">
                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Update Tariff Matrix
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card border rounded-4 shadow-sm p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-file-lines text-warning me-2"></i> Tariffs Template</h6>
                                <a href="{{ route('admin.bulk-import.template', 'tariffs') }}" class="btn btn-outline-warning text-dark btn-sm fw-bold">
                                    <i class="fa-solid fa-download me-1"></i> Download Template
                                </a>
                            </div>

                            <p class="text-secondary small mb-2">Required tariff columns:</p>
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item px-0 py-1"><code>origin_city, origin_province</code></li>
                                <li class="list-group-item px-0 py-1"><code>destination_city, destination_province</code></li>
                                <li class="list-group-item px-0 py-1"><code>service_code</code> (REG, EXP, CARGO, SAMEDAY, ECO)</li>
                                <li class="list-group-item px-0 py-1"><code>base_weight_kg, base_rate, per_kg_rate</code></li>
                                <li class="list-group-item px-0 py-1"><code>estimated_etd</code> (e.g. 1-2 Days)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Past Bulk Import Audit Log Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Import Audit History</h6>
        <span class="text-secondary small">Persistent tracking of all admin batch imports</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th class="ps-3">ID / Date</th>
                    <th>Import Type</th>
                    <th>Filename</th>
                    <th>Admin / Operator</th>
                    <th>Processed</th>
                    <th>Success</th>
                    <th>Failed</th>
                    <th class="text-end pe-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($imports as $imp)
                    <tr>
                        <td class="ps-3">
                            <span class="fw-bold">#{{ $imp->id }}</span>
                            <div class="text-muted small" style="font-size: 0.7rem;">{{ $imp->created_at->format('d M Y, H:i') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $imp->type_label }}</span>
                        </td>
                        <td>
                            <code class="text-dark">{{ $imp->original_filename }}</code>
                        </td>
                        <td>
                            <div class="small fw-bold">{{ $imp->user?->name ?? 'System' }}</div>
                        </td>
                        <td><span class="fw-bold">{{ $imp->total_rows }}</span> rows</td>
                        <td><span class="badge bg-success">{{ $imp->successful_rows }}</span></td>
                        <td>
                            @if($imp->failed_rows > 0)
                                <span class="badge bg-danger">{{ $imp->failed_rows }}</span>
                            @else
                                <span class="text-muted small">0</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <span class="badge {{ $imp->status_badge_class }}">{{ ucfirst($imp->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No bulk import history yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($imports->hasPages())
        <div class="p-3 border-top">
            {{ $imports->links() }}
        </div>
    @endif
</div>
@endsection
