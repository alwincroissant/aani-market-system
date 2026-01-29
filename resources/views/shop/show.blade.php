@extends('layouts.base')

@section('title', $vendor->business_name . ' - Shop')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Vendor Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        @if($vendor->logo_url)
                            <img src="{{ asset($vendor->logo_url) }}" alt="{{ $vendor->business_name }}" class="img-fluid rounded">
                        @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                <i class="bi bi-shop text-white fs-2"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-10">
                        <h2>{{ $vendor->business_name }}</h2>
                        @if($vendor->description)
                            <p class="text-muted">{{ $vendor->description }}</p>
                        @endif
                        <div class="mt-2">
                            @if($vendor->weekend_pickup_enabled)
                                <span class="badge bg-success me-2">🏪 Weekend Pickup</span>
                            @endif
                            @if($vendor->weekday_delivery_enabled)
                                <span class="badge bg-info me-2">🚚 Weekday Delivery</span>
                            @endif
                            @if($vendor->weekend_delivery_enabled)
                                <span class="badge bg-primary me-2">🚚 Weekend Delivery</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products -->
        @if($groupedProducts->count() > 0)
            @foreach($groupedProducts as $categoryName => $products)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            {{ $categoryName }}
                            <span class="badge bg-secondary">{{ $products->count() }} items</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($products as $product)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        @if($product->product_image_url)
                                            <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="bi bi-image text-muted fs-1"></i>
                                            </div>
                                        @endif
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title">{{ $product->product_name }}</h6>
                                            @if($product->description)
                                                <p class="card-text text-muted small">{{ Str::limit($product->description, 80) }}</p>
                                            @endif
                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold text-primary">₱{{ number_format($product->price_per_unit, 2) }}</span>
                                                    <span class="text-muted small">/ {{ $product->unit_type }}</span>
                                                </div>
                                                @auth
                                                    @if(auth()->user()->role === 'customer')
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" value="1" min="1" max="99" id="quantity_{{ $product->id }}" oninput="this.value = Math.max(1, Math.min(99, parseInt(this.value) || 1))">
                                                            <button class="btn btn-primary" onclick="addToCart({{ $product->id }})">
                                                                <i class="bi bi-cart-plus"></i> Add to Cart
                                                            </button>
                                                        </div>
                                                    @else
                                                        <div class="alert alert-info p-2 small mt-2">
                                                            Only customer accounts can place orders.
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" value="1" min="1" max="99" id="quantity_{{ $product->id }}" oninput="this.value = Math.max(1, Math.min(99, parseInt(this.value) || 1))" disabled>
                                                        <button class="btn btn-primary" onclick="showSignupPrompt()">
                                                            <i class="bi bi-person-plus"></i> Sign Up to Order
                                                        </button>
                                                    </div>
                                                @endauth
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-info">
                <h5>No Products Available</h5>
                <p>This vendor hasn't added any products yet. Please check back later.</p>
            </div>
        @endif
    </div>
</div>

<!-- Cart Summary -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1000;">
    <div class="card" style="min-width: 250px;">
        <div class="card-body">
            <h6 class="card-title">Cart Summary</h6>
            <div id="cartSummary">
                <p class="mb-0">Loading...</p>
            </div>
            <a href="{{ route('cart.view') }}" class="btn btn-primary btn-sm w-100 mt-2">View Cart</a>
        </div>
    </div>
</div>
@endsection

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
    const quantityInput = document.getElementById(`quantity_${productId}`);
    const quantity = parseInt(quantityInput.value);
    
    // Validate quantity
    if (isNaN(quantity) || quantity < 1) {
        quantityInput.value = 1;
        return;
    }
    if (quantity > 99) {
        quantityInput.value = 99;
        return;
    }
    
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                <strong>Success!</strong> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            // Update cart badge in navbar
            updateCartBadge();
            
            // Remove alert after 3 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 3000);
        } else {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                <strong>Error!</strong> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 5000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error message
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3';
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            <strong>Error!</strong> Failed to add item to cart. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 3000);
    });
}

function updateCartBadge() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.navbar .badge.bg-danger');
            if (badge) {
                badge.textContent = data.count;
            }
        })
        .catch(error => console.error('Error updating cart badge:', error));
}

function updateCartSummary() {
    fetch('/cart/view')
    .then(response => response.text())
    .then(html => {
        // Parse the HTML to extract cart info
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const cartInfo = doc.getElementById('cartInfo');
        
        if (cartInfo) {
            document.getElementById('cartSummary').innerHTML = cartInfo.innerHTML;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Load cart summary on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartSummary();
});
</script>
@endpush
