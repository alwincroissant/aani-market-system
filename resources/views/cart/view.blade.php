@extends('layouts.base')

@section('title', 'Shopping Cart')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Shopping Cart</h2>
        
        @if($products && count($products) > 0)
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input" id="selectAll" checked>
                                            </th>
                                            <th>Product</th>
                                            <th>Shop</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $itemId => $item)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input item-checkbox" 
                                                           data-item-id="{{ $itemId }}" 
                                                           data-price="{{ $item['price'] }}"
                                                           checked>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $item['item']->product_image_url ?? '/images/default-product.jpg' }}" 
                                                             alt="{{ $item['item']->product_name }}" 
                                                             class="img-thumbnail me-3" 
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                        <div>
                                                            <div class="fw-semibold">{{ $item['item']->product_name }}</div>
                                                            <small class="text-muted">{{ $item['item']->unit_type }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($item['item']->vendor)
                                                            <span class="badge bg-info">{{ $item['item']->vendor->business_name }}</span>
                                                        @else
                                                            <span class="text-muted">Unknown Shop</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>₱{{ number_format($item['item']->price_per_unit, 2) }}</td>
                                                <td>
                                                    <div class="input-group" style="width: 120px;">
                                                        <a href="{{ route('reduceByOne', $itemId) }}" class="btn btn-outline-secondary btn-sm">
                                                            <i class="bi bi-dash"></i>
                                                        </a>
                                                        <input type="text" 
                                                               class="form-control text-center" 
                                                               value="{{ $item['qty'] }}" 
                                                               readonly>
                                                        <a href="{{ route('addToCart', $itemId) }}" class="btn btn-outline-secondary btn-sm">
                                                            <i class="bi bi-plus"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                                <td>₱{{ number_format($item['price'], 2) }}</td>
                                                <td>
                                                    <a href="{{ route('removeItem', $itemId) }}" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i> Remove
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal (selected):</span>
                                <strong id="selectedSubtotal">₱{{ number_format($totalPrice, 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping (Weekday Delivery):</span>
                                <strong>₱50.00</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span><strong>Total:</strong></span>
                                <strong class="text-primary" id="selectedTotal">₱{{ number_format($totalPrice + 50, 2) }}</strong>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary" onclick="proceedToCheckout()">
                                    Proceed to Checkout (selected)
                                </button>
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                    Continue Shopping
                                </a>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const selectedSubtotal = document.getElementById('selectedSubtotal');
    const selectedTotal = document.getElementById('selectedTotal');

    function updateTotals() {
        let subtotal = 0;
        
        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                subtotal += parseFloat(checkbox.dataset.price);
            }
        });
        
        // Calculate shipping based on delivery options
        let shipping = 0;
        // For now, default to ₱50 shipping (weekday delivery)
        // This will be updated when delivery options are selected in checkout
        shipping = 50.00;
        
        const total = subtotal + shipping;
        
        selectedSubtotal.textContent = `₱${subtotal.toFixed(2)}`;
        selectedTotal.textContent = `₱${total.toFixed(2)}`;
    }

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateTotals();
    });

    // Individual item checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update select all checkbox state
            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
            
            updateTotals();
        });
    });

    // Initial totals
    updateTotals();
});

function proceedToCheckout() {
    const selectedItems = [];
    const itemCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    
    if (itemCheckboxes.length === 0) {
        alert('Please select at least one item to checkout.');
        return;
    }
    
    itemCheckboxes.forEach(checkbox => {
        selectedItems.push(checkbox.dataset.itemId);
    });
    
    // Create form and submit with selected items
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('checkout.index') }}';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Add selected items
    const itemsInput = document.createElement('input');
    itemsInput.type = 'hidden';
    itemsInput.name = 'selected_items';
    itemsInput.value = JSON.stringify(selectedItems);
    form.appendChild(itemsInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
