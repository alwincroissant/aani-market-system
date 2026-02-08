@extends('layouts.base')

@section('title', 'Product Details')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Product Details</h4>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($product->product_image_url)
                        <div class="col-md-4 mb-3">
                            <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}" class="img-fluid rounded">
                        </div>
                    @endif
                    <div class="{{ $product->product_image_url ? 'col-md-8' : 'col-md-12' }}">
                        <h5>{{ $product->product_name }}</h5>
                        <p class="text-muted">Category: 
                            <span class="badge" style="background-color: {{ $product->color_code ?? '#6c757d' }}">
                                {{ $product->category_name }}
                            </span>
                        </p>
                        <p><strong>Price:</strong> ₱{{ number_format($product->price_per_unit, 2) }} per {{ $product->unit_type }}</p>
                        @if($product->description)
                            <p><strong>Description:</strong></p>
                            <p>{{ $product->description }}</p>
                        @endif
                        <p>
                            <strong>Status:</strong> 
                            @if($product->is_available)
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-danger">Not Available</span>
                            @endif
                        </p>
                        <p><strong>Vendor:</strong> {{ $product->business_name }}</p>

                        {{-- Add to Cart snippet --}}
                        @if($product->is_available)
                        <div class="mt-3">
                            <div class="input-group mb-2" style="max-width: 150px;">
                                <input type="number" id="quantityInput" class="form-control" value="1" min="1" max="99">
                            </div>
                            <button type="button" class="btn btn-primary" onclick="addToCart({{ $product->id }})">
                                Add to Cart
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addToCart(productId) {
    const quantity = parseInt(document.getElementById('quantityInput').value);

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
            // in-page slide alert (short)
            const alertEl = document.createElement('div');
            alertEl.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 slide-alert';
            alertEl.style.zIndex = '9999';
            alertEl.innerHTML = `
                <strong>Success!</strong> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertEl);

            setTimeout(() => {
                if (!alertEl.parentNode) return;
                alertEl.classList.add('slide-out');
                alertEl.addEventListener('animationend', function handler() {
                    if (alertEl.parentNode) alertEl.parentNode.removeChild(alertEl);
                    alertEl.removeEventListener('animationend', handler);
                });
            }, 750);

            // Try to update badge immediately and broadcast to other tabs
            try {
                const badge = document.getElementById('cartCountBadge');
                let newCount = null;
                if (badge) {
                    const current = parseInt(badge.textContent) || 0;
                    newCount = current + quantity;
                    console.log('addToCart local update newCount', newCount);
                    if (typeof setCartCount === 'function') {
                        setCartCount(newCount);
                    } else {
                        badge.textContent = newCount;
                    }
                    try { localStorage.setItem('cart_count', newCount); } catch (e) {}
                    if (window.cartChannel) window.cartChannel.postMessage({ count: newCount });
                    document.dispatchEvent(new CustomEvent('cart.add', { detail: { count: newCount } }));
                } else {
                    console.log('addToCart: badge not found, dispatching generic cart.add');
                    // Fallback: let base script fetch updated count from server
                    document.dispatchEvent(new Event('cart.add'));
                }
            } catch (e) {
                console.error('addToCart local update failed', e);
                document.dispatchEvent(new Event('cart.add'));
            }

        } else {
            const alertEl = document.createElement('div');
            alertEl.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 end-0 m-3 slide-alert';
            alertEl.style.zIndex = '9999';
            alertEl.innerHTML = `
                <strong>Notice:</strong> ${data.message || 'Could not add item to cart.'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertEl);

            setTimeout(() => {
                if (!alertEl.parentNode) return;
                alertEl.classList.add('slide-out');
                alertEl.addEventListener('animationend', function handler() {
                    if (alertEl.parentNode) alertEl.parentNode.removeChild(alertEl);
                    alertEl.removeEventListener('animationend', handler);
                });
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush
