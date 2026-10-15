<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Courier & Logistics Management System') | National Express Network</title>

    <!-- Local Offline Assets - Zero Internet Required -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    @stack('styles')
</head>
<body class="bg-light">

    <!-- Top Notification Bar -->
    <div class="bg-dark text-white py-1 small">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <i class="fa-solid fa-bolt text-warning me-1"></i>
                <span>Nationwide Express Courier Network &bull; Integrated PostgreSQL 18 Architecture</span>
            </div>
            <div class="d-none d-md-flex align-items-center gap-3">
                <span><i class="fa-solid fa-headset me-1 text-info"></i> Customer Care: 1500-888</span>
                <span><i class="fa-solid fa-network-wired me-1 text-success"></i> Offline Local Network Ready</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-logo" href="{{ route('home') }}">
                <i class="fa-solid fa-truck-fast"></i>
                <span>Courier<span class="text-danger">.</span></span>
                <span class="brand-badge ms-1">LOGISTICS</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active fw-bold text-primary' : '' }}" href="{{ route('home') }}">
                            <i class="fa-solid fa-house me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tracking.*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('tracking.index') }}">
                            <i class="fa-solid fa-magnifying-glass-location me-1"></i> Track Waybill (Resi)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('rates.*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('rates.index') }}">
                            <i class="fa-solid fa-calculator me-1"></i> Shipping Rates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('branches.*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('branches.index') }}">
                            <i class="fa-solid fa-map-location-dot me-1"></i> Drop Points & Hubs
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm px-3 fw-bold">
                            <i class="fa-solid fa-gauge-high me-1"></i> Operations Portal
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary-custom btn-sm px-3 shadow-sm">
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Staff Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1">
        @if(session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-xmark me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5 border-top border-secondary">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-solid fa-truck-fast text-warning fs-3"></i>
                        <h4 class="brand-title mb-0 text-white">Courier & Logistics</h4>
                    </div>
                    <p class="text-secondary small">
                        Enterprise Courier & Logistic Management System modeled after industry leaders JNE, SiCepat, and J&T. Engineered with high-performance PostgreSQL 18, automated volumetric calculators, instant barcode labels, and bulk data processing.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">PostgreSQL 18</span>
                        <span class="badge bg-secondary">Bootstrap 5.3</span>
                        <span class="badge bg-success">100% Offline Assets</span>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-uppercase fw-bold text-white mb-3 small">Services</h6>
                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2">
                        <li><a href="{{ route('rates.index') }}" class="text-decoration-none text-secondary">Regular (REG)</a></li>
                        <li><a href="{{ route('rates.index') }}" class="text-decoration-none text-secondary">Express Overnight (EXP)</a></li>
                        <li><a href="{{ route('rates.index') }}" class="text-decoration-none text-secondary">Cargo Trucking (CARGO)</a></li>
                        <li><a href="{{ route('rates.index') }}" class="text-decoration-none text-secondary">City Same Day (SAMEDAY)</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="text-uppercase fw-bold text-white mb-3 small">Operational Links</h6>
                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2">
                        <li><a href="{{ route('tracking.index') }}" class="text-decoration-none text-secondary">Track Waybill</a></li>
                        <li><a href="{{ route('rates.index') }}" class="text-decoration-none text-secondary">Shipping Calculator</a></li>
                        <li><a href="{{ route('branches.index') }}" class="text-decoration-none text-secondary">Drop Point Network</a></li>
                        <li><a href="{{ route('login') }}" class="text-decoration-none text-secondary">Operations Login</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold text-white mb-3 small">System Environment</h6>
                    <p class="text-secondary small mb-2">
                        Designed for intranet deployment & internal network distribution with zero external CDN dependencies.
                    </p>
                    <div class="card bg-secondary bg-opacity-25 border-0 text-secondary p-2 small">
                        <div><i class="fa-solid fa-server me-1 text-info"></i> DB: PostgreSQL 18 (pgsql)</div>
                        <div><i class="fa-solid fa-code me-1 text-warning"></i> Framework: Laravel 12 / 13</div>
                        <div><i class="fa-solid fa-box-open me-1 text-success"></i> Bulk CSV Import Engine</div>
                    </div>
                </div>
            </div>

            <hr class="border-secondary my-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
                <span>&copy; {{ date('Y') }} Courier & Logistic Management System. Production-Ready Edition.</span>
                <span>Self-Contained Local Network Edition &bull; No Internet Required</span>
            </div>
        </div>
    </footer>

    <!-- Local Offline JS Scripts -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jsbarcode.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
