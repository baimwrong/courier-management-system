@extends('layouts.app')

@section('title', 'Shipping Tariff Calculator | Courier Management System')

@section('content')
<div class="bg-primary text-white py-4 shadow-sm" style="background: linear-gradient(135deg, #0d47a1, #1e3a8a);">
    <div class="container">
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-calculator me-2"></i> Shipping Rate & Tariff Calculator</h2>
        <p class="text-light text-opacity-75 mb-0">Estimate your delivery costs with automatic volumetric weight and insurance calculations.</p>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4">
        <!-- Calculator Form Column -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-sliders me-2 text-primary"></i> Shipment Parameters</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('rates.calculate') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Origin City</label>
                            <input type="text" name="origin_city" class="form-control" list="originCityList" value="{{ $selectedOrigin ?? 'Jakarta' }}" placeholder="Type origin city..." required>
                            <datalist id="originCityList">
                                @foreach($originCities as $c)
                                    <option value="{{ $c }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Destination City</label>
                            <input type="text" name="destination_city" class="form-control" list="destCityList" value="{{ $selectedDest ?? 'Bandung' }}" placeholder="Type destination city..." required>
                            <datalist id="destCityList">
                                @foreach($destCities as $c)
                                    <option value="{{ $c }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Actual Weight (Kg)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="weight" class="form-control" value="{{ $params['weight'] ?? '1.0' }}" min="0.1" required>
                                <span class="input-group-text bg-light">KG</span>
                            </div>
                        </div>

                        <!-- Package Dimensions -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Dimensions (L x W x H in CM)</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="number" step="1" name="length" class="form-control" placeholder="Length" value="{{ $params['length'] ?? '10' }}" required>
                                </div>
                                <div class="col-4">
                                    <input type="number" step="1" name="width" class="form-control" placeholder="Width" value="{{ $params['width'] ?? '10' }}" required>
                                </div>
                                <div class="col-4">
                                    <input type="number" step="1" name="height" class="form-control" placeholder="Height" value="{{ $params['height'] ?? '10' }}" required>
                                </div>
                            </div>
                            <div class="form-text small">Volumetric weight is computed automatically via <code>(L x W x H) / 5000</code>.</div>
                        </div>

                        <!-- Optional Extra Fees -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary">Goods Value (IDR)</label>
                                <input type="number" name="declared_value" class="form-control" placeholder="Optional" value="{{ $params['declared_value'] ?? 0 }}">
                                <span class="form-text small" style="font-size: 0.7rem;">0.2% insurance</span>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary">COD Amount (IDR)</label>
                                <input type="number" name="cod_amount" class="form-control" placeholder="Optional" value="{{ $params['cod_amount'] ?? 0 }}">
                                <span class="form-text small" style="font-size: 0.7rem;">2.5% COD fee</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 fw-bold py-2 shadow-sm">
                            <i class="fa-solid fa-calculator me-1"></i> Check Available Rates
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Rate Results Column -->
        <div class="col-lg-7">
            @if(isset($results))
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary px-3 py-1 mb-1">Route Result</span>
                            <h5 class="fw-bold text-dark mb-0">
                                {{ $selectedOrigin }} <i class="fa-solid fa-arrow-right text-muted mx-2"></i> {{ $selectedDest }}
                            </h5>
                        </div>
                        <span class="text-secondary small fw-bold">Chargeable Weight: <strong>{{ $results[0]['chargeable_weight'] ?? 1 }} Kg</strong></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-secondary small text-uppercase">
                                    <th class="ps-4">Service Tier</th>
                                    <th>Est. Transit</th>
                                    <th>Chargeable Wt.</th>
                                    <th>Base Cost</th>
                                    <th class="text-end pe-4">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $r)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $r['badge_class'] }} px-2 py-1 fw-bold">{{ $r['service_code'] }}</span>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $r['service_name'] }}</div>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $r['description'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fa-regular fa-clock me-1 text-primary"></i> {{ $r['etd'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $r['chargeable_weight'] }} Kg</span>
                                            <div class="text-muted small" style="font-size: 0.7rem;">(Act: {{ $r['actual_weight'] }}k / Vol: {{ $r['volumetric_weight'] }}k)</div>
                                        </td>
                                        <td>Rp {{ number_format($r['shipping_cost']) }}</td>
                                        <td class="text-end pe-4">
                                            <div class="h5 fw-bolder text-primary mb-0">
                                                Rp {{ number_format($r['total_cost']) }}
                                            </div>
                                            @if($r['insurance_cost'] > 0 || $r['cod_fee'] > 0)
                                                <div class="text-muted small" style="font-size: 0.7rem;">
                                                    Incl. fee Rp {{ number_format($r['insurance_cost'] + $r['cod_fee']) }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                    <div class="card-body">
                        <i class="fa-solid fa-boxes-packing text-muted display-4 mb-3"></i>
                        <h4 class="fw-bold text-secondary">Calculate Your Shipping Costs</h4>
                        <p class="text-muted">Select an origin and destination city with package dimensions to view all pricing options.</p>
                    </div>
                </div>
            @endif

            <!-- Calculation Rules Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mt-4">
                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-circle-question text-info me-2"></i> How We Calculate Tariffs</h6>
                <div class="row g-3 small text-secondary">
                    <div class="col-md-6">
                        <strong>Volumetric Weight Rule:</strong>
                        <p class="mb-0">Packages with large volume relative to actual weight are charged based on volumetric formula: <code>(Length x Width x Height in cm) / 5000</code>.</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Heavy Cargo Minimum:</strong>
                        <p class="mb-0">Heavy Cargo starts at a minimum charged weight of 10 Kg using a heavy freight divisor of 4,000 for door-to-door trucking.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
