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
                            $navCart = Session::has('cart') ? Session::get('cart') : null;
                            $navCartCount = $navCart ? $navCart->totalQty : 0;
                            $navCartTotal = $navCart ? $navCart->totalPrice : 0;
                            $role = auth()->user()->role;
                        @endphp
                        {{-- Customer navigation --}}
                        @if($role === 'customer')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">
                                    <i class="bi bi-house"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shop.index') }}">Shop</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}#market-map-section">Market map</a>
                            </li>
                        @endif

                        {{-- Vendor navigation --}}
                        @if($role === 'vendor')
                            <!-- Home/Store Link -->
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('vendor.dashboard') }}">
                                    <i class="bi bi-house"></i> Home
                                </a>
                            </li>
                            
                            <!-- Products Management Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="vendorProductsDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-box"></i> Products
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('products.index') }}">
                                        <i class="bi bi-list"></i> My Products
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('products.create') }}">
                                        <i class="bi bi-plus-circle"></i> Add New Product
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.reports.products') }}">
                                        <i class="bi bi-graph-up"></i> Product Performance
                                    </a></li>
                                </ul>
                            </li>
                            
                            <!-- Orders Management Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="vendorOrdersDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-cart3"></i> Orders
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('vendor.orders.index') }}">
                                        <i class="bi bi-list"></i> View Orders
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.reports.orders') }}">
                                        <i class="bi bi-graph-up"></i> Orders Report
                                    </a></li>
                                </ul>
                            </li>
                            
                            <!-- Reports & Analytics Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="vendorReportsDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-graph-up"></i> Reports
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('vendor.reports.sales') }}">
                                        <i class="bi bi-cash"></i> Sales Report
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.reports.orders') }}">
                                        <i class="bi bi-cart3"></i> Orders Report
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.reports.products') }}">
                                        <i class="bi bi-box"></i> Products Report
                                    </a></li>
                                </ul>
                            </li>
                            
                            <!-- Account Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="vendorAccountDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i> Account
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Store Settings</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.settings') }}">
                                        <i class="bi bi-gear"></i> Store Settings
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.index') }}">
                                        <i class="bi bi-person"></i> Profile Settings
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.addresses') }}">
                                        <i class="bi bi-geo-alt"></i> Delivery Addresses
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.orders') }}">
                                        <i class="bi bi-clock-history"></i> Order History
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="{{ route('logout') }}">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- Pickup Manager navigation --}}
                        @if($role === 'pickup_manager')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">
                                    <i class="bi bi-house"></i> Home
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="pickupManagerDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-qr-code"></i> Pickup Management
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('home') }}">
                                        <i class="bi bi-house"></i> Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('home') }}#market-map-section">
                                        <i class="bi bi-map"></i> Market Map
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.attendance') }}">
                                        <i class="bi bi-calendar-check"></i> Attendance Report
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
                        {{-- Cart dropdown for customers --}}
                        @if(auth()->check() && auth()->user()->role === 'customer')
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link dropdown-toggle position-relative" href="#" id="cartDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-cart"></i> My Cart
                                @if($navCartCount > 0)
                                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                                        {{ $navCartCount }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="cartDropdown" style="min-width: 280px;">
                                @if(!$navCart || $navCartCount === 0)
                                    <li class="dropdown-item text-muted small">Your cart is empty</li>
                                @else
                                    @foreach(array_slice($navCart->items, 0, 5) as $itemId => $item)
                                        <li class="dropdown-item small d-flex justify-content-between">
                                            <div>
                                                <div class="fw-semibold">{{ $item['item']->product_name }}</div>
                                                <div class="text-muted">Qty: {{ $item['qty'] }} × ₱{{ number_format($item['item']->price_per_unit, 2) }}</div>
                                            </div>
                                            <span class="text-muted">₱{{ number_format($item['price'], 2) }}</span>
                                        </li>
                                    @endforeach
                                    @if(count($navCart->items) > 5)
                                        <li class="dropdown-item text-muted small">...and {{ count($navCart->items) - 5 }} more items</li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-item d-flex justify-content-between small">
                                        <span>Total</span>
                                        <strong>₱{{ number_format($navCartTotal, 2) }}</strong>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-center" href="{{ route('getCart') }}">
                                            View full cart & checkout
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        
                        {{-- Account dropdown for customers (non-vendors) --}}
                        @if(auth()->check() && auth()->user()->role !== 'vendor')
                        <li class="nav-item dropdown ms-2">
                            <a class="nav-link dropdown-toggle" href="#" id="customerAccountDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> Account
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">My Account</h6></li>
                                <li><a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="bi bi-person"></i> Profile Settings
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.addresses') }}">
                                    <i class="bi bi-geo-alt"></i> Delivery Addresses
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.orders') }}">
                                    <i class="bi bi-receipt"></i> Order History
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                        @endif
                    @else
                        {{-- Unauthenticated user navigation --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
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

