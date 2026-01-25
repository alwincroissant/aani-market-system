<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AANI Market')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="pt-5 {{ request()->is('admin*') ? 'admin-theme' : '' }}">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">AANI Market</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        @if(auth()->user()->role === 'vendor')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('products.index') }}">My Products</a>
                            </li>
                        @endif
                        @if(auth()->user()->role === 'administrator')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    Admin Panel
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard.index') }}">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people"></i> User Management
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.map.index') }}">
                                        <i class="bi bi-map"></i> Map Management
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.sales') }}">
                                        <i class="bi bi-graph-up"></i> Sales Report
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.attendance') }}">
                                        <i class="bi bi-calendar-check"></i> Attendance Report
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('home', ['view_site' => 1]) }}">
                                        <i class="bi bi-eye"></i> View Site
                                    </a></li>
                                </ul>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('shop.index') }}">Browse Shops</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @include('layouts.flash-messages')
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    @if(request()->is('admin*'))
        <style>
            :root {
                --admin-green: #1f7a3e;
                --admin-green-dark: #155c2f;
                --admin-green-soft: #e8f3ec;
            }
            .admin-theme .navbar {
                background-color: var(--admin-green-dark) !important;
            }
            .admin-theme .btn-primary {
                background-color: var(--admin-green);
                border-color: var(--admin-green);
            }
            .admin-theme .btn-primary:hover {
                background-color: var(--admin-green-dark);
                border-color: var(--admin-green-dark);
            }
            .admin-theme .btn-outline-primary {
                color: var(--admin-green);
                border-color: var(--admin-green);
            }
            .admin-theme .btn-outline-primary:hover {
                background-color: var(--admin-green);
                border-color: var(--admin-green);
            }
            .admin-theme .card-header {
                background-color: var(--admin-green-soft);
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                font-weight: 600;
            }
            .admin-theme .badge.bg-success {
                background-color: var(--admin-green) !important;
            }
            .admin-theme .table thead th {
                background-color: #f6fbf7;
            }
            .admin-theme .stat-card {
                border: 1px solid rgba(31, 122, 62, 0.15);
                background: linear-gradient(180deg, #ffffff 0%, #f3faf6 100%);
            }
        </style>
    @endif
</body>
</html>

