@extends('layouts.base')

@section('title', $product->product_name)

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shop.index') }}">Browse Shops</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shop.show', $product->vendor_id) }}">{{ $product->business_name }}</a></li>
                <li class="breadcrumb-item active">{{ $product->product_name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        @if($product->product_image_url)
                            <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}" class="img-fluid rounded">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 300px;">
                                <i class="bi bi-image text-muted fs-1"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h3>{{ $product->product_name }}</h3>
                        <p class="text-muted">Sold by <a href="{{ route('shop.show', $product->vendor_id) }}">{{ $product->business_name }}</a></p>
                        @if($product->category_name)
                            <p><span class="badge bg-secondary">{{ $product->category_name }}</span></p>
                        @endif
                        <h4 class="text-primary">₱{{ number_format($product->price_per_unit, 2) }} / {{ $product->unit_type }}</h4>
                        
                        <!-- Stock Information -->
                        @if($product->track_stock)
                            @php
                                // Calculate stock status manually since we're using stdClass from DB query
                                $stockStatus = 'In stock';
                                $statusClass = 'bg-success';
                                if ($product->stock_quantity == 0) {
                                    $stockStatus = $product->allow_backorder ? 'Backorder' : 'Out of stock';
                                    $statusClass = $product->allow_backorder ? 'bg-info' : 'bg-danger';
                                } elseif ($product->stock_quantity <= $product->minimum_stock) {
                                    $stockStatus = 'Low stock';
                                    $statusClass = 'bg-warning';
                                }
                            @endphp
                            <div class="mt-2">
                                <span class="badge {{ $statusClass }}">
                                    {{ $stockStatus }}: {{ $product->stock_quantity }} available
                                </span>
                                @if($product->stock_quantity <= $product->minimum_stock && $product->stock_quantity > 0)
                                    <div class="text-warning small mt-1">
                                        <i class="bi bi-exclamation-triangle"></i> Only {{ $product->stock_quantity }} left!
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if($product->description)
                            <div class="mt-3">
                                <h5>Description</h5>
                                <p>{{ $product->description }}</p>
                            </div>
                        @endif
                        <div class="mt-4">
                            @auth
                                @if(auth()->user()->role === 'customer')
                                    @php
                                        $isInStock = !$product->track_stock || ($product->stock_quantity > 0 || $product->allow_backorder);
                                    @endphp
                                    @if($isInStock)
                                        <div class="input-group">
                                            <input type="number" class="form-control" value="1" min="1" max="{{ $product->stock_quantity ?: 99 }}" id="quantity" oninput="this.value = Math.max(1, Math.min({{ $product->stock_quantity ?: 99 }}, parseInt(this.value) || 1))">
                                            <button class="btn btn-primary" onclick="addToCart({{ $product->id }})">
                                                <i class="bi bi-cart-plus"></i> Add to Cart
                                            </button>
                                        </div>
                                    @else
                                        <div class="alert alert-warning p-2 small mt-2">
                                            @if($product->allow_backorder)
                                                <i class="bi bi-clock"></i> Available for backorder
                                            @else
                                                <i class="bi bi-x-circle"></i> Out of stock
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-info p-2 small mt-2">
                                        Only customer accounts can place orders.
                                    </div>
                                @endif
                            @else
                                <div class="input-group">
                                    <input type="number" class="form-control" value="1" min="1" max="{{ $product->stock_quantity ?: 99 }}" id="quantity" oninput="this.value = Math.max(1, Math.min({{ $product->stock_quantity ?: 99 }}, parseInt(this.value) || 1))" disabled>
                                    <button class="btn btn-primary" onclick="showSignupPrompt()">
                                        <i class="bi bi-person-plus"></i> Sign Up to Order
                                    </button>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Shop Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($product->logo_url)
                        <img src="{{ asset('storage/' . $product->logo_url) }}" alt="{{ $product->business_name }}" class="rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 80px; height: 80px;">
                            <i class="bi bi-shop text-white fs-3"></i>
                        </div>
                    @endif
                    <h6 class="mb-1">{{ $product->business_name }}</h6>
                </div>

                @if($product->business_description)
                    <p class="text-muted small mb-3">{{ Str::limit($product->business_description, 120) }}</p>
                @endif

                @if($product->business_hours)
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> {{ $product->business_hours }}
                        </small>
                    </div>
                @endif

                @if($product->contact_phone)
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="bi bi-telephone"></i> {{ $product->contact_phone }}
                        </small>
                    </div>
                @endif

                @if($product->contact_email)
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-envelope"></i> {{ $product->contact_email }}
                        </small>
                    </div>
                @endif

                <div class="mb-3">
                    @if($product->weekend_pickup_enabled)
                        <span class="badge bg-success mb-1" style="font-size: 0.7rem;">🏪 Weekend Pickup</span><br>
                    @endif
                    @if($product->weekday_delivery_enabled)
                        <span class="badge bg-info mb-1" style="font-size: 0.7rem;">🚚 Weekday Delivery</span><br>
                    @endif
                    @if($product->weekend_delivery_enabled)
                        <span class="badge bg-primary mb-1" style="font-size: 0.7rem;">🚚 Weekend Delivery</span>
                    @endif
                </div>

                <a href="{{ route('shop.show', $product->vendor_id) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-shop"></i> View All Products
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showSignupPrompt() {
    const alert = document.createElement('div');
    alert.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3';
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        <strong>Sign Up Required!</strong> Create an account to add items to cart and place orders.
        <div class="mt-2">
            <a href="{{ route('auth.register') }}" class="btn btn-primary btn-sm me-2">
                <i class="bi bi-person-plus"></i> Sign Up
            </a>
            <a href="{{ route('auth.login') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    // Remove alert after 8 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 8000);
}

function addToCart(productId) {
    const quantityInput = document.getElementById('quantity');
    const quantity = parseInt(quantityInput.value);
    
    window.location.href = `/cart/add/${productId}?quantity=${quantity}`;
}
</script>
@endpush
@endsection
