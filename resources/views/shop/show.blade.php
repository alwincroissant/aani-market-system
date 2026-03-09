@extends('layouts.base')

@section('title', $vendor->business_name . ' - Shop')

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

    body {
        font-family: 'DM Sans', sans-serif !important;
        background: var(--bg) !important;
        color: var(--text) !important;
    }

    .shop-wrapper {
        background: var(--bg);
        padding: 32px 28px;
        min-height: 100vh;
    }

    .shop-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* ── Vendor Header Block ── */
    .vendor-header {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 36px;
        box-shadow: var(--shadow);
        position: relative;
    }

    .vendor-banner {
        height: 240px;
        background: linear-gradient(135deg, var(--accent-lt) 0%, var(--warm-lt) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .vendor-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .vendor-info-block {
        display: grid;
        grid-template-columns: 140px 1fr 280px;
        gap: 32px;
        align-items: start;
        padding: 32px;
        position: relative;
    }

    .vendor-logo {
        width: 140px;
        height: 140px;
        border-radius: 12px;
        overflow: hidden;
        background: var(--accent-lt);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 60px;
        margin-top: -70px;
        border: 4px solid var(--surface);
        box-shadow: var(--shadow);
    }

    .vendor-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .vendor-details h2 {
        font-family: 'DM Sans', sans-serif;
        font-size: 28px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 8px;
    }

    .vendor-details p {
        font-size: 14px;
        color: var(--muted);
        margin-bottom: 16px;
        line-height: 1.6;
    }

    .vendor-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .vendor-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        background: var(--accent-lt);
        color: var(--accent-dk);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .vendor-contact {
        background: var(--accent-lt);
        border-radius: 10px;
        padding: 20px;
    }

    .contact-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--accent-dk);
        letter-spacing: .05em;
        margin-bottom: 12px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        font-size: 13px;
        color: var(--text);
    }

    /* ── Products Section ── */
    .products-section {
        margin-bottom: 48px;
    }

    .category-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--border);
    }

    .category-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text);
    }

    .category-count {
        font-size: 13px;
        color: var(--muted);
        font-weight: 500;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    /* ── Product Card ── */
    .product-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: box-shadow .2s, transform .2s;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .product-card:hover {
        box-shadow: 0 8px 32px rgba(0,0,0,.08);
        transform: translateY(-4px);
    }

    .product-image {
        width: 100%;
        height: 200px;
        background: var(--accent-lt);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 48px;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-body {
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .product-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .product-desc {
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 12px;
        line-height: 1.5;
        flex: 1;
    }

    .product-price {
        font-family: 'DM Mono', monospace;
        font-size: 16px;
        font-weight: 600;
        color: var(--accent);
        margin-bottom: 8px;
    }

    .product-unit {
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 12px;
    }

    .stock-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 12px;
        width: fit-content;
    }

    .stock-badge.in-stock   { background: var(--accent-lt); color: var(--accent-dk); }
    .stock-badge.low-stock  { background: var(--warm-lt); color: #92400e; }
    .stock-badge.backorder  { background: #E0E7FF; color: #3730A3; }
    .stock-badge.out-stock  { background: #FEE2E2; color: #7F1D1D; }

    .product-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }

    .quantity-input {
        width: 60px;
        padding: 8px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 13px;
        text-align: center;
        transition: border-color .15s;
        outline: none;
    }
    .quantity-input:focus { border-color: var(--accent); }
    .quantity-input.over-stock { border-color: #DC2626; }

    /* Stock error */
    .stock-error {
        display: none;
        align-items: center;
        gap: 5px;
        font-size: 11.5px;
        color: #DC2626;
        margin-top: 6px;
    }
    .stock-error.visible { display: flex; }
    .stock-error svg { flex-shrink: 0; }

    .btn-add-cart {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 12px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
        text-decoration: none;
    }

    .btn-add-cart:hover:not(:disabled) { background: var(--accent-dk); }
    .btn-add-cart:disabled,
    .btn-add-cart.disabled {
        background: var(--muted);
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-backorder {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 12px;
        background: #EFF6FF;
        color: #3730A3;
        border: 1px solid #BFDBFE;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }
    .btn-backorder:hover:not(:disabled) { background: #DBEAFE; }
    .btn-backorder:disabled { opacity: 0.5; cursor: not-allowed; }

    /* ── Empty State ── */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: var(--surface);
        border-radius: 12px;
        border: 1px dashed var(--border);
    }

    .empty-state-icon { font-size: 48px; margin-bottom: 16px; }

    .empty-state h3 {
        color: var(--text);
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .empty-state p { color: var(--muted); font-size: 14px; }

    /* ── Store Closed Banner ── */
    .store-closed-banner {
        background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
        border: 1px solid #F87171;
        border-radius: 12px;
        padding: 24px 32px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .store-closed-banner .closed-icon { font-size: 40px; flex-shrink: 0; }
    .store-closed-banner .closed-text h3 {
        font-size: 18px; font-weight: 600; color: #991B1B; margin-bottom: 4px;
    }
    .store-closed-banner .closed-text p {
        font-size: 14px; color: #B91C1C; margin: 0;
    }
    .product-card.store-closed { opacity: 0.6; pointer-events: none; }

    @media (max-width: 768px) {
        .vendor-info-block { grid-template-columns: 1fr; }
        .vendor-logo { margin-top: 0; width: 100px; height: 100px; }
        .products-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
    }
</style>

<div class="shop-wrapper">
    <div class="shop-container">

        <!-- Vendor Header -->
        <div class="vendor-header">
            <div class="vendor-banner">
                @if($vendor->banner_url)
                    <img src="{{ asset('storage/' . $vendor->banner_url) }}" alt="{{ $vendor->business_name }}">
                @else
                    <div style="font-size: 80px;">🏪</div>
                @endif
            </div>

            <div class="vendor-info-block">
                <div class="vendor-logo">
                    @if($vendor->logo_url)
                        <img src="{{ asset('storage/' . $vendor->logo_url) }}" alt="{{ $vendor->business_name }}">
                    @else
                        🛒
                    @endif
                </div>

                <div class="vendor-details">
                    <h2>{{ $vendor->business_name }}</h2>
                    @if($vendor->business_description)
                        <p>{{ $vendor->business_description }}</p>
                    @endif

                    <div class="vendor-badges">
                        @if($vendor->weekend_pickup_enabled)
                            <span class="vendor-badge">🏪 Weekend Pickup</span>
                        @endif
                        @if($vendor->weekday_delivery_enabled)
                            <span class="vendor-badge">🚚 Weekday Delivery</span>
                        @endif
                        @if($vendor->weekend_delivery_enabled)
                            <span class="vendor-badge">🚚 Weekend Delivery</span>
                        @endif
                    </div>
                </div>

                <div class="vendor-contact">
                    <div class="contact-label">📞 Contact</div>
                    <div class="contact-item">
                        <span>{{ $vendor->contact_phone ?? 'N/A' }}</span>
                    </div>
                    <div class="contact-item">
                        <span>{{ $vendor->contact_email ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(!$vendor->is_live)
        <div class="store-closed-banner">
            <div class="closed-icon">🔒</div>
            <div class="closed-text">
                <h3>Store is Closed</h3>
                <p>This vendor's store is currently closed. You can browse their products but ordering is not available at this time.</p>
            </div>
        </div>
        @endif

        <!-- Products by Category -->
        @if($groupedProducts->count() > 0)
            @foreach($groupedProducts as $categoryName => $products)
                <div class="products-section">
                    <div class="category-header">
                        <h3 class="category-title">{{ $categoryName }}</h3>
                        <span class="category-count">{{ $products->count() }} items</span>
                    </div>

                    <div class="products-grid">
                        @foreach($products as $product)
                        @php
                            $maxQty  = ($product->track_stock && $product->stock_quantity > 0)
                                       ? $product->stock_quantity
                                       : 99;
                            $cartObj = session('cart');
                            $cartQty = ($cartObj && isset($cartObj->items[$product->id]))
                                       ? $cartObj->items[$product->id]['qty']
                                       : 0;
                        @endphp
                        <div class="product-card">
                            <div class="product-image">
                                @if($product->product_image_url)
                                    <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}">
                                @else
                                    📦
                                @endif
                            </div>

                            <div class="product-body">
                                <h4 class="product-name">{{ $product->product_name }}</h4>

                                @if($product->description)
                                    <p class="product-desc">{{ Str::limit($product->description, 70) }}</p>
                                @endif

                                @if($product->track_stock)
                                    @php
                                        $inStock     = $product->stock_quantity > 0;
                                        $isLow       = $inStock && $product->stock_quantity <= $product->minimum_stock;
                                        $isBackorder = !$inStock && $product->allow_backorder;
                                    @endphp
                                    <div class="stock-badge @if(!$inStock && !$isBackorder) out-stock @elseif($isBackorder) backorder @elseif($isLow) low-stock @else in-stock @endif">
                                        @if($inStock)
                                            @if($isLow)
                                                ⚠️ Low stock — {{ $product->stock_quantity }} left
                                            @else
                                                ✓ In stock — {{ $product->stock_quantity }} available
                                            @endif
                                        @elseif($isBackorder)
                                            🕐 Backorder available
                                        @else
                                            ✕ Out of stock
                                        @endif
                                    </div>
                                @endif

                                <div class="product-price">₱{{ number_format($product->price_per_unit, 2) }}</div>
                                <div class="product-unit">/ {{ $product->unit_type }}</div>

                                <div class="product-actions">
                                    @if(!$vendor->is_live)
                                        <button class="btn-add-cart disabled" disabled>🔒 Store Closed</button>
                                    @else
                                    @auth
                                        @if(auth()->user()->role === 'customer')
                                            @if($product->track_stock && $product->stock_quantity > 0)
                                                <input type="number" class="quantity-input"
                                                       value="1" min="1" max="{{ $maxQty }}"
                                                       id="qty_{{ $product->id }}"
                                                       oninput="validateQty({{ $product->id }}, {{ $maxQty }}, {{ $cartQty }})">
                                                <button class="btn-add-cart"
                                                        id="cartBtn_{{ $product->id }}"
                                                        onclick="addToCart({{ $product->id }}, {{ $maxQty }}, {{ $cartQty }})">
                                                    🛒 Add
                                                </button>
                                            @elseif($product->track_stock && $product->stock_quantity == 0)
                                                @if($product->allow_backorder)
                                                    <input type="number" class="quantity-input"
                                                           value="1" min="1" max="99"
                                                           id="qty_{{ $product->id }}"
                                                           oninput="validateQty({{ $product->id }}, 99, 0)">
                                                    <button class="btn-backorder"
                                                            id="cartBtn_{{ $product->id }}"
                                                            onclick="addToCart({{ $product->id }}, 99, 0)">
                                                        🕐 Backorder
                                                    </button>
                                                @else
                                                    <button class="btn-add-cart disabled" disabled>Out of Stock</button>
                                                @endif
                                            @else
                                                <input type="number" class="quantity-input"
                                                       value="1" min="1" max="99"
                                                       id="qty_{{ $product->id }}"
                                                       oninput="validateQty({{ $product->id }}, 99, 0)">
                                                <button class="btn-add-cart"
                                                        id="cartBtn_{{ $product->id }}"
                                                        onclick="addToCart({{ $product->id }}, 99, 0)">
                                                    🛒 Add
                                                </button>
                                            @endif
                                        @else
                                            <button class="btn-add-cart disabled" disabled>Sign up to order</button>
                                        @endif
                                    @else
                                        <button class="btn-add-cart" onclick="window.location.href='{{ route('auth.login') }}'">
                                            🔐 Login
                                        </button>
                                    @endauth
                                    @endif
                                </div>

                                {{-- Stock error shown below the actions row --}}
                                <div class="stock-error" id="stockError_{{ $product->id }}">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    <span id="stockErrorMsg_{{ $product->id }}"></span>
                                </div>

                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>No Products Available</h3>
                <p>This vendor hasn't added any products yet. Please check back later.</p>
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
    function validateQty(id, max, cartQty) {
        const input    = document.getElementById('qty_' + id);
        const errorEl  = document.getElementById('stockError_' + id);
        const errorMsg = document.getElementById('stockErrorMsg_' + id);
        const cartBtn  = document.getElementById('cartBtn_' + id);

        if (!input) return true;

        let val = parseInt(input.value, 10);
        if (isNaN(val) || val < 1) { val = 1; input.value = val; }

        const remaining = max - cartQty;
        const overStock = remaining <= 0 || val > remaining;

        input.classList.toggle('over-stock', overStock);

        if (remaining <= 0) {
            errorMsg.textContent = `You already have the maximum ${max} unit${max !== 1 ? 's' : ''} in your cart.`;
            errorEl.classList.add('visible');
            if (cartBtn) cartBtn.disabled = true;
        } else if (val > remaining) {
            errorMsg.textContent = `You have ${cartQty} in your cart. Only ${remaining} more unit${remaining !== 1 ? 's' : ''} can be added.`;
            errorEl.classList.add('visible');
            if (cartBtn) cartBtn.disabled = true;
        } else {
            errorEl.classList.remove('visible');
            if (cartBtn) cartBtn.disabled = false;
        }

        return !overStock;
    }

    function addToCart(productId, max, cartQty) {
        if (!validateQty(productId, max, cartQty)) return;
        const input = document.getElementById('qty_' + productId);
        const quantity = input ? parseInt(input.value) : 1;
        window.location.href = `/cart/add/${productId}?quantity=${quantity}`;
    }
</script>
@endpush