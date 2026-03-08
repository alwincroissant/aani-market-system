<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AANI Market')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @stack('styles')
</head>
<body class="pt-5 {{ request()->is('admin*') ? 'admin-theme' : '' }} {{ auth()->check() && auth()->user()->role === 'vendor' ? 'vendor-theme' : '' }}">
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}"
                    alt="AANI Market"
                    style="height: 50px; width: auto; display: block;">
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
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile.orders') }}">
                                    <i class="bi bi-receipt"></i> My Orders
                                </a>
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
                                </ul>
                            </li>
                            
                            <!-- Sales Management Dropdown (Orders + Physical Sales) -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="vendorOrdersDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-cart3"></i> Sales
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('vendor.orders.index') }}">
                                        <i class="bi bi-list"></i> View Orders
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.walk-in-sales.create') }}">
                                        <i class="bi bi-plus-circle"></i> Record Physical Sale
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.walk-in-sales.index') }}">
                                        <i class="bi bi-shop-window"></i> View Physical Sales
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
                                    <li><a class="dropdown-item" href="{{ route('stock.recent-changes') }}">
                                        <i class="bi bi-file-earmark-text"></i> Stock Report
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
                                    <li><a class="dropdown-item" href="{{ route('profile.change-password.show') }}">
                                        <i class="bi bi-shield-lock"></i> Change Password
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="{{ route('logout') }}">
                                        <i class="bi bi-box-arrow-right"></i> Logout
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
                                    <li><a class="dropdown-item" href="{{ route('admin.pickups.index') }}">
                                        <i class="bi bi-qr-code-scan"></i> Pickup operations
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('home', ['view_site' => 1]) }}">
                                        <i class="bi bi-eye"></i> View customer site
                                    </a></li>
                                </ul>
                            </li>
                            
                            <!-- Account Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminAccountDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i> Account
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">My Account</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.index') }}">
                                        <i class="bi bi-person"></i> Profile Settings
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="{{ route('logout') }}">
                                        <i class="bi bi-box-arrow-right"></i> Logout
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
                        
                        {{-- Account dropdown for customers only --}}
                        @if(auth()->check() && auth()->user()->role === 'customer')
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

        {{-- Vendor Rent Alert Popup --}}
        @auth
            @if(auth()->user()->role === 'vendor')
                @php
                    $vendorForRent = \App\Models\Vendor::where('user_id', auth()->id())->first();
                    $rentAlerts = collect();
                    if ($vendorForRent) {
                        $rentAlerts = \App\Models\StallPayment::where('vendor_id', $vendorForRent->id)
                            ->where('status', '!=', 'paid')
                            ->where('due_date', '<=', now()->addDays(7)->toDateString())
                            ->orderBy('due_date')
                            ->get();
                    }
                @endphp
                @if($rentAlerts->count() > 0)
                    <!-- Rent Alert Modal -->
                    <div class="modal fade" id="rentAlertModal" tabindex="-1" aria-labelledby="rentAlertModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header {{ $rentAlerts->where('status', 'overdue')->count() > 0 ? 'bg-danger' : 'bg-warning' }} text-white">
                                    <h5 class="modal-title" id="rentAlertModalLabel">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        Stall Rent Alert
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @foreach($rentAlerts as $alert)
                                        <div class="d-flex align-items-start mb-3 p-3 rounded {{ $alert->status === 'overdue' ? 'bg-danger-subtle border border-danger-subtle' : 'bg-warning-subtle border border-warning-subtle' }}">
                                            <div class="me-3 mt-1">
                                                @if($alert->status === 'overdue')
                                                    <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                                                @else
                                                    <i class="bi bi-clock-fill text-warning fs-4"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">
                                                    @if($alert->status === 'overdue')
                                                        <span class="text-danger">OVERDUE</span>
                                                    @else
                                                        <span class="text-warning">Due Soon</span>
                                                    @endif
                                                    — Stall #{{ $alert->stall_id }}
                                                    @if($alert->billing_period)
                                                        <small class="text-muted">({{ $alert->billing_period }})</small>
                                                    @endif
                                                </div>
                                                <div class="mt-1">
                                                    <span class="fw-semibold">₱{{ number_format($alert->amount_due - $alert->amount_paid, 2) }}</span>
                                                    remaining
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    Due: {{ \Carbon\Carbon::parse($alert->due_date)->format('M d, Y') }}
                                                    @if($alert->status === 'overdue')
                                                        <span class="text-danger fw-semibold">
                                                            ({{ \Carbon\Carbon::parse($alert->due_date)->diffForHumans() }})
                                                        </span>
                                                    @else
                                                        <span class="text-warning fw-semibold">
                                                            ({{ \Carbon\Carbon::parse($alert->due_date)->diffForHumans() }})
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Remind Me Later</button>
                                    <a href="{{ route('vendor.stall-payments') }}" class="btn {{ $rentAlerts->where('status', 'overdue')->count() > 0 ? 'btn-danger' : 'btn-warning' }}">
                                        <i class="bi bi-credit-card me-1"></i> Pay Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session()->pull('show_rent_alert'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var rentModal = new bootstrap.Modal(document.getElementById('rentAlertModal'));
                            rentModal.show();
                        });
                    </script>
                    @endif
                @endif
            @endif
        @endauth

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
                background-color: #BFE6CC !important;
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

    @auth
        @if(auth()->user()->role === 'vendor')
            <style>
                :root {
                    --vendor-bg: #F5F4F0;
                    --vendor-surface: #FFFFFF;
                    --vendor-border: #E4E2DC;
                    --vendor-text: #1A1916;
                    --vendor-muted: #7A7871;
                    --vendor-accent: #1D6F42;
                    --vendor-accent-light: #EAF4EE;
                    --vendor-accent-dark: #155232;
                    --vendor-warm: #D97706;
                    --vendor-warm-light: #FEF3C7;
                    --vendor-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.05);
                }

                .vendor-theme {
                    background: var(--vendor-bg);
                    color: var(--vendor-text);
                }

                .vendor-theme .navbar {
                    background-color: var(--vendor-surface) !important;
                    border-bottom: 1px solid var(--vendor-border) !important;
                }

                .vendor-theme .nav-link,
                .vendor-theme .dropdown-item,
                .vendor-theme .dropdown-header {
                    color: var(--vendor-text);
                }

                .vendor-theme .nav-link:hover,
                .vendor-theme .nav-link:focus,
                .vendor-theme .dropdown-item:hover,
                .vendor-theme .dropdown-item:focus {
                    color: var(--vendor-accent);
                    background-color: var(--vendor-accent-light);
                }

                .vendor-theme .container > .card,
                .vendor-theme .card {
                    background: var(--vendor-surface);
                    border: 1px solid var(--vendor-border);
                    box-shadow: var(--vendor-shadow);
                }

                .vendor-theme .card-header {
                    background: #F8F8F5;
                    border-bottom: 1px solid var(--vendor-border);
                    color: var(--vendor-text);
                    font-weight: 600;
                }

                .vendor-theme .btn-primary,
                .vendor-theme .bg-primary,
                .vendor-theme .badge.bg-primary {
                    background-color: var(--vendor-accent) !important;
                    border-color: var(--vendor-accent) !important;
                    color: #fff !important;
                }

                .vendor-theme .btn-primary:hover,
                .vendor-theme .btn-primary:focus {
                    background-color: var(--vendor-accent-dark) !important;
                    border-color: var(--vendor-accent-dark) !important;
                }

                .vendor-theme .btn-success,
                .vendor-theme .badge.bg-success {
                    background-color: var(--vendor-accent) !important;
                    border-color: var(--vendor-accent) !important;
                }

                .vendor-theme .btn-outline-primary {
                    color: var(--vendor-accent) !important;
                    border-color: #A9CCB8 !important;
                }

                .vendor-theme .btn-outline-primary:hover,
                .vendor-theme .btn-outline-primary:focus {
                    background-color: var(--vendor-accent-light) !important;
                    color: var(--vendor-accent-dark) !important;
                    border-color: #8FBFA5 !important;
                }

                .vendor-theme .btn-outline-success {
                    color: var(--vendor-accent-dark) !important;
                    border-color: #A9CCB8 !important;
                }

                .vendor-theme .btn-outline-success:hover,
                .vendor-theme .btn-outline-success:focus {
                    background-color: var(--vendor-accent-light) !important;
                    color: var(--vendor-accent-dark) !important;
                    border-color: #8FBFA5 !important;
                }

                .vendor-theme .btn-outline-warning {
                    color: var(--vendor-warm) !important;
                    border-color: #F2C588 !important;
                }

                .vendor-theme .btn-outline-warning:hover,
                .vendor-theme .btn-outline-warning:focus {
                    background-color: var(--vendor-warm-light) !important;
                    color: #92400E !important;
                    border-color: #F2C588 !important;
                }

                .vendor-theme .text-primary,
                .vendor-theme .link-primary {
                    color: var(--vendor-accent) !important;
                }

                .vendor-theme .text-muted {
                    color: var(--vendor-muted) !important;
                }

                .vendor-theme .alert-warning {
                    background-color: var(--vendor-warm-light);
                    border-color: #F7D9A7;
                    color: #7C2D12;
                }

                .vendor-theme .form-control,
                .vendor-theme .form-select {
                    background-color: var(--vendor-surface);
                    border-color: var(--vendor-border);
                    color: var(--vendor-text);
                }

                .vendor-theme .form-control:focus,
                .vendor-theme .form-select:focus {
                    border-color: #8FBFA5;
                    box-shadow: 0 0 0 0.2rem rgba(29, 111, 66, 0.15);
                }

                .vendor-theme .table thead th {
                    background-color: #F8F8F5;
                    color: var(--vendor-text);
                    border-bottom-color: var(--vendor-border);
                }

                .vendor-theme .table td,
                .vendor-theme .table th {
                    border-color: var(--vendor-border);
                }

                .vendor-theme .page-link {
                    color: var(--vendor-accent);
                    border-color: var(--vendor-border);
                }

                .vendor-theme .page-item.active .page-link {
                    background-color: var(--vendor-accent);
                    border-color: var(--vendor-accent);
                }
            </style>
        @endif
    @endauth
</body>
</html>

