@extends('layouts.admin')

@section('title', 'Branches & Sorting Hubs')
@section('page_title', 'Hubs, Branches & Drop Points')

@section('breadcrumbs')
<span class="text-secondary small">/ Network Node Infrastructure</span>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.branches.index') }}" method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search branch code, name, city..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select">
                        <option value="">All Branch Types</option>
                        <option value="central_hub" {{ request('type') == 'central_hub' ? 'selected' : '' }}>Central Hub</option>
                        <option value="sorting_hub" {{ request('type') == 'sorting_hub' ? 'selected' : '' }}>Sorting Hub</option>
                        <option value="branch" {{ request('type') == 'branch' ? 'selected' : '' }}>Branch Office</option>
                        <option value="drop_point" {{ request('type') == 'drop_point' ? 'selected' : '' }}>Drop Point</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom fw-bold flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddBranch">
                        <i class="fa-solid fa-plus me-1"></i> Add Node
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Branches Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold text-dark">Logistics Network Nodes</span>
            <span class="badge bg-secondary ms-2">{{ $branches->total() }} Active</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th class="ps-3">Code</th>
                    <th>Branch Name</th>
                    <th>Type</th>
                    <th>City / Province</th>
                    <th>Address</th>
                    <th>Phone / Email</th>
                    <th>Staff</th>
                    <th class="text-end pe-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $b)
                    <tr>
                        <td class="ps-3 fw-bold text-primary">{{ $b->code }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $b->name }}</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $b->type_label }}</span>
                        </td>
                        <td>
                            <div class="fw-bold small">{{ $b->city }}</div>
                            <div class="text-muted small" style="font-size: 0.7rem;">{{ $b->province }}</div>
                        </td>
                        <td>
                            <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $b->address }}</div>
                        </td>
                        <td>
                            <div class="small"><i class="fa-solid fa-phone text-muted me-1"></i> {{ $b->phone ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $b->users_count }} personnel</span>
                        </td>
                        <td class="text-end pe-3">
                            @if($b->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No branches found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($branches->hasPages())
        <div class="p-3 border-top">
            {{ $branches->links() }}
        </div>
    @endif
</div>

<!-- Modal: Add Branch -->
<div class="modal fade" id="modalAddBranch" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.branches.store') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-network-wired text-primary me-2"></i> Register Branch / Hub</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Branch Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. SUB-DC02" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Node Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="central_hub">Central Hub</option>
                                <option value="sorting_hub">Sorting Hub</option>
                                <option value="branch">Branch Office</option>
                                <option value="drop_point">Drop Point</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Branch Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Surabaya Timur Delivery Center" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">City *</label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Surabaya" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Province *</label>
                            <input type="text" name="province" class="form-control" placeholder="e.g. Jawa Timur" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Complete Address *</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Full street address..." required></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. 031-889900">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" placeholder="e.g. 60281">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom fw-bold">Save Branch</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
