<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Operations Center') | Courier & Logistics Management</title>

    <!-- Local Offline Assets - Zero Internet Required -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    @stack('styles')
</head>
<body class="bg-light">

    <div class="d-flex min-vh-100">
        <!-- Sidebar -->
        <aside class="sidebar d-flex flex-column flex-shrink-0 p-0 text-white shadow-lg">
            <!-- Brand -->
            <div class="p-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-white text-decoration-none gap-2">
                    <i class="fa-solid fa-truck-fast text-warning fs-4"></i>
                    <div>
                        <div class="fw-bold fs-5 tracking-tight">Courier CMS</div>
                        <div class="small text-secondary" style="font-size: 0.7rem;">LOGISTICS OPERATIONS</div>
                    </div>
                </a>
            </div>

            <!-- Active User Branch Box -->
            <div class="px-3 py-2 bg-dark bg-opacity-50 border-bottom border-secondary border-opacity-25 small">
                <div class="text-secondary" style="font-size: 0.72rem; text-transform: uppercase;">Operational Node:</div>
                <div class="text-white fw-bold d-flex align-items-center gap-1">
                    <i class="fa-solid fa-warehouse text-info"></i>
                    <span>{{ auth()->user()->branch?->name ?? 'Headquarters (Global)' }}</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex-grow-1 overflow-auto py-2">
                <div class="nav-header">Operations Core</div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.shipments.index') }}" class="nav-link {{ request()->routeIs('admin.shipments.index') || request()->routeIs('admin.shipments.show') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            <span>All Shipments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.shipments.create') }}" class="nav-link {{ request()->routeIs('admin.shipments.create') ? 'active' : '' }}">
                            <i class="fa-solid fa-plus-circle text-warning"></i>
                            <span>Book Shipment</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.bulk-import.index') }}" class="nav-link {{ request()->routeIs('admin.bulk-import.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-import text-success"></i>
                            <span>Bulk Import Center</span>
                            <span class="badge bg-danger ms-auto small">CSV</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.manifests.index') }}" class="nav-link {{ request()->routeIs('admin.manifests.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>Manifests & Run-Sheets</span>
                        </a>
                    </li>
                </ul>

                <div class="nav-header">Master & Rates</div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('admin.tariffs.index') }}" class="nav-link {{ request()->routeIs('admin.tariffs.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-calculator"></i>
                            <span>Tariff Rate Matrix</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.branches.index') }}" class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-network-wired"></i>
                            <span>Hubs & Branches</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-gear"></i>
                            <span>Staff & Couriers</span>
                        </a>
                    </li>
                </ul>

                <div class="nav-header">Reports & Analytics</div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Analytics & COD</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Footer / System status -->
            <div class="p-3 border-top border-secondary border-opacity-25 bg-dark bg-opacity-75 small">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-success"><i class="fa-solid fa-circle-dot me-1"></i> PostgreSQL 18</span>
                    <a href="{{ route('home') }}" class="text-secondary text-decoration-none" title="Visit Public Portal" target="_blank">
                        <i class="fa-solid fa-globe"></i> Portal
                    </a>
                </div>
                <div class="text-secondary" style="font-size: 0.7rem;">
                    Local Intranet Setup &bull; Production Mode
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white border-bottom shadow-sm px-4 py-2 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Overview')</h5>
                    @yield('breadcrumbs')
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Quick Demo Role Switcher (Helpful for pair testing!) -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-switch me-1"></i> Switch Role (Demo)
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">Fast Operational Switcher</h6></li>
                            <li>
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="1">
                                    <button class="dropdown-item d-flex align-items-center justify-content-between" type="submit">
                                        <span><i class="fa-solid fa-user-shield text-danger me-2"></i> Super Administrator</span>
                                        <span class="badge bg-danger small">Admin</span>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="2">
                                    <button class="dropdown-item d-flex align-items-center justify-content-between" type="submit">
                                        <span><i class="fa-solid fa-user-tie text-primary me-2"></i> Branch Manager (JKT)</span>
                                        <span class="badge bg-primary small">Manager</span>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="3">
                                    <button class="dropdown-item d-flex align-items-center justify-content-between" type="submit">
                                        <span><i class="fa-solid fa-boxes-packing text-warning me-2"></i> Hub Operator (CGK)</span>
                                        <span class="badge bg-warning text-dark small">Operator</span>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="5">
                                    <button class="dropdown-item d-flex align-items-center justify-content-between" type="submit">
                                        <span><i class="fa-solid fa-motorcycle text-success me-2"></i> Delivery Courier (Budi)</span>
                                        <span class="badge bg-success small">Courier</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="text-start d-none d-md-block">
                                <div class="fw-bold small lh-1">{{ auth()->user()->name }}</div>
                                <span class="badge {{ auth()->user()->role_badge_class }} py-0 px-1" style="font-size: 0.65rem;">
                                    {{ auth()->user()->role_label }}
                                </span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-secondary" style="font-size: 0.7rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">Signed in as {{ auth()->user()->email }}</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i> Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Alerts -->
            <div class="px-4 pt-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-xmark me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            <!-- Main Content Container -->
            <main class="flex-grow-1 overflow-auto p-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Local Offline JS Scripts -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chartjs/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendor/jsbarcode.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
