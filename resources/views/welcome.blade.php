@extends('layouts.base')
@section('title', 'AANI Market - Fresh from the Market')
@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --bg:        #F5F4F0;
        --surface:   #FFFFFF;
        --border:    #E4E2DC;
        --text:      #1A1916;
        --muted:     #7A7871;
        --accent:    #1D6F42;
        --accent-lt: #EAF4EE;
        --accent-dk: #155232;
        --warm:      #D97706;
        --warm-lt:   #FEF3C7;
        --radius:    10px;
        --shadow:    0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.05);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        font-size: 14px;
        line-height: 1.6;
    }

    a { color: inherit; text-decoration: none; }

    /* ── Notice / Welcome bars ── */
    .notice-bar {
        background: var(--warm-lt);
        border-bottom: 1px solid #fde68a;
        padding: 10px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13px;
        font-weight: 500;
        color: #92400e;
    }
    .notice-bar a {
        padding: 5px 14px;
        background: var(--warm);
        color: #fff;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
    }
    .welcome-bar {
        background: var(--accent-lt);
        border-bottom: 1px solid #c6e8d4;
        padding: 10px 28px;
        font-size: 13.5px;
        color: var(--accent-dk);
    }
    .welcome-bar strong { font-weight: 600; }

    /* ── Page ── */
    .page { padding: 32px 28px; max-width: 1200px; margin: 0 auto; }

    /* ── Hero ── */
    .hero {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow);
        margin-bottom: 28px;
        overflow: hidden;
        position: relative;
    }

    /* Decorative background pattern */
    .hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 60% 70% at 15% 50%, #eaf4ee 0%, transparent 65%),
            radial-gradient(ellipse 40% 60% at 85% 20%, #fef9ee 0%, transparent 60%);
        pointer-events: none;
    }

    /* Subtle dot grid overlay */
    .hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, #D4D2CC 1px, transparent 1px);
        background-size: 28px 28px;
        opacity: .35;
        pointer-events: none;
    }

    .hero-inner {
        position: relative;
        z-index: 1;
        padding: 56px 60px 52px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--accent);
        background: var(--accent-lt);
        border: 1px solid #c6e8d4;
        padding: 5px 14px;
        border-radius: 99px;
        margin-bottom: 20px;
    }
    .hero-eyebrow-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--accent);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: .5; transform: scale(.7); }
    }

    .hero-title {
        font-size: 42px;
        font-weight: 600;
        line-height: 1.18;
        color: var(--text);
        letter-spacing: -.7px;
        max-width: 640px;
        margin-bottom: 16px;
    }
    .hero-title em {
        font-style: normal;
        color: var(--accent);
        position: relative;
    }
    /* Underline accent on "online" */
    .hero-title em::after {
        content: '';
        position: absolute;
        bottom: 2px; left: 0; right: 0;
        height: 3px;
        background: var(--accent-lt);
        border-radius: 2px;
        z-index: -1;
    }

    .hero-desc {
        font-size: 15.5px;
        color: var(--muted);
        max-width: 520px;
        margin-bottom: 28px;
        line-height: 1.7;
    }

    .hero-ctas {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 32px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 11px 22px;
        background: var(--accent);
        color: #fff;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background .15s, transform .1s;
        text-decoration: none;
    }
    .btn-primary:hover { background: var(--accent-dk); transform: translateY(-1px); }

    .btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 11px 22px;
        background: transparent;
        color: var(--text);
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid var(--border);
        cursor: pointer;
        transition: background .15s, border-color .15s;
        text-decoration: none;
    }
    .btn-ghost:hover { background: var(--bg); border-color: #ccc; }

    /* Stats strip inside hero */
    .hero-stats {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: rgba(255,255,255,.75);
        backdrop-filter: blur(8px);
        overflow: hidden;
    }
    .hero-stat {
        padding: 14px 28px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        border-right: 1px solid var(--border);
    }
    .hero-stat:last-child { border-right: none; }
    .hero-stat-value {
        font-family: 'DM Mono', monospace;
        font-size: 18px;
        font-weight: 600;
        color: var(--text);
        line-height: 1;
    }
    .hero-stat-label {
        font-size: 11.5px;
        color: var(--muted);
        white-space: nowrap;
    }

    /* ── Section header ── */
    .section-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .section-title { font-size: 15px; font-weight: 600; }
    .section-link  { font-size: 12.5px; color: var(--accent); font-weight: 500; }
    .section-link:hover { text-decoration: underline; }

    /* ── Quick Actions ── */
    .quick-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 32px;
    }

    .quick-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 22px;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: box-shadow .15s, transform .15s;
    }
    .quick-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); transform: translateY(-2px); }

    .quick-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .quick-icon.green { background: var(--accent-lt); }
    .quick-icon.amber { background: var(--warm-lt); }
    .quick-icon.slate { background: #F1F0F5; }

    .quick-card h5 { font-size: 14px; font-weight: 600; margin: 0; }
    .quick-card p  { font-size: 13px; color: var(--muted); margin: 0; flex: 1; }

    .btn-sm {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s;
        align-self: flex-start;
    }
    .btn-sm.green   { background: var(--accent); color: #fff; }
    .btn-sm.green:hover { background: var(--accent-dk); }
    .btn-sm.amber   { background: var(--warm); color: #fff; }
    .btn-sm.amber:hover { background: #b45309; }
    .btn-sm.outline { background: transparent; color: var(--text); border: 1px solid var(--border); }
    .btn-sm.outline:hover { background: var(--bg); }

    /* ── Vendor Grid ── */
    .vendor-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 14px;
    }

    .vendor-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: box-shadow .15s, transform .15s;
    }
    .vendor-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); transform: translateY(-2px); }

    .vendor-header { display: flex; align-items: center; gap: 12px; }
    .vendor-avatar {
        width: 44px; height: 44px;
        border-radius: 9px;
        background: var(--accent-lt);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        overflow: hidden;
    }
    .vendor-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .vendor-name  { font-size: 13.5px; font-weight: 600; }
    .vendor-count { font-size: 12px; color: var(--muted); }
    .vendor-desc  { font-size: 12.5px; color: var(--muted); flex: 1; line-height: 1.5; }

    /* ── Product Grid ── */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 14px;
    }

    .product-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        transition: box-shadow .15s, transform .15s;
    }
    .product-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); transform: translateY(-2px); }

    .product-img {
        height: 150px;
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .product-img img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .product-body {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
    }
    .product-name   { font-size: 13.5px; font-weight: 600; }
    .product-vendor { font-size: 12px; color: var(--muted); }
    .product-desc   { font-size: 12px; color: var(--muted); flex: 1; }

    .product-footer { margin-top: 10px; }
    .product-price { font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 600; color: var(--accent); }
    .product-unit  { font-size: 11.5px; color: var(--muted); margin-bottom: 8px; }
    .product-actions { display: flex; gap: 6px; }

    /* ── Center helper ── */
    .center { text-align: center; margin-top: 8px; margin-bottom: 32px; }

    /* ── Map ── */
    .map-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--shadow);
        margin-bottom: 36px;
    }
    .map-card-header {
        padding: 16px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .map-card-header .map-icon {
        width: 32px; height: 32px;
        background: var(--accent-lt);
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
    }
    .map-card-header .map-title { font-size: 14px; font-weight: 600; }
    .map-card-header .map-sub   { font-size: 12px; color: var(--muted); }

    .map-legend {
        padding: 12px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        background: var(--bg);
    }
    .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--muted); }
    .legend-dot  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

    #marketMap { height: 580px; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .hero-title   { font-size: 32px; }
        .hero-inner   { padding: 40px 32px 36px; }
        .hero-stats   { flex-wrap: wrap; }
        .vendor-grid  { grid-template-columns: repeat(2, 1fr); }
        .product-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .page         { padding: 20px 16px; }
        .hero-title   { font-size: 26px; }
        .hero-inner   { padding: 32px 20px 28px; }
        .hero-stat    { padding: 12px 18px; }
        .hero-stat-value { font-size: 15px; }
        .quick-grid   { grid-template-columns: 1fr; }
        .vendor-grid  { grid-template-columns: 1fr; }
        .product-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 400px) {
        .product-grid { grid-template-columns: 1fr; }
    }
