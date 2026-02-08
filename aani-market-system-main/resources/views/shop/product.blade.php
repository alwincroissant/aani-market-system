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
                        @if($product->description)
                            <div class="mt-3">
                                <h5>Description</h5>
                                <p>{{ $product->description }}</p>
                            </div>
                        @endif
                        <div class="mt-4">
                            @auth
                                @if(auth()->user()->role === 'customer')
                                    <div class="input-group">
                                        <input type="number" class="form-control" value="1" min="1" max="99" id="quantity" oninput="this.value = Math.max(1, Math.min(99, parseInt(this.value) || 1))">
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
                                    <input type="number" class="form-control" value="1" min="1" max="99" id="quantity" oninput="this.value = Math.max(1, Math.min(99, parseInt(this.value) || 1))" disabled>
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
                <div class="d-flex align-items-center mb-3">
                    @if($product->logo_url)
                        <img src="{{ asset($product->logo_url) }}" alt="{{ $product->business_name }}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-shop text-white small"></i>
                        </div>
                    @endif
                    <div>
                        <h6 class="mb-0">{{ $product->business_name }}</h6>
                        <a href="{{ route('shop.show', $product->vendor_id) }}" class="btn btn-sm btn-outline-primary">
                            View All Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showSignupPrompt() {
    const alert = document.createElement('div');
    alert.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3 slide-alert';
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
    
            // Remove alert after 8 seconds with slide-out animation
    setTimeout(() => {
        if (!alert.parentNode) return;
        alert.classList.add('slide-out');
        alert.addEventListener('animationend', function handler() {
            if (alert.parentNode) alert.parentNode.removeChild(alert);
            alert.removeEventListener('animationend', handler);
        });
    }, 8000);
}

function addToCart(productId) {
    const quantityInput = document.getElementById('quantity');
    const quantity = parseInt(quantityInput.value);

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
            // show popup
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 slide-alert';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                <strong>Success!</strong> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);

            // Remove alert after 750ms (250ms slide-in + 500ms stay) with slide-out animation
            setTimeout(() => {
                if (!alert.parentNode) return;
                alert.classList.add('slide-out');
                alert.addEventListener('animationend', function handler() {
                    if (alert.parentNode) alert.parentNode.removeChild(alert);
                    alert.removeEventListener('animationend', handler);
                });
            }, 750);

            // reset quantity                                                                       
            quantityInput.value = 1;

            // <-- DISPATCH EVENT TO UPDATE BADGE
            try {
                const badge = document.getElementById('cartCountBadge');
                let newCount = null;
                if (badge) {
                    const current = parseInt(badge.textContent) || 0;
                    newCount = current + quantity;
                    console.log('shop addToCart local update newCount', newCount);
                    if (typeof setCartCount === 'function') {
                        setCartCount(newCount);
                    } else {
                        badge.textContent = newCount;
                    }
                    try { localStorage.setItem('cart_count', newCount); } catch (e) {}
                    if (window.cartChannel) window.cartChannel.postMessage({ count: newCount });
                    document.dispatchEvent(new CustomEvent('cart.add', { detail: { count: newCount } }));
                } else {
                    console.log('shop addToCart: badge not found, dispatching generic cart.add');
                    document.dispatchEvent(new Event('cart.add'));
                }
            } catch (e) {
                console.error('shop addToCart local update failed', e);
                document.dispatchEvent(new Event('cart.add'));
            }
        } else {
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 end-0 m-3 slide-alert';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                <strong>Notice:</strong> ${data.message || 'Could not add item to cart.'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);

            // Remove alert after 3 seconds with slide-out animation
            setTimeout(() => {
                if (!alert.parentNode) return;
                alert.classList.add('slide-out');
                alert.addEventListener('animationend', function handler() {
                    if (alert.parentNode) alert.parentNode.removeChild(alert);
                    alert.removeEventListener('animationend', handler);
                });
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3 slide-alert';
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            <strong>Error!</strong> Failed to add item to cart. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        // Remove alert after 3 seconds with slide-out animation
        setTimeout(() => {
            if (!alert.parentNode) return;
            alert.classList.add('slide-out');
            alert.addEventListener('animationend', function handler() {
                if (alert.parentNode) alert.parentNode.removeChild(alert);
                alert.removeEventListener('animationend', handler);
            });
        }, 3000);
    });
}
</script>
@endpush
@endsection
