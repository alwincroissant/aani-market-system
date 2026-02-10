<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AANI Market')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    @stack('styles')
    <style>
        .slide-alert { transform: translateY(-10px); opacity: 0; animation: slideIn 250ms cubic-bezier(.2,.8,.2,1) forwards; }
        .slide-alert.slide-out { animation: slideOut 250ms cubic-bezier(.2,.8,.2,1) forwards; }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateY(0); opacity: 1; } to { transform: translateY(-20px); opacity: 0; } }
        .badge-bounce { animation: badgeBounce 500ms cubic-bezier(.2,.7,.4,1) !important; }
        @keyframes badgeBounce { 0% { transform: translateY(0) scale(1); } 25% { transform: translateY(-12px) scale(1.1); } 50% { transform: translateY(0) scale(1); } 75% { transform: translateY(-6px) scale(1.05); } 100% { transform: translateY(0) scale(1); } }
    </style>
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
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard.index') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users.index') }}">
                                    <i class="bi bi-people"></i> Users
                                    @if($pendingVendorsCount ?? 0 > 0)
                                        <span class="badge bg-danger ms-1">{{ $pendingVendorsCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.map.index') }}">
                                    <i class="bi bi-map"></i> Map
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.orders.index') }}">
                                    <i class="bi bi-cart3"></i> Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('products.index') }}">
                                    <i class="bi bi-box"></i> Products
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-graph-up"></i> Reports
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.sales') }}">
                                        <i class="bi bi-cash"></i> Sales Report
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.attendance') }}">
                                        <i class="bi bi-calendar-check"></i> Attendance Report
                                    </a></li>
                                </ul>
                            </li>
                        @elseif(auth()->user()->role === 'vendor')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('vendor.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('products.index') }}">
                                    <i class="bi bi-box"></i> My Products
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile.index') }}">
                                    <i class="bi bi-shop"></i> Store Profile
                                </a>
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
                                <li><h6 class="dropdown-header">Account</h6></li>
                                <li><a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="bi bi-person"></i> My Profile
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.addresses') }}">
                                    <i class="bi bi-geo-alt"></i> Delivery Addresses
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.orders') }}">
                                    <i class="bi bi-clock-history"></i> Order History
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Live cart button for customers -->
    @auth
        @if(auth()->user()->role === 'customer')
            <div class="position-fixed" style="top: 80px; right: 20px; z-index: 1000;">
                <button id="liveCartButton" class="btn btn-primary rounded-circle p-3 shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border: none; cursor: pointer;" onclick="toggleCartPopup()">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.49.402H5a.5.5 0 0 1-.49-.402L3.61 3.5H1.5a.5.5 0 0 1-.5-.5zM3.14 4l.7 4H13.16l.7-4H3.14zM5 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm6 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                    </svg>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCountBadge">
                        {{ collect(Session::get('cart', []))->sum('quantity') }}
                    </span>
                </button>
                
                <!-- Cart Popup -->
                <div id="cartSummaryPopup" class="card position-absolute" style="display: none; top: 70px; right: 0; min-width: 280px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">Cart Summary</h6>
                        <div class="mb-3">
                            <small class="text-muted">Items in cart:</small>
                            <p class="mb-1"><strong id="cartItemCount">{{ collect(Session::get('cart', []))->sum('quantity') }}</strong></p>
                            <small class="text-muted">Total:</small>
                            <p class="mb-3"><strong id="cartTotalAmount">₱0.00</strong></p>
                        </div>
                        <a href="{{ auth()->check() ? route('cart.view') : route('auth.login') }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-bag"></i> View Full Cart
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <div class="container mt-4">
        @include('layouts.flash-messages')
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

    <!-- Live Cart Update Script -->
    <script>
        // Set badge text safely and animate when items are added
        function animateBadge() {
            const badge = document.getElementById('cartCountBadge');
            if (!badge) return;
            badge.classList.remove('badge-bounce');
            // Force reflow to restart the animation
            void badge.offsetWidth;
            badge.classList.add('badge-bounce');
            badge.addEventListener('animationend', function cb() {
                badge.classList.remove('badge-bounce');
                badge.removeEventListener('animationend', cb);
            });
        }

        function setCartCount(count) {
            const badge = document.getElementById('cartCountBadge');
            if (!badge) return;
            const prev = parseInt(badge.textContent) || 0;
            const newText = typeof count === 'number' ? String(count) : count;
            badge.textContent = newText;
            // animate when count increases
            try {
                const newVal = parseInt(newText) || 0;
                if (newVal > prev) animateBadge();
            } catch (e) {}
        }

        async function fetchCartCount() {
            try {
                const response = await fetch('{{ route("cart.count") }}');
                const data = await response.json();
                setCartCount(data.count);
                // keep a local cache for cross-tab sync
                try { localStorage.setItem('cart_count', data.count); } catch (e) {}
            } catch (err) {
                console.error('Failed to fetch cart count:', err);
            }
        }

        // BroadcastChannel for cross-tab realtime updates (fallbacks to storage event)
        if ('BroadcastChannel' in window) {
            window.cartChannel = new BroadcastChannel('aani_cart_channel');
            window.cartChannel.addEventListener('message', (ev) => {
                console.log('BroadcastChannel message received', ev.data);
                if (ev && ev.data && typeof ev.data.count !== 'undefined') {
                    setCartCount(ev.data.count);
                }
            });
        }

        // Listen for storage events from other tabs
        window.addEventListener('storage', function(e) {
            console.log('storage event', e.key, e.newValue);
            if (e.key === 'cart_count') {
                setCartCount(parseInt(e.newValue) || 0);
            }
        });

        // Listen for cart.add events from pages like product/show and update from server
        document.addEventListener('cart.add', function(e) {
            console.log('cart.add event received', e && e.detail);
            // If event provides a count, use it; otherwise fetch from server
            if (e && e.detail && typeof e.detail.count !== 'undefined') {
                setCartCount(e.detail.count);
                try { localStorage.setItem('cart_count', e.detail.count); } catch (err) {}
                if (window.cartChannel) window.cartChannel.postMessage({ count: e.detail.count });
            } else {
                fetchCartCount();
            }
        });

        // Initialize badge from local cache then validate with server
        (function initCartBadge() {
            try {
                const cached = parseInt(localStorage.getItem('cart_count'));
                console.log('initCartBadge cached value', cached);
                if (!isNaN(cached)) setCartCount(cached);
            } catch (e) {}
            // Always sync with server on page load
            fetchCartCount();
        })();

        // Toggle cart popup when badge is clicked
        window.toggleCartPopup = function() {
            const popup = document.getElementById('cartSummaryPopup');
            if (!popup) return;
            const isHidden = popup.style.display === 'none' || !popup.style.display;
            if (isHidden) {
                // Fetch cart summary when opening popup
                fetch('{{ route('cart.summary') }}')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('cartItemCount').textContent = data.count;
                        document.getElementById('cartTotalAmount').textContent = '₱' + data.total;
                        popup.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching cart summary:', error);
                        popup.style.display = 'block';
                    });
            } else {
                popup.style.display = 'none';
            }
        };

        // Close popup when clicking outside
        document.addEventListener('click', function(e) {
            const popup = document.getElementById('cartSummaryPopup');
            const cartBtn = document.getElementById('liveCartButton');
            if (popup && cartBtn && !popup.contains(e.target) && !cartBtn.contains(e.target)) {
                popup.style.display = 'none';
            }
        });
    </script>

    @if(request()->is('admin*'))
        <style>
            /* Admin theme styles omitted for brevity */
        </style>
    @endif
</body>
</html>