</style>

{{-- Admin Banner --}}
@if(auth()->check() && auth()->user()->role === 'administrator' && request('view_site'))
<div class="notice-bar">
    <span>👁 You are viewing the public site as an administrator</span>
    <a href="{{ route('admin.dashboard.index') }}">← Back to Admin</a>
</div>
@endif

{{-- Customer Welcome --}}
@php
    $welcomeFirst = optional(optional(auth()->user())->customer)->first_name;
@endphp
@if(auth()->check() && auth()->user()->role === 'customer' && $welcomeFirst)
<div class="welcome-bar">
    👋 Welcome back, <strong>{{ $welcomeFirst }}</strong>! Ready to shop?
</div>
@endif

<div class="page">

    {{-- ── Hero ── --}}
    <div class="hero" style="margin-bottom: 28px;">
        <div class="hero-inner">

            <div class="hero-eyebrow">
                <span class="hero-eyebrow-dot"></span>
                AANI Wet Market
            </div>

            <h1 class="hero-title">
                Your neighborhood market,<br><em>now online.</em>
            </h1>

            <p class="hero-desc">
                Shop fresh seafood, meat, fruits, vegetables, and native favorites from trusted AANI vendors. Browse stalls like you do on-site and order from your phone.
            </p>

            <div class="hero-ctas">
                <a href="{{ route('shop.index') }}" class="btn-primary">
                    🛒 Start Shopping
                </a>
                <button class="btn-ghost" onclick="document.getElementById('market-map-section').scrollIntoView({behavior:'smooth'})">
                    🗺 Explore Map
                </button>
                @guest
                    <a href="{{ route('auth.register') }}" class="btn-ghost">Create Account</a>
                @endguest
            </div>

            {{-- Stats strip --}}
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-value">✓</span>
                    <span class="hero-stat-label">Verified Vendors</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">🏪</span>
                    <span class="hero-stat-label">Pickup & Delivery</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">🛒</span>
                    <span class="hero-stat-label">One Cart, All Stalls</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">🌿</span>
                    <span class="hero-stat-label">Fresh Daily</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Quick Actions ── --}}
    <div class="section-header">
        <div class="section-title">What are you looking for?</div>
    </div>
    <div class="quick-grid">
        <div class="quick-card">
            <div class="quick-icon green">🏪</div>
            <h5>Browse Shops</h5>
            <p>Explore all vendors and their products across the market</p>
            <a href="{{ route('shop.index') }}" class="btn-sm green">Browse Now</a>
        </div>
        <div class="quick-card">
            <div class="quick-icon slate">🗺</div>
            <h5>Market Map</h5>
            <p>Find vendors by location and explore the stall layout</p>
            <button class="btn-sm outline" onclick="document.getElementById('market-map-section').scrollIntoView({behavior:'smooth'})">View Map</button>
        </div>
        <div class="quick-card">
            <div class="quick-icon amber">🛒</div>
            @auth
                <h5>Your Cart</h5>
                <p>You're signed in. Review your cart and place orders anytime.</p>
                <a href="{{ route('getCart') }}" class="btn-sm amber">View Cart</a>
            @else
                <h5>Sign In to Order</h5>
                <p>Create an account or login to manage your cart and orders.</p>
                <a href="{{ route('auth.login') }}" class="btn-sm amber">Login</a>
            @endauth
        </div>
    </div>

    {{-- ── Featured Vendors ── --}}
    @if($featuredVendors->count() > 0)
    <div class="section-header">
        <div class="section-title">Featured Vendors</div>
        <a href="{{ route('shop.index') }}" class="section-link">View all →</a>
    </div>
    <div class="vendor-grid">
        @foreach($featuredVendors as $vendor)
        <div class="vendor-card">
            <div class="vendor-header">
                <div class="vendor-avatar">
                    @if($vendor->logo_url)
                        <img src="{{ asset('/storage/' . $vendor->logo_url) }}" alt="{{ $vendor->business_name }}">
                    @else
                        🏪
                    @endif
                </div>
                <div>
                    <div class="vendor-name">{{ $vendor->business_name }}</div>
                    <div class="vendor-count">{{ $vendor->product_count }} products</div>
                </div>
            </div>
            @if($vendor->business_description)
                <div class="vendor-desc">{{ Str::limit($vendor->business_description, 80) }}</div>
            @endif
            <a href="{{ route('shop.show', $vendor->id) }}" class="btn-sm green">Visit Shop</a>
        </div>
        @endforeach
    </div>
    <div class="center">
        <a href="{{ route('shop.index') }}" class="btn-ghost">View All Vendors</a>
    </div>
    @endif

    {{-- ── Recent Products ── --}}
    @if($featuredProducts->count() > 0)
    <div class="section-header">
        <div class="section-title">Recent Products</div>
        <a href="{{ route('shop.index') }}" class="section-link">Browse all →</a>
    </div>
    <div class="product-grid">
        @foreach($featuredProducts as $product)
        <div class="product-card">
            <div class="product-img">
                @if($product->product_image_url)
                    <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}">
                @else
                    🛍
                @endif
            </div>
            <div class="product-body">
                <div class="product-name">{{ $product->product_name }}</div>
                <div class="product-vendor">{{ $product->business_name }}</div>
                @if($product->description)
                    <div class="product-desc">{{ Str::limit($product->description, 55) }}</div>
                @endif
                <div class="product-footer">
                    <div class="product-price">₱{{ number_format($product->price_per_unit, 2) }}</div>
                    <div class="product-unit">per {{ $product->unit_type }}</div>
                    <div class="product-actions">
                        <a href="{{ route('shop.product', $product->id) }}" class="btn-sm outline" style="flex:1;justify-content:center;">View</a>
                        <a href="{{ route('shop.show', $product->vendor_id) }}" class="btn-sm green" style="flex:1;justify-content:center;">Shop</a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="center">
        <a href="{{ route('shop.index') }}" class="btn-ghost">Browse All Products</a>
    </div>
    @endif

    {{-- ── Market Map ── --}}
    <div id="market-map-section">
        <div class="section-header">
            <div class="section-title">Market Map</div>
        </div>
        <div class="map-card">
            <div class="map-card-header">
                <div class="map-icon">🗺</div>
                <div>
                    <div class="map-title">Interactive Stall Map</div>
                    <div class="map-sub">Click any stall to visit the vendor's shop</div>
                </div>
            </div>
            <div class="map-legend">
                <div class="legend-item"><span class="legend-dot" style="background:#228B22;"></span> Vegetables (VEG)</div>
                <div class="legend-item"><span class="legend-dot" style="background:#9400D3;"></span> Plants & Flowers (PLT)</div>
                <div class="legend-item"><span class="legend-dot" style="background:#8B4513;"></span> Meat & Fish (MF)</div>
                <div class="legend-item"><span class="legend-dot" style="background:#FF8C00;"></span> Food (FD)</div>
            </div>
            <div id="marketMap"></div>
        </div>
    </div>

