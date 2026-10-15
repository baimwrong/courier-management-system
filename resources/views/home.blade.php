@extends('layouts.app')

@section('title', 'Courier & Logistics Management System | Reliable Logistics Network')

@section('content')
<!-- Hero Section -->
<section class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #0d47a1 0%, #1e3a8a 50%, #0f172a 100%);">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm">
                    <i class="fa-solid fa-truck-fast me-1"></i> Fast &bull; Accurate &bull; Nationwide
                </span>
                <h1 class="display-4 fw-bolder mb-3 lh-sm">
                    Ship Across the Nation with Speed & Absolute Precision
                </h1>
                <p class="lead text-light text-opacity-75 mb-4">
                    Enterprise logistics and courier management platform modeled after JNE, SiCepat, and J&T. Powered by PostgreSQL 18, automated volumetric tariff engines, and real-time tracking checkpoints.
                </p>

                <!-- Dual Action Form Tabs -->
                <div class="card bg-white text-dark shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-light border-0 p-2">
                        <ul class="nav nav-pills nav-fill" id="heroTabs">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold py-2" data-bs-toggle="pill" data-bs-target="#tabTrack">
                                    <i class="fa-solid fa-magnifying-glass me-2 text-primary"></i> Track Waybill (Cek Resi)
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold py-2" data-bs-toggle="pill" data-bs-target="#tabRates">
                                    <i class="fa-solid fa-calculator me-2 text-success"></i> Check Shipping Tariff (Cek Ongkir)
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content">
                            <!-- Track Tab -->
                            <div class="tab-pane fade show active" id="tabTrack">
                                <form action="{{ route('tracking.search') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary text-uppercase">Enter Tracking / AWB Numbers</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fa-solid fa-barcode text-primary"></i>
                                            </span>
                                            <input type="text" name="awb" class="form-control border-start-0" placeholder="e.g. CR-202610-884912, CR-202610-771234" value="CR-202610-884912" required>
                                            <button class="btn btn-accent px-4 fw-bold" type="submit">
                                                <i class="fa-solid fa-magnifying-glass me-1"></i> Track Now
                                            </button>
                                        </div>
                                        <div class="form-text small mt-2">
                                            <i class="fa-solid fa-circle-info text-info me-1"></i> Supports multiple AWBs separated by comma or space. Try demo AWB: <code>CR-202610-884912</code> (Delivered) or <code>CR-202610-771234</code> (Out for Delivery).
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Rates Tab -->
                            <div class="tab-pane fade" id="tabRates">
                                <form action="{{ route('rates.calculate') }}" method="POST">
                                    @csrf
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-5">
                                            <label class="form-label small fw-bold text-secondary">Origin City</label>
                                            <input type="text" name="origin_city" class="form-control" list="originList" placeholder="e.g. Jakarta" value="Jakarta" required>
                                            <datalist id="originList">
                                                @foreach($cities as $c)
                                                    <option value="{{ $c }}">
                                                @endforeach
                                            </datalist>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small fw-bold text-secondary">Destination City</label>
                                            <input type="text" name="destination_city" class="form-control" list="destList" placeholder="e.g. Bandung" value="Bandung" required>
                                            <datalist id="destList">
                                                @foreach($cities as $c)
                                                    <option value="{{ $c }}">
                                                @endforeach
                                            </datalist>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-secondary">Weight (Kg)</label>
                                            <input type="number" step="0.1" name="weight" class="form-control" value="1.0" min="0.1" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold py-2">
                                        <i class="fa-solid fa-calculator me-1"></i> Calculate Shipping Rates
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 text-center d-none d-lg-block">
                <div class="bg-white bg-opacity-10 p-4 rounded-4 backdrop-blur shadow border border-white border-opacity-10 text-start">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-warning text-dark rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-shield-halved fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-white mb-0">High-Reliability Architecture</h5>
                            <span class="text-white-50 small">Internal Intranet & Local Air-Gapped Network Ready</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                            <div>
                                <strong class="text-white">PostgreSQL 18 Optimized Schema</strong>
                                <div class="text-white-50 small">Indexes, composite routing lookups, JSONB metadata, and high-concurrency booking transactions.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                            <div>
                                <strong class="text-white">Standard 4x6" Thermal Label Barcode</strong>
                                <div class="text-white-50 small">Offline JsBarcode generation with Code128 format compatible with thermal label printers.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                            <div>
                                <strong class="text-white">Comprehensive Bulk CSV Engine</strong>
                                <div class="text-white-50 small">Fast import for shipments, batch status scans, and tariff matrices with full error logs.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Stats Banner -->
<section class="py-4 bg-white shadow-sm border-bottom">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="h2 fw-bolder text-primary mb-0">{{ number_format($stats['total_shipments']) }}</div>
                <div class="text-secondary small fw-bold text-uppercase">Packages Handled</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="h2 fw-bolder text-warning mb-0">{{ $stats['active_hubs'] }}</div>
                <div class="text-secondary small fw-bold text-uppercase">Sorting Hubs & Drop Points</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="h2 fw-bolder text-success mb-0">{{ $stats['sla_rate'] }}</div>
                <div class="text-secondary small fw-bold text-uppercase">On-Time Delivery SLA</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="h2 fw-bolder text-info mb-0">{{ $stats['provinces_covered'] }}</div>
                <div class="text-secondary small fw-bold text-uppercase">Provinces Reached</div>
            </div>
        </div>
    </div>
</section>

<!-- Our Shipping Services -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary px-3 py-2 text-uppercase mb-2">Shipping Products</span>
            <h2 class="fw-bold">Tailored Delivery Services for Every Need</h2>
            <p class="text-secondary">From instant same-day parcel drops to full truckload heavy cargo, choose your speed.</p>
        </div>

        <div class="row g-4">
            @foreach($services as $s)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-all">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <span class="badge {{ $s->badge_class }} px-3 py-2 fs-6 fw-bold">
                                {{ $s->code }}
                            </span>
                            <span class="text-muted small fw-bold">
                                <i class="fa-regular fa-clock me-1 text-primary"></i> {{ $s->etd_range }}
                            </span>
                        </div>
                        <div class="card-body px-4 pt-3 pb-4">
                            <h4 class="fw-bold text-dark mb-2">{{ $s->name }}</h4>
                            <p class="text-secondary small mb-3">{{ $s->description }}</p>
                            <div class="bg-light p-3 rounded-3 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary">Min. Weight:</span>
                                    <span class="fw-bold">{{ $s->min_weight_kg }} Kg</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">Volumetric Divisor:</span>
                                    <span class="fw-bold">{{ number_format($s->volumetric_divisor) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Drop Points Preview -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <span class="badge bg-success px-3 py-2 text-uppercase mb-2">Network Nodes</span>
                <h3 class="fw-bold mb-0">Nationwide Hubs & Drop Points</h3>
            </div>
            <a href="{{ route('branches.index') }}" class="btn btn-outline-primary fw-bold mt-3 mt-md-0">
                <i class="fa-solid fa-map-location-dot me-1"></i> View All Branches
            </a>
        </div>

        <div class="row g-3">
            @foreach($branches as $b)
                <div class="col-md-4">
                    <div class="card border rounded-3 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold text-dark mb-0">{{ $b->name }}</h6>
                            <span class="badge bg-secondary small">{{ $b->type_label }}</span>
                        </div>
                        <p class="text-secondary small mb-2"><i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $b->address }}, {{ $b->city }}</p>
                        <div class="small text-muted mt-auto">
                            <i class="fa-solid fa-phone text-primary me-1"></i> {{ $b->phone ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
