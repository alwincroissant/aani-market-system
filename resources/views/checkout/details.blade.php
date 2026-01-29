@extends('layouts.base')

@section('title', 'Checkout Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4">Checkout Details</h2>
            
            <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                @csrf
                
                <!-- Delivery Method Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Delivery Method</h5>
                    </div>
                    <div class="card-body">
                        @foreach($groupedCart as $vendorId => $items)
                            <div class="mb-4">
                                <h6>{{ $vendorServices[$vendorId]->business_name }}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="delivery_type_{{ $vendorId }}" class="form-label">Choose Delivery Method *</label>
                                            <select class="form-select" name="delivery_type_{{ $vendorId }}" id="delivery_type_{{ $vendorId }}">
                                                <option value="">Select delivery method...</option>
                                                @if($vendorServices[$vendorId]->weekend_pickup_enabled)
                                                    <option value="weekend_pickup">🏪 Weekend Pickup (FREE)</option>
                                                @endif
                                                @if($vendorServices[$vendorId]->weekday_delivery_enabled)
                                                    <option value="weekday_delivery">🚚 Weekday Delivery (₱150.00)</option>
                                                @endif
                                                @if($vendorServices[$vendorId]->weekend_delivery_enabled)
                                                    <option value="weekend_delivery">🚚 Weekend Delivery (₱75.00)</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="card mb-4 d-none" id="deliveryInfoCard">
                    <div class="card-header">
                        <h5 class="mb-0">Delivery Information</h5>
                    </div>
                    <div class="card-body">
                        <!-- Delivery Address Selection -->
                        <div class="mb-3">
                            <label class="form-label">Delivery Address *</label>
                            @if($addresses->count() > 0)
                                <div class="mb-2">
                                    @foreach($addresses as $address)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="delivery_address_id" 
                                                   id="address_{{ $address->id }}" value="{{ $address->id }}" 
                                                   @if($address->is_default) checked @endif required>
                                            <label class="form-check-label" for="address_{{ $address->id }}">
                                                <strong>{{ $address->address_line }}</strong><br>
                                                <strong>Recipient:</strong> {{ $address->recipient_name ?? 'Not specified' }}<br>
                                                <strong>Contact:</strong> {{ $address->recipient_phone ?? 'Not specified' }}<br>
                                                {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                                                @if($address->is_default) <span class="badge bg-primary">Default</span> @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                    <i class="bi bi-plus-circle"></i> Add New Address
                                </button>
                            @else
                                <p class="text-muted">No delivery addresses found. Please add an address first.</p>
                                <a href="{{ route('profile.addresses') }}" class="btn btn-primary">Add Delivery Address</a>
                            @endif
                        </div>

                        <!-- Delivery Instructions -->
                        <div class="mb-3">
                            <label for="delivery_instructions" class="form-label">Delivery Instructions</label>
                            <textarea class="form-control" id="delivery_instructions" name="delivery_instructions" 
                                      rows="3" placeholder="Special instructions for delivery..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pickup Information -->
                <div class="card mb-4 d-none" id="pickupInfoCard">
                    <div class="card-header">
                        <h5 class="mb-0">Pickup Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Pickup Location</h6>
                            <p class="mb-2"><strong>AANI Weekend Market</strong></p>
                            <p class="mb-1">1-A Palayan Rd, FTI-ARCA South, Taguig, 1630 Metro Manila, Philippines</p>
                            <p class="mb-1">Located within the Arca South estate (formerly the FTI Complex), adjacent to the Sunshine Mall</p>
                            <hr>
                            <p class="mb-1"><strong>Operating Hours:</strong></p>
                            <p class="mb-1">Saturday & Sunday: 5:00 AM - 2:00 PM</p>
                            <hr>
                            <p class="mb-0"><strong>Contact:</strong> (02) 8888-9999 | pickup@aani-market.com</p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="pickup_instructions" class="form-label">Pickup Instructions (Optional)</label>
                            <textarea class="form-control" id="pickup_instructions" name="pickup_instructions" 
                                      rows="3" placeholder="Any special instructions for pickup..."></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> <strong>Please Note:</strong> 
                            Bring your pickup code when picking up your order. A unique 6-character pickup code will be generated and will be available when your order is ready for pickup (typically on weekends).
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-4 d-none" id="paymentMethodCard">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="payment_online" value="online">
                                    <label class="form-check-label" for="payment_online">
                                        💳 Online Payment
                                        <small class="d-block text-muted">Pay full amount online</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="payment_pickup" value="pickup">
                                    <label class="form-check-label" for="payment_pickup">
                                        💵 Pay on Pickup
                                        <small class="d-block text-muted">Pay when you collect your order</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Button -->
                <div class="d-grid d-none" id="orderButtonContainer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> Place Order
                    </button>
                </div>
            </form>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    @foreach($groupedCart as $vendorId => $items)
                        <div class="mb-3">
                            <h6>{{ $vendorServices[$vendorId]->business_name }}</h6>
                            @foreach($items as $item)
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">{{ $item['product_name'] }} ({{ $item['quantity'] }} {{ $item['unit_type'] }})</span>
                                    <span class="small">₱{{ number_format($item['price_per_unit'] * $item['quantity'], 2) }}</span>
                                </div>
                            @endforeach
                            <hr>
                        @endforeach
                    
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Subtotal:</strong>
                        <strong id="subtotal">₱{{ number_format($subtotal, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Market Fee:</span>
                        <span>₱{{ number_format($marketFee, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee:</span>
                        <span id="deliveryFee">₱0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5>Total:</h5>
                        <h5 class="text-primary" id="total">₱{{ number_format($total, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Delivery Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickAddAddressForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quick_address_line" class="form-label">Address Line</label>
                                <input type="text" class="form-control" id="quick_address_line" name="address_line" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quick_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="quick_city" name="city" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quick_province" class="form-label">Province</label>
                                <input type="text" class="form-control" id="quick_province" name="province" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quick_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="quick_postal_code" name="postal_code" 
                                       placeholder="1234" pattern="[0-9]{4}" maxlength="4">
                                <small class="form-text text-muted">Must be exactly 4 digits (numbers only)</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="quick_is_default" name="is_default">
                        <label class="form-check-label" for="quick_is_default">
                            Set as default address
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="quickAddAddress()">Add Address</button>
            </div>
        </div>
    </div>
</div>

<script>
// Check if it's weekday or weekend
function checkDeliveryAvailability() {
    const now = new Date();
    const day = now.getDay(); // 0 = Sunday, 6 = Saturday
    const isWeekday = day >= 1 && day <= 5; // Monday to Friday
    
    // Disable weekend delivery on weekdays and vice versa
    document.querySelectorAll('select[name^="delivery_type_"]').forEach(select => {
        const options = select.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === 'weekday_delivery' && !isWeekday) {
                option.disabled = true;
                option.textContent += ' (Not available today)';
            } else if (option.value === 'weekend_delivery' && isWeekday) {
                option.disabled = true;
                option.textContent += ' (Not available today)';
            } else {
                option.disabled = false;
                // Remove any existing "(Not available today)" text
                option.textContent = option.textContent.replace(' (Not available today)', '');
            }
        });
    });
}

// Calculate delivery fees and update totals
function calculateTotals() {
    let deliveryFee = 0;
    const deliverySelects = document.querySelectorAll('select[name^="delivery_type_"]');
    
    deliverySelects.forEach(select => {
        if (select.value === 'weekday_delivery') {
            deliveryFee += 150;
        } else if (select.value === 'weekend_delivery') {
            deliveryFee += 75;
        }
        // Weekend pickup is free
    });
    
    // Update delivery fee display
    const deliveryFeeElement = document.getElementById('deliveryFee');
    if (deliveryFeeElement) {
        deliveryFeeElement.textContent = '₱' + deliveryFee.toFixed(2);
    }
    
    // Update total
    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');
    if (subtotalElement && totalElement) {
        const subtotal = parseFloat(subtotalElement.textContent.replace('₱', '').replace(',', ''));
        const total = subtotal + deliveryFee;
        totalElement.textContent = '₱' + total.toFixed(2);
    }
}

// Toggle delivery/pickup information cards
function toggleDeliveryInfo() {
    const deliverySelects = document.querySelectorAll('select[name^="delivery_type_"]');
    const deliveryInfoCard = document.getElementById('deliveryInfoCard');
    const pickupInfoCard = document.getElementById('pickupInfoCard');
    const paymentMethodCard = document.getElementById('paymentMethodCard');
    const orderButtonContainer = document.getElementById('orderButtonContainer');
    
    let hasPickup = false;
    let hasDelivery = false;
    let hasAnySelection = false;
    
    deliverySelects.forEach(select => {
        if (select.value && select.value.includes('pickup')) {
            hasPickup = true;
            hasAnySelection = true;
        } else if (select.value && select.value.includes('delivery')) {
            hasDelivery = true;
            hasAnySelection = true;
        }
    });
    
    if (!hasAnySelection) {
        // Hide all cards when nothing is selected
        deliveryInfoCard.classList.add('d-none');
        pickupInfoCard.classList.add('d-none');
        paymentMethodCard.classList.add('d-none');
        orderButtonContainer.classList.add('d-none');
    } else if (hasPickup && !hasDelivery) {
        // Show pickup info, hide delivery info
        deliveryInfoCard.classList.add('d-none');
        pickupInfoCard.classList.remove('d-none');
        paymentMethodCard.classList.remove('d-none');
        orderButtonContainer.classList.remove('d-none');
    } else if (hasDelivery || (!hasPickup && !hasDelivery)) {
        // Show delivery info, hide pickup info
        deliveryInfoCard.classList.remove('d-none');
        pickupInfoCard.classList.add('d-none');
        paymentMethodCard.classList.remove('d-none');
        orderButtonContainer.classList.remove('d-none');
    }
    
    // Calculate totals after toggling
    calculateTotals();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    checkDeliveryAvailability();
    toggleDeliveryInfo();
    
    // Initial payment options check
    updatePaymentOptions();
    
    // Add event listeners to delivery type selects
    document.querySelectorAll('select[name^="delivery_type_"]').forEach(select => {
        select.addEventListener('change', function() {
            toggleDeliveryInfo();
            updatePaymentOptions();
        });
    });
});

// Update payment options based on delivery method
function updatePaymentOptions() {
    const hasPickup = document.querySelectorAll('select[name^="delivery_type_"]').length > 0 && 
                       Array.from(document.querySelectorAll('select[name^="delivery_type_"]')).some(select => select.value.includes('pickup'));
    document.getElementById('payment_pickup').disabled = !hasPickup;
    if (!hasPickup && document.getElementById('payment_pickup').checked) {
        document.getElementById('payment_online').checked = true;
    }
}

// Quick add address function
function quickAddAddress() {
    const form = document.getElementById('quickAddAddressForm');
    const formData = new FormData(form);
    
    fetch('{{ route("profile.addresses.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error adding address');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding address');
    });
}
</script>
@endsection
