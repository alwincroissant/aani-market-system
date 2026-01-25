@extends('layouts.base')

@section('title', 'Shopping Cart')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Shopping Cart</h2>
        
        @if($groupedCart->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    @foreach($groupedCart as $vendorId => $items)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    {{ $items->first()->vendor_name }}
                                    @if(isset($vendorServices[$vendorId]))
                                        <div class="float-end">
                                            @if($vendorServices[$vendorId]->weekend_pickup_enabled)
                                                <span class="badge bg-success me-1">🏪 Pickup</span>
                                            @endif
                                            @if($vendorServices[$vendorId]->weekday_delivery_enabled)
                                                <span class="badge bg-info me-1">🚚 Weekday Delivery</span>
                                            @endif
                                            @if($vendorServices[$vendorId]->weekend_delivery_enabled)
                                                <span class="badge bg-primary">🚚 Weekend Delivery</span>
                                            @endif
                                        </div>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $cartKey => $item)
                                                <tr>
                                                    <td>{{ $item['product_name'] }}</td>
                                                    <td>₱{{ number_format($item['price_per_unit'], 2) }}</td>
                                                    <td>
                                                        <input type="number" 
                                                               class="form-control form-control-sm" 
                                                               style="width: 80px;"
                                                               value="{{ $item['quantity'] }}" 
                                                               min="1" 
                                                               max="99"
                                                               onchange="updateCartItem('{{ $item['product_id'] }}', '{{ $item['vendor_id'] }}', this.value)">
                                                    </td>
                                                    <td>₱{{ number_format($item['price_per_unit'] * $item['quantity'], 2) }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-danger" onclick="removeFromCart('{{ $item['product_id'] }}', '{{ $item['vendor_id'] }}')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="3">Vendor Subtotal</th>
                                                <th>₱{{ number_format($items->sum(function($item) { return $item['price_per_unit'] * $item['quantity']; }), 2) }}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>₱{{ number_format($groupedCart->sum(function($vendorItems) { 
                                    return $vendorItems->sum(function($item) { 
                                        return $item['price_per_unit'] * $item['quantity']; 
                                    }); 
                                }), 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Market Fee (5%):</span>
                                <strong>₱{{ number_format($groupedCart->sum(function($vendorItems) { 
                                    return $vendorItems->sum(function($item) { 
                                        return $item['price_per_unit'] * $item['quantity']; 
                                    }); 
                                }) * 0.05, 2) }}</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span><strong>Total:</strong></span>
                                <strong class="text-primary">₱{{ number_format($groupedCart->sum(function($vendorItems) { 
                                    return $vendorItems->sum(function($item) { 
                                        return $item['price_per_unit'] * $item['quantity']; 
                                    }); 
                                }) * 1.05, 2) }}</strong>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                                    Proceed to Checkout
                                </a>
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                    Continue Shopping
                                </a>
                                <button class="btn btn-outline-danger" onclick="clearCart()">
                                    Clear Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                <h5>Your cart is empty</h5>
                <p class="mb-3">Start shopping to add items to your cart!</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Browse Shops</a>
            </div>
        @endif
    </div>
</div>

<!-- Hidden div for cart info (used by shop page) -->
<div id="cartInfo" class="d-none">
    <p class="mb-0"><strong>{{ $groupedCart->sum('quantity') }}</strong> items</p>
    <p class="mb-0">₱{{ number_format($groupedCart->sum(function($vendorItems) { 
        return $vendorItems->sum(function($item) { 
            return $item['price_per_unit'] * $item['quantity']; 
        }); 
    }), 2) }}</p>
</div>
@endsection

@push('scripts')
<script>
function updateCartItem(productId, vendorId, quantity) {
    fetch('/cart/update', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: productId,
            vendor_id: vendorId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function removeFromCart(productId, vendorId) {
    if (confirm('Are you sure you want to remove this item?')) {
        updateCartItem(productId, vendorId, 0);
    }
}

function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        fetch('/cart/clear', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
</script>
@endpush
