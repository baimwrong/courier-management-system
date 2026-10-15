@extends('layouts.app')

@section('title', 'Operations Login | Courier & Logistics Management')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white p-4 text-center">
                    <i class="fa-solid fa-truck-fast text-warning display-5 mb-2"></i>
                    <h4 class="fw-bolder mb-0">Operations Portal</h4>
                    <span class="text-light text-opacity-75 small">Courier & Logistics Management System</span>
                </div>

                <div class="card-body p-4 p-md-5">
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="name@courier.test" value="{{ old('email', 'admin@courier.test') }}" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Enter password" value="password" required>
                            </div>
                            <div class="form-text small">Default seed password is <code>password</code></div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                            <label class="form-check-label small" for="remember">Keep me logged in</label>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 fw-bold py-2 shadow-sm">
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Sign In to Back-Office
                        </button>
                    </form>

                    <!-- Quick Demo Role Switcher -->
                    <div class="mt-4 pt-3 border-top">
                        <label class="form-label small fw-bold text-secondary text-uppercase d-block text-center mb-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> Quick 1-Click Demo Login
                        </label>
                        <div class="d-flex flex-column gap-2">
                            @foreach($demoUsers as $du)
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $du->id }}">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-between p-2">
                                        <div class="text-start">
                                            <div class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $du->name }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">{{ $du->email }}</div>
                                        </div>
                                        <span class="badge {{ $du->role_badge_class }}">{{ $du->role_label }}</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