</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapImageUrl = '{{ asset("storage/maps/marketmap.png") }}';
    let map, markers = [];

    map = L.map('marketMap', { crs: L.CRS.Simple, minZoom: -2, maxZoom: 2 });

    const img = new Image();
    img.onload = function() {
        const bounds = [[0, 0], [this.height, this.width]];
        L.imageOverlay(mapImageUrl, bounds).addTo(map);
        map.fitBounds(bounds);
        loadStalls();
    };
    img.onerror = function() {
        document.getElementById('marketMap').innerHTML = '<div style="padding:24px;color:#7A7871;font-size:13px;">Failed to load map image.</div>';
    };
    img.src = mapImageUrl;

    function loadStalls() {
        @foreach($stalls as $stall)
            @if($stall->x1 && $stall->y1 && $stall->x2 && $stall->y2)
                addStall(
                    {{ $stall->stall_id }},
                    {{ $stall->x1 }}, {{ $stall->y1 }}, {{ $stall->x2 }}, {{ $stall->y2 }},
                    '{{ $stall->stall_number }}',
                    {{ $stall->vendor_id }},
                    '{{ addslashes($stall->business_name) }}',
                    '{{ $stall->section_code ?? '' }}',
                    {{ $stall->weekend_pickup_enabled ? 'true' : 'false' }},
                    {{ $stall->weekday_delivery_enabled ? 'true' : 'false' }},
                    {{ $stall->weekend_delivery_enabled ? 'true' : 'false' }}
                );
            @endif
        @endforeach
    }

    function addStall(id, x1, y1, x2, y2, stallNumber, vendorId, businessName, sectionCode, weekendPickup, weekdayDelivery, weekendDelivery) {
        const colors = { 'VEG':'#228B22','PLT':'#9400D3','MF':'#8B4513','FD':'#FF8C00' };
        const color = colors[sectionCode] || '#1D6F42';
        const bounds = [[Math.min(y1,y2), Math.min(x1,x2)], [Math.max(y1,y2), Math.max(x1,x2)]];

        const rect = L.rectangle(bounds, {
            color, fillColor: color, weight: 2, opacity: 0.9, fillOpacity: 0.45
        }).addTo(map);

        const center = L.latLng((bounds[0][0]+bounds[1][0])/2, (bounds[0][1]+bounds[1][1])/2);

        const icon = L.divIcon({
            className: '',
            html: `<div style="background:#fff;border:2.5px solid ${color};border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:11px;box-shadow:0 2px 6px rgba(0,0,0,.2);cursor:pointer;">${stallNumber}</div>`,
            iconSize: [32,32], iconAnchor: [16,16]
        });
        const label = L.marker(center, { icon }).addTo(map);

        let services = [];
        if (weekendPickup)   services.push('🏪 Weekend Pickup');
        if (weekdayDelivery) services.push('🚚 Weekday Delivery');
        if (weekendDelivery) services.push('🚚 Weekend Delivery');

        const popup = `
            <div style="font-family:'DM Sans',sans-serif;min-width:190px;padding:2px;">
                <div style="font-weight:600;font-size:14px;margin-bottom:4px;">${businessName}</div>
                <div style="font-size:12px;color:#7A7871;margin-bottom:2px;">Stall ${stallNumber} &middot; Section ${sectionCode||'N/A'}</div>
                ${services.length ? `<div style="font-size:12px;color:#7A7871;margin-bottom:10px;">${services.join(' · ')}</div>` : '<div style="margin-bottom:10px;"></div>'}
                <a href="/shop/${vendorId}" style="display:inline-block;padding:6px 14px;background:#1D6F42;color:#fff;border-radius:6px;font-size:12.5px;font-weight:600;text-decoration:none;">Visit Shop →</a>
            </div>`;

        rect.bindPopup(popup);
        label.bindPopup(popup);

        [rect, label].forEach(el => {
            el.on('mouseover', () => rect.setStyle({ fillOpacity: 0.7 }));
            el.on('mouseout',  () => rect.setStyle({ fillOpacity: 0.45 }));
        });

        markers.push(rect, label);
    }
});
</script>
@endpush

@endsection