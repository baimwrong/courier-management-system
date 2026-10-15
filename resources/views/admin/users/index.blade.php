@extends('layouts.admin')

@section('title', 'Staff & Couriers')
@section('page_title', 'Personnel, Staff & Courier Fleet')

@section('breadcrumbs')
<span class="text-secondary small">/ Operations Human Resources</span>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.users.index') }}" method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Name, email, phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="branch_manager" {{ request('role') == 'branch_manager' ? 'selected' : '' }}>Branch Manager</option>
                        <option value="hub_operator" {{ request('role') == 'hub_operator' ? 'selected' : '' }}>Hub Operator</option>
                        <option value="courier" {{ request('role') == 'courier' ? 'selected' : '' }}>Delivery Courier</option>
                        <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Counter Cashier</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->code }} - {{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom fw-bold flex-grow-1">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddUser">
                        <i class="fa-solid fa-user-plus me-1"></i> Add
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold text-dark">Staff Directory</span>
            <span class="badge bg-secondary ms-2">{{ $users->total() }} Users</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-uppercase">
                    <th class="ps-3">Name / Email</th>
                    <th>Role</th>
                    <th>Assigned Branch Node</th>
                    <th>Contact Phone</th>
                    <th>Vehicle Details (Couriers)</th>
                    <th>Registered</th>
                    <th class="text-end pe-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark">{{ $u->name }}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">{{ $u->email }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $u->role_badge_class }}">{{ $u->role_label }}</span>
                        </td>
                        <td>
                            <div class="small fw-bold">{{ $u->branch?->name ?? 'HQ / Global' }}</div>
                            <div class="text-muted small" style="font-size: 0.7rem;">{{ $u->branch?->city }}</div>
                        </td>
                        <td>
                            <span class="small">{{ $u->phone ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($u->role === 'courier')
                                <span class="badge bg-light text-dark border">
                                    <i class="fa-solid fa-motorcycle text-success me-1"></i>
                                    {{ ucfirst($u->vehicle_type ?? 'Motorcycle') }} &bull; {{ $u->vehicle_plate ?? 'No Plate' }}
                                </span>
                            @else
                                <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                        <td>
                            <span class="small text-muted">{{ $u->created_at->format('d M Y') }}</span>
                        </td>
                        <td class="text-end pe-3">
                            @if($u->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Disabled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No staff found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="p-3 border-top">
            {{ $users->links() }}
        </div>
    @endif
</div>

<!-- Modal: Add User -->
<div class="modal fade" id="modalAddUser" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus text-primary me-2"></i> Register Staff / Courier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Budi Santoso" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Email *</label>
                            <input type="email" name="email" class="form-control" placeholder="name@courier.test" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Password *</label>
                            <input type="password" name="password" class="form-control" placeholder="Min 6 chars" value="password" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Role *</label>
                            <select name="role" class="form-select" required>
                                <option value="courier">Delivery Courier</option>
                                <option value="hub_operator">Hub Operator</option>
                                <option value="cashier">Counter Cashier</option>
                                <option value="branch_manager">Branch Manager</option>
                                <option value="superadmin">Super Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Assigned Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">-- Headquarters / Global --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->code }} - {{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxx">
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Vehicle Type (For Couriers)</label>
                            <select name="vehicle_type" class="form-select">
                                <option value="motorcycle">Motorcycle</option>
                                <option value="van">Van</option>
                                <option value="truck">Truck</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Vehicle Plate #</label>
                            <input type="text" name="vehicle_plate" class="form-control" placeholder="e.g. B 1234 ABC">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom fw-bold">Create User Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
