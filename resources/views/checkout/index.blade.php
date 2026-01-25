@extends('layouts.base')

@section('title', 'Checkout')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Checkout</h2>
        
        @if($groupedCart->count() > 0)
            <form action="{{ route('checkout.process') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Customer Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="delivery_notes" class="form-label">Delivery Notes</label>
                                        <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items by Vendor -->
                        @foreach($groupedCart as $vendorId => $items)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        {{ $items->first()->vendor_name }}
                                        <small class="text-muted">({{ $items->count() }} items)</small>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Delivery Options -->
                                    <div class="mb-3">
                                        <label class="form-label">Delivery Method *</label>
                                        <div class="row">
                                            @if(isset($vendorServices[$vendorId]))
                                                @if($vendorServices[$vendorId]->weekend_pickup_enabled)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                   name="delivery_type_{{ $vendorId }}" 
                                                                   id="pickup_{{ $vendorId }}" 
                                                                   value="pickup" required>
                                                            <label class="form-check-label" for="pickup_{{ $vendorId }}">
                                                                🏪 Weekend Pickup
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($vendorServices[$vendorId]->weekday_delivery_enabled)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                   name="delivery_type_{{ $vendorId }}" 
                                                                   id="weekday_delivery_{{ $vendorId }}" 
                                                                   value="weekday_delivery" required>
                                                            <label class="form-check-label" for="weekday_delivery_{{ $vendorId }}">
                                                                🚚 Weekday Delivery
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($vendorServices[$vendorId]->weekend_delivery_enabled)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                   name="delivery_type_{{ $vendorId }}" 
                                                                   id="weekend_delivery_{{ $vendorId }}" 
                                                                   value="weekend_delivery" required>
                                                            <label class="form-check-label" for="weekend_delivery_{{ $vendorId }}">
                                                                🚚 Weekend Delivery
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="col-12">
                                                    <p class="text-muted">No delivery options available for this vendor</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Items Summary -->
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Qty</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $item)
                                                    <tr>
                                                        <td>{{ $item['product_name'] }}</td>
                                                        <td>₱{{ number_format($item['price_per_unit'], 2) }}</td>
                                                        <td>{{ $item['quantity'] }}</td>
                                                        <td>₱{{ number_format($item['price_per_unit'] * $item['quantity'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-primary">
                                                    <th colspan="3">Vendor Subtotal</th>
                                                    <th>₱{{ number_format($items->sum(function($item) { return $item['price_per_unit'] * $item['quantity']; }), 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Summary -->
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
                                    <strong class="text-primary fs-4">₱{{ number_format($groupedCart->sum(function($vendorItems) { 
                                        return $vendorItems->sum(function($item) { 
                                            return $item['price_per_unit'] * $item['quantity']; 
                                        }); 
                                    }) * 1.05, 2) }}</strong>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Place Order
                                    </button>
                                    <a href="{{ route('cart.view') }}" class="btn btn-outline-secondary">
                                        Back to Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-info text-center">
                <h5>Your cart is empty</h5>
                <p class="mb-3">Add items to your cart before checking out!</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Browse Shops</a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate that each vendor has a delivery option selected
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const vendorIds = @json($groupedCart->keys()->toArray());
            let allSelected = true;
            
            vendorIds.forEach(vendorId => {
                const deliveryOptions = document.querySelectorAll(`input[name="delivery_type_${vendorId}"]:checked`);
                if (deliveryOptions.length === 0) {
                    allSelected = false;
                }
            });
            
            if (!allSelected) {
                e.preventDefault();
                alert('Please select a delivery method for each vendor.');
            }
        });
    }
});
</script>
@endpush
