@extends('layouts.base')
@section('title', $product->product_name)
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
    .page { padding: 28px; max-width: 1100px; margin: 0 auto; }

    /* ── Breadcrumb ── */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: var(--muted);
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .breadcrumb a { color: var(--accent); transition: opacity .15s; }
    .breadcrumb a:hover { opacity: .75; }
    .breadcrumb-sep { color: var(--border); }
    .breadcrumb-current { color: var(--muted); }

    /* ── Product Layout ── */
    .product-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }

    /* ── Product Main Card ── */
    .product-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .product-inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }

    /* Image Pane */
    .product-image-pane {
        background: var(--bg);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        min-height: 320px;
        padding: 32px;
    }

    .product-image-stage {
        width: 100%;
        max-width: 420px;
        height: 280px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px;
    }
    
    .product-main-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
    }
    
    .product-image-placeholder {
        font-size: 64px;
        opacity: .4;
    }

    /* Image Carousel Thumbnails */
    .product-image-carousel {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
        max-width: 100%;
        min-height: 64px;
        margin-top: 14px;
    }
    
    .carousel-thumb {
        width: 60px;
        height: 60px;
        border: 2px solid var(--border);
        border-radius: 6px;
        cursor: pointer;
        overflow: hidden;
        transition: border-color .15s;
        flex-shrink: 0;
    }
    
    .carousel-thumb:hover,
    .carousel-thumb.active {
        border-color: var(--accent);
    }
    
    .carousel-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Info Pane */
    .product-info-pane {
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .product-category {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--accent);
        background: var(--accent-lt);
        padding: 3px 10px;
        border-radius: 99px;
        margin-bottom: 14px;
        align-self: flex-start;
    }

    .product-name {
        font-size: 22px;
        font-weight: 600;
        line-height: 1.25;
        color: var(--text);
        margin-bottom: 6px;
        letter-spacing: -.3px;
    }

    .product-by {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 20px;
    }
    .product-by a { color: var(--accent); font-weight: 500; }
    .product-by a:hover { text-decoration: underline; }

    .product-price-row {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border);
    }
    .product-price {
        font-family: 'DM Mono', monospace;
        font-size: 28px;
        font-weight: 600;
        color: var(--accent);
    }
    .product-unit {
        font-size: 13px;
        color: var(--muted);
    }

    /* Stock Status */
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 99px;
        margin-bottom: 20px;
        align-self: flex-start;
    }
    .stock-badge.in-stock   { background: var(--accent-lt); color: var(--accent-dk); }
    .stock-badge.low-stock  { background: var(--warm-lt); color: #92400e; }
    .stock-badge.backorder  { background: #E0E7FF; color: #3730A3; }
    .stock-badge.out-stock  { background: #FEE2E2; color: #7F1D1D; }
    .stock-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    .stock-dot.green  { background: var(--accent); }
    .stock-dot.amber  { background: var(--warm); }
    .stock-dot.blue   { background: #4F46E5; }
    .stock-dot.red    { background: #DC2626; }

    .stock-note {
        font-size: 12px;
        color: var(--warm);
        margin-top: -14px;
        margin-bottom: 18px;
        font-weight: 500;
    }

    /* Description (inside main card, below image+info) */
    .product-description {
        padding: 24px 32px;
        border-top: 1px solid var(--border);
    }
    .product-description .desc-title {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 10px;
    }
    .product-description p {
        font-size: 14px;
        color: var(--text);
        line-height: 1.7;
    }

    /* Cart Actions */
    .cart-area {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: auto;
    }

    .qty-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .qty-label { font-size: 13px; color: var(--muted); font-weight: 500; min-width: 60px; }

    .qty-control {
        display: flex;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        background: var(--surface);
    }
    .qty-btn {
        width: 36px; height: 36px;
        background: var(--bg);
        border: none;
        cursor: pointer;
        font-size: 16px;
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s;
        font-family: inherit;
    }
    .qty-btn:hover { background: var(--border); }
    .qty-input {
        width: 48px;
        height: 36px;
        border: none;
        border-left: 1px solid var(--border);
        border-right: 1px solid var(--border);
        text-align: center;
        font-family: 'DM Mono', monospace;
        font-size: 14px;
        font-weight: 500;
        color: var(--text);
        background: var(--surface);
        outline: none;
        -moz-appearance: textfield;
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; }

    .btn-add-cart {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 20px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
        text-decoration: none;
        width: 100%;
    }
    .btn-add-cart:hover { background: var(--accent-dk); }
    .btn-add-cart.backorder { background: #4F46E5; }
    .btn-add-cart.backorder:hover { background: #3730A3; }
    .btn-add-cart:disabled,
    .btn-add-cart.disabled {
        background: var(--border);
        color: var(--muted);
        cursor: not-allowed;
    }

    /* ── Store Closed Banner ── */
    .store-closed-banner {
        background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
        border: 1px solid #F87171;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .store-closed-banner .closed-icon { font-size: 28px; flex-shrink: 0; }
    .store-closed-banner .closed-text h4 {
        font-size: 15px; font-weight: 600; color: #991B1B; margin-bottom: 2px;
    }
    .store-closed-banner .closed-text p {
        font-size: 13px; color: #B91C1C; margin: 0;
    }

    .btn-login {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 20px;
        background: transparent;
        color: var(--text);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, border-color .15s;
        text-decoration: none;
        width: 100%;
    }
    .btn-login:hover { background: var(--bg); border-color: #ccc; }

    .alert-info-box {
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #3730A3;
        line-height: 1.5;
    }

    /* ── Sidebar ── */
    .sidebar { display: flex; flex-direction: column; gap: 16px; }

    .shop-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .shop-card-top {
        padding: 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .shop-avatar-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .shop-avatar {
        width: 48px; height: 48px;
        border-radius: 10px;
        object-fit: cover;
        background: var(--accent-lt);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        overflow: hidden;
    }
    .shop-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .shop-name { font-size: 14px; font-weight: 600; }
    .shop-tag  { font-size: 12px; color: var(--muted); margin-top: 1px; }

    .shop-desc {
        font-size: 13px;
        color: var(--muted);
        line-height: 1.6;
    }

    .shop-card-meta {
        padding: 16px 24px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        border-bottom: 1px solid var(--border);
    }
    .meta-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 13px;
        color: var(--muted);
    }
    .meta-icon { font-size: 14px; flex-shrink: 0; margin-top: 1px; }
    .meta-text { line-height: 1.4; }

    .shop-card-action { padding: 16px 24px; }

    .btn-visit-shop {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 18px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text);
        background: var(--surface);
        transition: background .15s, border-color .15s;
        text-decoration: none;
        width: 100%;
    }
    .btn-visit-shop:hover { background: var(--bg); border-color: #ccc; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .product-layout  { grid-template-columns: 1fr; }
        .product-inner   { grid-template-columns: 1fr; }
        .product-image-pane { border-right: none; border-bottom: 1px solid var(--border); min-height: 220px; }
        .product-image-stage { height: 240px; max-width: 100%; }
    }
    @media (max-width: 600px) {
        .page { padding: 16px; }
    }
</style>

<div class="page">

    <div class="product-layout">

        {{-- ── Product Main Card ── --}}
        <div class="product-card">
            <div class="product-inner">

                {{-- Image --}}
                <div class="product-image-pane">
                    <div class="product-image-stage">
                        @if($allImages->count() > 0)
                            <img id="mainProductImage" class="product-main-image" src="{{ asset($allImages->first()->image_url) }}" alt="{{ $product->product_name }}">
                        @else
                            <div class="product-image-placeholder">📦</div>
                        @endif
                    </div>

                    @if($allImages->count() > 1)
                        <div class="product-image-carousel">
                            @foreach($allImages as $index => $img)
                                <div class="carousel-thumb {{ $index === 0 ? 'active' : '' }}" onclick="changeMainImage('{{ asset($img->image_url) }}', this)">
                                    <img src="{{ asset($img->image_url) }}" alt="Product image {{ $index + 1 }}">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="product-info-pane">

                    @if($product->category_name)
                        <span class="product-category">{{ $product->category_name }}</span>
                    @endif

                    <h1 class="product-name">{{ $product->product_name }}</h1>
                    <p class="product-by">Sold by <a href="{{ route('shop.show', $product->vendor_id) }}">{{ $product->business_name }}</a></p>

                    <div class="product-price-row">
                        <span class="product-price">₱{{ number_format($product->price_per_unit, 2) }}</span>
                        <span class="product-unit">/ {{ $product->unit_type }}</span>
                    </div>

                    {{-- Stock --}}
                    @if($product->track_stock)
                        @php
                            $inStock     = $product->stock_quantity > 0;
                            $isLow       = $inStock && $product->stock_quantity <= $product->minimum_stock;
                            $isBackorder = !$inStock && $product->allow_backorder;
                        @endphp

                        @if($inStock && !$isLow)
                            <span class="stock-badge in-stock"><span class="stock-dot green"></span> In stock &mdash; {{ $product->stock_quantity }} available</span>
                        @elseif($isLow)
                            <span class="stock-badge low-stock"><span class="stock-dot amber"></span> Low stock &mdash; {{ $product->stock_quantity }} left</span>
                        @elseif($isBackorder)
                            <span class="stock-badge backorder"><span class="stock-dot blue"></span> Available on backorder</span>
                        @else
                            <span class="stock-badge out-stock"><span class="stock-dot red"></span> Out of stock</span>
                        @endif

                        @if($isLow && $inStock)
                            <p class="stock-note">⚡ Only {{ $product->stock_quantity }} remaining — order soon!</p>
                        @endif
                    @endif

                    {{-- Cart --}}
                    <div class="cart-area">
                        @if(!$product->vendor_is_live)
                            <div class="store-closed-banner">
                                <div class="closed-icon">🔒</div>
                                <div class="closed-text">
                                    <h4>Store is Closed</h4>
                                    <p>This vendor's store is currently closed. Ordering is not available.</p>
                                </div>
                            </div>
                            <button class="btn-add-cart disabled" disabled>🔒 Store Closed</button>
                        @else
                        @auth
                            @if(auth()->user()->role === 'customer')
                                @php
                                    $isAvailable = !$product->track_stock || ($product->stock_quantity > 0 || $product->allow_backorder);
                                @endphp
                                @if($isAvailable)
                                    <div class="qty-row">
                                        <span class="qty-label">Quantity</span>
                                        <div class="qty-control">
                                            <button class="qty-btn" type="button" onclick="changeQty(-1)">−</button>
                                            <input class="qty-input" type="number" id="quantity" value="1" min="1" max="{{ $product->stock_quantity ?: 99 }}">
                                            <button class="qty-btn" type="button" onclick="changeQty(1)">+</button>
                                        </div>
                                    </div>
                                    @if($product->track_stock && $product->stock_quantity == 0 && $product->allow_backorder)
                                        <button class="btn-add-cart backorder" onclick="addToCart({{ $product->id }})">
                                            🕐 Place Backorder
                                        </button>
                                    @else
                                        <button class="btn-add-cart" onclick="addToCart({{ $product->id }})">
                                            🛒 Add to Cart
                                        </button>
                                    @endif
                                @else
                                    <button class="btn-add-cart disabled" disabled>Out of Stock</button>
                                @endif
                            @else
                                <div class="alert-info-box">
                                    Only customer accounts can place orders. Please create a customer account to order.
                                </div>
                            @endif
                        @else
                            <div class="qty-row">
                                <span class="qty-label">Quantity</span>
                                <div class="qty-control">
                                    <button class="qty-btn" disabled>−</button>
                                    <input class="qty-input" type="number" value="1" disabled>
                                    <button class="qty-btn" disabled>+</button>
                                </div>
                            </div>
                            <a href="{{ route('auth.login') }}" class="btn-login">🔐 Login to Order</a>
                        @endauth
                        @endif
                    </div>

                </div>
            </div>

            {{-- Description --}}
            @if($product->description)
            <div class="product-description">
                <div class="desc-title">Product Description</div>
                <p>{{ $product->description }}</p>
            </div>
            @endif
        </div>

        {{-- ── Sidebar ── --}}
        <aside class="sidebar">
            <div class="shop-card">
                <div class="shop-card-top">
                    <div class="shop-avatar-wrap">
                        <div class="shop-avatar">
                            @if($product->logo_url)
                                <img src="{{ asset('storage/' . $product->logo_url) }}" alt="{{ $product->business_name }}">
                            @else
                                🏪
                            @endif
                        </div>
                        <div>
                            <div class="shop-name">{{ $product->business_name }}</div>
                            <div class="shop-tag">Market Vendor</div>
                        </div>
                    </div>
                    @if($product->business_description)
                        <p class="shop-desc">{{ Str::limit($product->business_description, 120) }}</p>
                    @endif
                </div>

                @if($product->contact_phone || $product->contact_email || $product->business_hours)
                <div class="shop-card-meta">
                    @if($product->contact_phone)
                        <div class="meta-item">
                            <span class="meta-icon">📞</span>
                            <span class="meta-text">{{ $product->contact_phone }}</span>
                        </div>
                    @endif
                    @if($product->contact_email)
                        <div class="meta-item">
                            <span class="meta-icon">✉️</span>
                            <span class="meta-text">{{ $product->contact_email }}</span>
                        </div>
                    @endif
                    @if($product->business_hours)
                        <div class="meta-item">
                            <span class="meta-icon">🕐</span>
                            <span class="meta-text">{{ $product->business_hours }}</span>
                        </div>
                    @endif
                </div>
                @endif

                <div class="shop-card-action">
                    <a href="{{ route('shop.show', $product->vendor_id) }}" class="btn-visit-shop">
                        🏪 Visit Shop
                    </a>
                </div>
            </div>
        </aside>

    </div>
</div>

@push('scripts')
<script>
    const maxQty = {{ $product->stock_quantity ?: 99 }};

    function changeQty(delta) {
        const input = document.getElementById('quantity');
        if (!input) return;
        let val = parseInt(input.value) + delta;
        input.value = Math.max(1, Math.min(maxQty, val));
    }

    function addToCart(productId) {
        const qty = parseInt(document.getElementById('quantity').value) || 1;
        window.location.href = `/cart/add/${productId}?quantity=${qty}`;
    }

    function changeMainImage(imageUrl, thumbnailElement) {
        // Update main image
        const mainImage = document.getElementById('mainProductImage');
        if (mainImage) {
            mainImage.src = imageUrl;
        }
        
        // Update active thumbnail
        document.querySelectorAll('.carousel-thumb').forEach(thumb => {
            thumb.classList.remove('active');
        });
        if (thumbnailElement) {
            thumbnailElement.classList.add('active');
        }
    }
</script>
@endpush

@endsection
