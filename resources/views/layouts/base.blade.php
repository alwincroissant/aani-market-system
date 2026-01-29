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
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <span class="fw-bold">AANI Market</span>
                <span class="ms-2 text-muted small d-none d-sm-inline">Wet Market Online</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('shop.index') }}">Browse Shops</a>
                        </li>
                    @endguest
                    
                    @auth
                        @if(auth()->user()->role === 'administrator')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear"></i> Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Users</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.vendors.index') }}">Vendors</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">Products</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}">Orders</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.map.index') }}">Market Map</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.sales') }}">Reports</a></li>
                                </ul>
                            </li>
                        @elseif(auth()->user()->role === 'pickup_manager')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('pickup-manager.index') }}">
                                    <i class="bi bi-qr-code-scan"></i> Pickup Manager
                                </a>
                            </li>
                        @elseif(auth()->user()->role === 'vendor')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="vendorDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-shop"></i> Vendor
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.products.index') }}">My Products</a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.orders.index') }}">Orders</a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.attendance.index') }}">Attendance</a></li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shop.index') }}">Browse Shops</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cart.view') }}">My Cart</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('customer.orders.index') }}">My Orders</a>
                            </li>
                        @endif
                    @endauth
                </ul>
                
                <ul class="navbar-nav">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.register') }}">Register</a>
                        </li>
                    @endguest
                    
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->email }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.index') }}">My Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.addresses') }}">Delivery Addresses</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.orders') }}">Order History</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}">Logout</a></li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @auth
        @if(auth()->user()->role === 'customer')
            <div class="position-fixed" style="top: 80px; right: 20px; z-index: 1000;">
                <a href="{{ route('cart.view') }}" class="btn btn-primary rounded-circle p-3 shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.49.402H5a.5.5 0 0 1-.49-.402L3.61 3.5H1.5a.5.5 0 0 1-.5-.5zM3.14 4l.7 4H13.16l.7-4H3.14zM5 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm6 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                    </svg>
                    @if(Session::get('cart'))
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ collect(Session::get('cart'))->sum('quantity') }}
                        </span>
                    @endif
                </a>
            </div>
        @endif
    @endauth

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

