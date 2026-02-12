@extends('layouts.base')

@section('title', 'Checkout')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Checkout</h2>
        
        @if($groupedCart && count($groupedCart) > 0)
            <form action="{{ route('checkout.process') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Order Items by Vendor -->
                        @foreach($groupedCart as $vendorId => $items)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        {{ $vendorInfo[$vendorId]->business_name }}
                                        <small class="text-muted">({{ count($items) }} items)</small>
                                        @if(isset($vendorInfo[$vendorId]))
                                            <div class="float-end">
                                                @if($vendorInfo[$vendorId]->weekend_pickup_enabled)
                                                    <span class="badge bg-success me-1">🏪 Pickup</span>
                                                @endif
                                                @if($vendorInfo[$vendorId]->weekday_delivery_enabled)
                                                    <span class="badge bg-info me-1">🚚 Weekday Delivery</span>
                                                @endif
                                                @if($vendorInfo[$vendorId]->weekend_delivery_enabled)
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $itemId => $item)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $item['item']->product_image_url ?? '/images/default-product.jpg' }}" 
                                                                     alt="{{ $item['item']->product_name }}" 
                                                                     class="img-thumbnail me-3" 
                                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                                <div>
                                                                    <div class="fw-semibold">{{ $item['item']->product_name }}</div>
                                                                    <small class="text-muted">{{ $item['item']->unit_type }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>₱{{ number_format($item['item']->price_per_unit, 2) }}</td>
                                                        <td>{{ $item['qty'] }}</td>
                                                        <td>₱{{ number_format($item['price'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-primary">
                                                    <th colspan="3">Vendor Subtotal</th>
                                                    <th>₱{{ number_format(array_sum(array_column($items, 'price')), 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Customer Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Customer information will be collected during checkout.</p>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Delivery Address</h5>
                            </div>
                            <div class="card-body">
                                @if($addresses && $addresses->count() > 0)
                                    <div class="mb-3">
                                        <label class="form-label">Select Address</label>
                                        @foreach($addresses as $address)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="selected_address" 
                                                       id="address_{{ $address->id }}" value="{{ $address->id }}" 
                                                       {{ $address->is_default ? 'checked' : '' }}>
                                                <label class="form-check-label d-block" for="address_{{ $address->id }}">
                                                    <div class="border rounded p-3 {{ $address->is_default ? 'border-primary' : '' }}">
                                                        <div class="fw-semibold">{{ $address->recipient_name }}</div>
                                                        <div class="text-muted small">{{ $address->recipient_phone }}</div>
                                                        <div>{{ $address->address_line }}, {{ $address->city }}</div>
                                                        @if($address->province)
                                                            <div>{{ $address->province }}, {{ $address->postal_code }}</div>
                                                        @endif
                                                        @if($address->is_default)
                                                            <span class="badge bg-primary mt-1">Default</span>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mb-3">
                                        <a href="{{ route('profile.addresses') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-plus"></i> Add New Address
                                        </a>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <p class="mb-2">You don't have any saved addresses.</p>
                                        <a href="{{ route('profile.addresses') }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus"></i> Add Address
                                        </a>
                                    </div>
                                @endif
                                
                                <div class="mb-3">
                                    <label for="delivery_notes" class="form-label">Delivery Notes</label>
                                    <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="3" 
                                              placeholder="Special delivery instructions, landmarks, etc."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Options -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Delivery Options & Customer Details</h5>
                            </div>
                            <div class="card-body">
                                <!-- Customer Details -->
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                               value="{{ auth()->user()->customer ? (auth()->user()->customer->first_name && auth()->user()->customer->last_name ? auth()->user()->customer->first_name . ' ' . auth()->user()->customer->last_name : auth()->user()->name) : auth()->user()->name }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone" 
                                               value="{{ auth()->user()->customer ? auth()->user()->customer->phone : '' }}">
                                    </div>
                                </div>
                                
                                <!-- Delivery Options by Vendor -->
                                @foreach($groupedCart as $vendorId => $items)
                                    <div class="mb-3">
                                        <h6 class="text-primary">{{ $vendorInfo[$vendorId]->business_name }}</h6>
                                        @if(isset($vendorInfo[$vendorId]))
                                            <div class="form-check">
                                                @if($vendorInfo[$vendorId]->weekend_pickup_enabled)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="delivery_type_{{ $vendorId }}" 
                                                               id="pickup_{{ $vendorId }}" value="weekend_pickup" checked>
                                                        <label class="form-check-label" for="pickup_{{ $vendorId }}">
                                                            🏪 Weekend Pickup (Free)
                                                        </label>
                                                    </div>
                                                @endif
                                                @if($vendorInfo[$vendorId]->weekday_delivery_enabled)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="delivery_type_{{ $vendorId }}" 
                                                               id="weekday_delivery_{{ $vendorId }}" value="weekday_delivery">
                                                        <label class="form-check-label" for="weekday_delivery_{{ $vendorId }}">
                                                            🚚 Weekday Delivery (₱50)
                                                        </label>
                                                    </div>
                                                @endif
                                                @if($vendorInfo[$vendorId]->weekend_delivery_enabled)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="delivery_type_{{ $vendorId }}" 
                                                               id="weekend_delivery_{{ $vendorId }}" value="weekend_delivery">
                                                        <label class="form-check-label" for="weekend_delivery_{{ $vendorId }}">
                                                            🚚 Weekend Delivery (₱75)
                                                        </label>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-muted">No delivery options available for this vendor.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Order Summary -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong>₱{{ number_format($totalPrice, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Delivery Fees:</span>
                                    <strong id="deliveryFees">₱0</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <span><strong>Total:</strong></span>
                                    <strong class="text-primary" id="totalAmount">₱{{ number_format($totalPrice, 2) }}</strong>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Place Order
                                    </button>
                                    <a href="{{ route('getCart') }}" class="btn btn-outline-secondary">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate delivery fees when delivery options change
    function calculateDeliveryFees() {
        let deliveryFees = 0;
        
        @foreach($groupedCart as $vendorId => $items)
            const deliveryType{{ $vendorId }} = document.querySelector('input[name="delivery_type_{{ $vendorId }}"]:checked');
            if (deliveryType{{ $vendorId }}) {
                if (deliveryType{{ $vendorId }}.value === 'weekday_delivery') {
                    deliveryFees += 50;
                } else if (deliveryType{{ $vendorId }}.value === 'weekend_delivery') {
                    deliveryFees += 75;
                }
            }
        @endforeach
        
        const subtotal = {{ $totalPrice }};
        const marketFee = subtotal * 0.05;
        const total = subtotal + marketFee + deliveryFees;
        
        document.getElementById('deliveryFees').textContent = `₱${deliveryFees.toFixed(2)}`;
        document.getElementById('totalAmount').textContent = `₱${total.toFixed(2)}`;
    }
    
    // Add event listeners to delivery options
    @foreach($groupedCart as $vendorId => $items)
        const deliveryOptions{{ $vendorId }} = document.querySelectorAll('input[name="delivery_type_{{ $vendorId }}"]');
        deliveryOptions{{ $vendorId }}.forEach(option => {
            option.addEventListener('change', calculateDeliveryFees);
        });
    @endforeach
    
    // Initial calculation
    calculateDeliveryFees();
});
</script>
@endpush
@endsection
