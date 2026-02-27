@extends('layouts.base')
@section('title', 'Browse Shops')
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

    /* ── Page ── */
    .page { padding: 28px; max-width: 1200px; margin: 0 auto; }

    /* ── Page Header ── */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .page-header h1 { font-size: 20px; font-weight: 600; }
    .page-header p  { font-size: 13px; color: var(--muted); margin-top: 2px; }

    .btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text);
        background: var(--surface);
        transition: background .15s, border-color .15s;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-ghost:hover { background: var(--bg); border-color: #ccc; }

    /* ── Filter Card ── */
    .filter-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 28px;
        overflow: hidden;
    }

    .filter-header {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg);
    }
    .filter-header-left {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
    }
    .filter-header-left svg { width: 14px; height: 14px; color: var(--muted); }
    .active-filters-count {
        font-size: 11px;
        font-weight: 600;
        background: var(--accent);
        color: #fff;
        padding: 1px 7px;
        border-radius: 99px;
    }

    .filter-body { padding: 20px; }

    .filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }
    .filter-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 14px;
        align-items: end;
    }

    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-label {
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .form-control,
    .form-select {
        height: 38px;
        padding: 0 12px;
        border: 1px solid var(--border);
        border-radius: 7px;
        font-family: 'DM Sans', sans-serif;
        font-size: 13.5px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        width: 100%;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237A7871' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }
    .form-control:focus,
    .form-select:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(29,111,66,.1);
    }
    .form-control:disabled,
    .form-select:disabled {
        background: var(--bg);
        color: var(--muted);
        cursor: not-allowed;
    }

    .filter-actions { display: flex; gap: 8px; }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 7px;
        font-family: 'DM Sans', sans-serif;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-primary:hover { background: var(--accent-dk); }

    /* ── Section Title ── */
    .section-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .section-title { font-size: 15px; font-weight: 600; }
    .section-sub   { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .section-link  { font-size: 12.5px; color: var(--accent); font-weight: 500; }

    /* ── Vendor Grid ── */
    .vendor-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 32px;
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
        object-fit: cover;
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
    .vendor-range { font-size: 12px; color: var(--muted); }
    .vendor-range strong { font-family: 'DM Mono', monospace; color: var(--text); font-size: 12px; }

    .btn-sm-green {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 14px;
        background: var(--accent);
        color: #fff;
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        align-self: flex-start;
        transition: background .15s;
        border: none;
        cursor: pointer;
    }
    .btn-sm-green:hover { background: var(--accent-dk); }

    /* ── Product Grid ── */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 28px;
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

    .product-thumb {
        height: 160px;
        background: var(--bg);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: var(--border);
        flex-shrink: 0;
    }
    .product-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .product-body {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
    }
    .product-name   { font-size: 13.5px; font-weight: 600; line-height: 1.3; }
    .product-vendor { font-size: 12px; color: var(--muted); }

    .product-cat {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--accent);
        background: var(--accent-lt);
        padding: 2px 8px;
        border-radius: 99px;
        align-self: flex-start;
        margin-top: 2px;
    }

    .product-desc { font-size: 12px; color: var(--muted); flex: 1; }

    /* Stock badges */
    .stock-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 99px;
        align-self: flex-start;
        margin-top: 2px;
    }
    .stock-pill .dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .stock-in      { background: var(--accent-lt); color: var(--accent-dk); }
    .stock-in .dot { background: var(--accent); }
    .stock-low      { background: var(--warm-lt); color: #92400e; }
    .stock-low .dot { background: var(--warm); }
    .stock-back      { background: #E0E7FF; color: #3730A3; }
    .stock-back .dot { background: #4F46E5; }
    .stock-out      { background: #FEE2E2; color: #7F1D1D; }
    .stock-out .dot { background: #DC2626; }

    .product-footer {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--border);
    }
    .price-row {
        display: flex;
        align-items: baseline;
        gap: 4px;
        margin-bottom: 10px;
    }
    .price-value { font-family: 'DM Mono', monospace; font-size: 15px; font-weight: 600; color: var(--accent); }
    .price-unit  { font-size: 12px; color: var(--muted); }

    /* Qty + Cart */
    .cart-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }

    .qty-control {
        display: flex;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: 7px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .qty-btn {
        width: 28px; height: 30px;
        background: var(--bg);
        border: none;
        cursor: pointer;
        font-size: 15px;
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .12s;
        font-family: inherit;
        line-height: 1;
    }
    .qty-btn:hover { background: var(--border); }
    .qty-input {
        width: 36px; height: 30px;
        border: none;
        border-left: 1px solid var(--border);
        border-right: 1px solid var(--border);
        text-align: center;
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        font-weight: 500;
        color: var(--text);
        background: var(--surface);
        outline: none;
        -moz-appearance: textfield;
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; }

    .btn-cart {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 0 12px;
        height: 30px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 7px;
        font-family: 'DM Sans', sans-serif;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
        white-space: nowrap;
    }
    .btn-cart:hover { background: var(--accent-dk); }
    .btn-cart.backorder { background: #4F46E5; }
    .btn-cart.backorder:hover { background: #3730A3; }
    .btn-cart:disabled { background: var(--border); color: var(--muted); cursor: not-allowed; }

    .btn-row { display: flex; gap: 7px; }
    .btn-view {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 10px;
        border: 1px solid var(--border);
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 500;
        color: var(--text);
        background: var(--surface);
        transition: background .15s, border-color .15s;
        text-decoration: none;
    }
    .btn-view:hover { background: var(--bg); border-color: #ccc; }
    .btn-shop {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 10px;
        background: var(--accent-lt);
        color: var(--accent-dk);
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s;
    }
    .btn-shop:hover { background: #d0ecda; }

    /* ── Empty State ── */
    .empty-state {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 48px 24px;
        text-align: center;
        color: var(--muted);
    }
    .empty-state .empty-icon { font-size: 40px; margin-bottom: 12px; }
    .empty-state h5 { font-size: 15px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
    .empty-state p  { font-size: 13.5px; }

    /* ── Responsive ── */
    @media (max-width: 1100px) {
        .product-grid  { grid-template-columns: repeat(3, 1fr); }
        .filter-grid   { grid-template-columns: 2fr 1fr 1fr; }
        .filter-grid-2 { grid-template-columns: 1fr 1fr auto; }
    }
    @media (max-width: 860px) {
        .vendor-grid   { grid-template-columns: repeat(2, 1fr); }
        .product-grid  { grid-template-columns: repeat(2, 1fr); }
        .filter-grid   { grid-template-columns: 1fr 1fr; }
        .filter-grid-2 { grid-template-columns: 1fr 1fr auto; }
    }
    @media (max-width: 560px) {
        .page         { padding: 16px; }
        .vendor-grid  { grid-template-columns: 1fr; }
        .product-grid { grid-template-columns: 1fr; }
        .filter-grid  { grid-template-columns: 1fr; }
        .filter-grid-2 { grid-template-columns: 1fr; }
    }
</style>

<div class="page">

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1>Browse Shops</h1>
            <p>Explore vendors and products from AANI Market</p>
        </div>
        <a href="{{ route('home') }}" class="btn-ghost">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:13px;height:13px"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Home
        </a>
    </div>

    {{-- ── Filters ── --}}
    <div class="filter-card">
        <div class="filter-header">
            <div class="filter-header-left">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filters & Search
                @if(request()->hasAny(['search','service','category','price_min','price_max','product_sort_by']))
                    <span class="active-filters-count">Active</span>
                @endif
            </div>
        </div>
        <div class="filter-body">
            <form method="GET" action="{{ route('shop.index') }}">
                <div class="filter-grid">
                    <div class="form-group">
                        <label class="form-label" for="search">Search</label>
                        <input class="form-control" type="text" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Search shops or products…">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="section">Section</label>
                        <select class="form-select" id="section" name="section" disabled>
                            <option>All Sections</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="service">Service</label>
                        <select class="form-select" id="service" name="service">
                            <option value="">All Services</option>
                            <option value="weekend_pickup"    {{ request('service') == 'weekend_pickup'    ? 'selected' : '' }}>Weekend Pickup</option>
                            <option value="weekday_delivery"  {{ request('service') == 'weekday_delivery'  ? 'selected' : '' }}>Weekday Delivery</option>
                            <option value="weekend_delivery"  {{ request('service') == 'weekend_delivery'  ? 'selected' : '' }}>Weekend Delivery</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="product_sort_by">Sort Products</label>
                        <select class="form-select" id="product_sort_by" name="product_sort_by">
                            <option value="created_at" {{ request('product_sort_by') == 'created_at' ? 'selected' : '' }}>Newest First</option>
                            <option value="price_low"  {{ request('product_sort_by') == 'price_low'  ? 'selected' : '' }}>Price: Low → High</option>
                            <option value="price_high" {{ request('product_sort_by') == 'price_high' ? 'selected' : '' }}>Price: High → Low</option>
                            <option value="name"       {{ request('product_sort_by') == 'name'       ? 'selected' : '' }}>Name A–Z</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="category">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->category_name }}" {{ request('category') == $category->category_name ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="price_min">Min Price (₱)</label>
                        <input class="form-control" type="number" id="price_min" name="price_min" value="{{ request('price_min') }}" placeholder="0" min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="price_max">Max Price (₱)</label>
                        <input class="form-control" type="number" id="price_max" name="price_max" value="{{ request('price_max') }}" placeholder="9999" min="0" step="0.01">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">&nbsp;</label>
                        <div class="filter-actions">
                            <button type="submit" class="btn-primary">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                Apply Filters
                            </button>
                            <a href="{{ route('shop.index') }}" class="btn-ghost">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Featured Vendors ── --}}
    @if($featuredVendors->count() > 0)
    <div class="section-header">
        <div>
            <div class="section-title">Featured Vendors</div>
            <div class="section-sub">{{ $featuredVendors->count() }} vendors</div>
        </div>
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
                    <div class="vendor-count">{{ $vendor->product_count ?? 0 }} products</div>
                </div>
            </div>
            @if($vendor->business_description)
                <div class="vendor-desc">{{ Str::limit($vendor->business_description, 100) }}</div>
            @endif
            @if($vendor->min_price || $vendor->max_price)
                <div class="vendor-range">
                    Price range: <strong>₱{{ number_format($vendor->min_price ?? 0, 2) }} – ₱{{ number_format($vendor->max_price ?? 0, 2) }}</strong>
                </div>
            @endif
            <a href="{{ route('shop.show', $vendor->id) }}" class="btn-sm-green">Visit Shop</a>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Products ── --}}
    <div class="section-header">
        <div>
            <div class="section-title">Products</div>
            <div class="section-sub">{{ $products->count() }} result{{ $products->count() !== 1 ? 's' : '' }}</div>
        </div>
    </div>

    @if($products->count() > 0)
    <div class="product-grid">
        @foreach($products as $product)
        @php
            $tracked   = $product->track_stock;
            $qty       = $product->stock_quantity ?? 0;
            $minStock  = $product->minimum_stock ?? 0;
            $backorder = $product->allow_backorder ?? false;

            $inStock   = !$tracked || $qty > 0;
            $isLow     = $tracked && $qty > 0 && $qty <= $minStock;
            $isBack    = $tracked && $qty == 0 && $backorder;
            $isOut     = $tracked && $qty == 0 && !$backorder;
            $maxQty    = $qty ?: 99;
        @endphp
        <div class="product-card">
            <div class="product-thumb">
                @if($product->product_image_url)
                    <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}">
                @else
                    🛍
                @endif
            </div>
            <div class="product-body">
                <div class="product-name">{{ $product->product_name }}</div>
                <div class="product-vendor">{{ $product->business_name }}</div>

                @if($product->category_name)
                    <span class="product-cat">{{ $product->category_name }}</span>
                @endif

                @if($product->description)
                    <div class="product-desc">{{ Str::limit($product->description, 55) }}</div>
                @endif

                @if($tracked)
                    @if($isOut)
                        <span class="stock-pill stock-out"><span class="dot"></span>Out of stock</span>
                    @elseif($isBack)
                        <span class="stock-pill stock-back"><span class="dot"></span>Backorder</span>
                    @elseif($isLow)
                        <span class="stock-pill stock-low"><span class="dot"></span>Low — {{ $qty }} left</span>
                    @else
                        <span class="stock-pill stock-in"><span class="dot"></span>In stock — {{ $qty }} available</span>
                    @endif
                @endif

                <div class="product-footer">
                    <div class="price-row">
                        <span class="price-value">₱{{ number_format($product->price_per_unit, 2) }}</span>
                        <span class="price-unit">/ {{ $product->unit_type }}</span>
                    </div>

                    @if(!$isOut)
                        <div class="cart-row">
                            <div class="qty-control">
                                <button class="qty-btn" type="button" onclick="changeQty({{ $product->id }}, -1, {{ $maxQty }})">−</button>
                                <input class="qty-input" type="number" id="quantityInput-{{ $product->id }}" value="1" min="1" max="{{ $maxQty }}">
                                <button class="qty-btn" type="button" onclick="changeQty({{ $product->id }}, 1, {{ $maxQty }})">+</button>
                            </div>
                            @if($isBack)
                                <button class="btn-cart backorder" type="button" onclick="addToCart({{ $product->id }})">🕐 Backorder</button>
                            @else
                                <button class="btn-cart" type="button" onclick="addToCart({{ $product->id }})">🛒 Add</button>
                            @endif
                        </div>
                    @else
                        <button class="btn-cart" type="button" disabled style="width:100%;margin-bottom:8px;">Out of Stock</button>
                    @endif

                    <div class="btn-row">
                        <a href="{{ route('shop.product', $product->id) }}" class="btn-view">View</a>
                        <a href="{{ route('shop.show', $product->vendor_id) }}" class="btn-shop">Shop</a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h5>No Products Found</h5>
        <p>Try adjusting your filters or clearing your search to see more products.</p>
    </div>
    @endif

</div>

@push('scripts')
<script>
    function changeQty(id, delta, max) {
        const input = document.getElementById('quantityInput-' + id);
        if (!input) return;
        input.value = Math.max(1, Math.min(max, parseInt(input.value) + delta));
    }

    function addToCart(productId) {
        const input = document.getElementById('quantityInput-' + productId);
        const qty   = input ? parseInt(input.value) : 1;
        window.location.href = `/cart/add/${productId}?quantity=${qty}`;
    }
</script>
@endpush

@endsection
