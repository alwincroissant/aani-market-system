@extends('layouts.base')

@section('title', 'Shopping Cart')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Shopping Cart</h2>
        
        @if($groupedCart->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    <form action="{{ route('checkout.index') }}" method="POST" id="cartCheckoutForm">
                        @csrf
                        @foreach($groupedCart as $vendorId => $items)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        {{ $vendorServices[$vendorId]->business_name }}
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
                                                    <th style="width: 40px;">
                                                        <input type="checkbox" class="form-check-input" onclick="toggleVendorSelection({{ $vendorId }}, this.checked)">
                                                    </th>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $index => $item)
                                                    @php
                                                        // Find the actual cart key by matching the item data
                                                        $cart = Session::get('cart', []);
                                                        $cartKey = null;
                                                        foreach ($cart as $key => $cartItem) {
                                                            if ($cartItem['product_id'] == $item['product_id'] && $cartItem['vendor_id'] == $item['vendor_id']) {
                                                                $cartKey = $key;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox"
                                                                   class="form-check-input cart-item-checkbox vendor-{{ $vendorId }}"
                                                                   name="selected_items[]"
                                                                   value="{{ $cartKey }}"
                                                                   data-price="{{ $item['price_per_unit'] }}"
                                                                   data-quantity="{{ $item['quantity'] }}"
                                                                   onchange="recalculateSelectedTotals()">
                                                        </td>
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
                                                            <button class="btn btn-sm btn-danger" type="button" onclick="removeFromCart('{{ $item['product_id'] }}', '{{ $item['vendor_id'] }}')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-primary">
                                                    <th colspan="4">Vendor Subtotal</th>
                                                    <th>₱{{ number_format($items->sum(function($item) { return $item['price_per_unit'] * $item['quantity']; }), 2) }}</th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </form>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal (selected):</span>
                                <strong id="selectedSubtotal">₱0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Market Fee (5%):</span>
                                <strong id="selectedMarketFee">₱0.00</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span><strong>Total (selected):</strong></span>
                                <strong class="text-primary" id="selectedTotal">₱0.00</strong>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" form="cartCheckoutForm" class="btn btn-primary">
                                    Proceed to Checkout (selected)
                                </button>
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
        fetch('/cart/destroy', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: productId,
                vendor_id: vendorId
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

function toggleVendorSelection(vendorId, isChecked) {
    const checkboxes = document.querySelectorAll('.vendor-' + vendorId);
    checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
    });
    recalculateSelectedTotals();
}

function recalculateSelectedTotals() {
    const selectedCheckboxes = document.querySelectorAll('.cart-item-checkbox:checked');
    let subtotal = 0;
    
    console.log('Selected checkboxes:', selectedCheckboxes.length);
    
    selectedCheckboxes.forEach(checkbox => {
        const price = parseFloat(checkbox.dataset.price);
        const quantity = parseInt(checkbox.dataset.quantity);
        subtotal += price * quantity;
        console.log('Item:', checkbox.value, 'Price:', price, 'Quantity:', quantity);
    });
    
    const marketFee = subtotal * 0.05;
    const total = subtotal + marketFee;
    
    console.log('Calculated totals - Subtotal:', subtotal, 'Market Fee:', marketFee, 'Total:', total);
    
    document.getElementById('selectedSubtotal').textContent = '₱' + subtotal.toFixed(2);
    document.getElementById('selectedMarketFee').textContent = '₱' + marketFee.toFixed(2);
    document.getElementById('selectedTotal').textContent = '₱' + total.toFixed(2);
}

// Initialize totals on page load
document.addEventListener('DOMContentLoaded', function() {
    recalculateSelectedTotals();
    
    // Add form submission debugging
    document.getElementById('cartCheckoutForm').addEventListener('submit', function(e) {
        const selectedCheckboxes = document.querySelectorAll('.cart-item-checkbox:checked');
        console.log('Form submission - Selected items:', selectedCheckboxes.length);
        
        selectedCheckboxes.forEach((checkbox, index) => {
            console.log(`Selected item ${index + 1}:`, checkbox.value);
        });
    });
});
</script>
@endpush
