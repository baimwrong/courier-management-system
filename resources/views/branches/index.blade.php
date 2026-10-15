@extends('layouts.app')

@section('title', 'Branch & Drop Point Network | Courier Management System')

@section('content')
<div class="bg-primary text-white py-4 shadow-sm" style="background: linear-gradient(135deg, #0d47a1, #1e3a8a);">
    <div class="container">
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-map-location-dot me-2"></i> Nationwide Drop Points & Sorting Hubs</h2>
        <p class="text-light text-opacity-75 mb-0">Locate your nearest drop counter or regional sorting center.</p>
    </div>
</div>

<div class="container my-5">
    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('branches.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Filter by City</label>
                        <select name="city" class="form-select">
                            <option value="">All Cities</option>
                            @foreach($cities as $c)
                                <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Branch Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="central_hub" {{ request('type') == 'central_hub' ? 'selected' : '' }}>Central Hub</option>
                            <option value="sorting_hub" {{ request('type') == 'sorting_hub' ? 'selected' : '' }}>Sorting Hub</option>
                            <option value="branch" {{ request('type') == 'branch' ? 'selected' : '' }}>Branch Office</option>
                            <option value="drop_point" {{ request('type') == 'drop_point' ? 'selected' : '' }}>Drop Point</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Keyword Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, code, or address..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-custom w-100 fw-bold py-2">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Branch Cards Grid -->
    <div class="row g-4">
        @forelse($branches as $b)
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-secondary mb-1" style="font-size: 0.7rem;">{{ $b->code }}</span>
                            <h5 class="fw-bold text-dark mb-0">{{ $b->name }}</h5>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                            {{ $b->type_label }}
                        </span>
                    </div>

                    <p class="text-secondary small mb-3 flex-grow-1">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i>
                        {{ $b->address }}, {{ $b->city }}, {{ $b->province }} {{ $b->postal_code }}
                    </p>

                    <div class="bg-light p-3 rounded-3 small">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-phone text-primary"></i>
                            <span class="fw-bold">{{ $b->phone ?? '021-1500-888' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-clock text-warning"></i>
                            <span>Mon - Sun: 08:00 - 21:00 WIB</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-store-slash text-muted display-4 mb-3"></i>
                <h5 class="fw-bold text-secondary">No Branches Found</h5>
                <p class="text-muted">No drop points match your current filter parameters. Try clearing your filters.</p>
                <a href="{{ route('branches.index') }}" class="btn btn-outline-primary btn-sm">Clear Filter</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $branches->links() }}
    </div>
</div>
@endsection
