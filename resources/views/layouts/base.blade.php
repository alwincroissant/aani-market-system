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
                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        @php
                            $navCart = session('cart', []);
                            $navCartCount = collect($navCart)->sum('quantity');
                            $navCartTotal = collect($navCart)->sum(function ($item) {
                                return $item['price_per_unit'] * $item['quantity'];
                            });
                            $role = auth()->user()->role;
                        @endphp
                        {{-- Customer navigation --}}
                        @if($role === 'customer')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shop.index') }}">Shop</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}#market-map-section">Market map</a>
                            </li>
                        @endif

                        {{-- Vendor navigation --}}
                        @if($role === 'vendor')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="vendorDropdown" role="button" data-bs-toggle="dropdown">
                                    Vendor
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('products.index') }}">
                                        <i class="bi bi-box-seam"></i> My products
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('home') }}">
                                        <i class="bi bi-eye"></i> View customer site
                                    </a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- Admin navigation --}}
                        @if($role === 'administrator')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard.index') }}">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people"></i> User management
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.map.index') }}">
                                        <i class="bi bi-map"></i> Market map & stalls
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.sales') }}">
                                        <i class="bi bi-graph-up"></i> Sales report
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.attendance') }}">
                                        <i class="bi bi-calendar-check"></i> Attendance report
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('home', ['view_site' => 1]) }}">
                                        <i class="bi bi-eye"></i> View customer site
                                    </a></li>
                                </ul>
                            </li>
                        @endif
                        {{-- Cart dropdown for authenticated users --}}
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link dropdown-toggle position-relative" href="#" id="cartDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-cart"></i>
                                @if($navCartCount > 0)
                                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                                        {{ $navCartCount }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="cartDropdown" style="min-width: 280px;">
                                @if($navCartCount === 0)
                                    <li class="dropdown-item text-muted small">Your cart is empty</li>
                                @else
                                    @foreach(array_slice($navCart, 0, 5) as $item)
                                        <li class="dropdown-item small d-flex justify-content-between">
                                            <div>
                                                <div class="fw-semibold">{{ $item['product_name'] }}</div>
                                                <div class="text-muted">
                                                    x{{ $item['quantity'] }} @ ₱{{ number_format($item['price_per_unit'], 2) }}
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                ₱{{ number_format($item['price_per_unit'] * $item['quantity'], 2) }}
                                            </div>
                                        </li>
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-item d-flex justify-content-between small">
                                        <span>Total</span>
                                        <strong>₱{{ number_format($navCartTotal, 2) }}</strong>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-center" href="{{ route('cart.view') }}">
                                            View full cart & checkout
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="nav-link" href="{{ route('logout') }}">Sign out</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('shop.index') }}">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}#market-map-section">Market map</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.register') }}">Sign up</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-outline-success btn-sm" href="{{ route('vendor.register') }}">
                                <i class="bi bi-shop"></i> Become a vendor
                            </a>
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

