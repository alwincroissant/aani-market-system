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
                                        <small class="text-muted">(<span id="vendor-item-count-{{ $vendorId }}">{{ count($items) }}</span> items)</small>
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
                                        <table class="table" id="vendor-table-{{ $vendorId }}">
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
                                                    <tr id="checkout-row-{{ $itemId }}">
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
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-secondary"
                                                                        onclick="checkoutChangeQty('{{ $itemId }}', {{ $vendorId }}, -1)">−</button>
                                                                <span id="checkout-qty-{{ $itemId }}" class="fw-semibold px-1">{{ $item['qty'] }}</span>
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-secondary"
                                                                        onclick="checkoutChangeQty('{{ $itemId }}', {{ $vendorId }}, 1)">+</button>
                                                            </div>
                                                        </td>
                                                        <td id="checkout-rowtotal-{{ $itemId }}">₱{{ number_format($item['price'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-primary">
                                                    <th colspan="3">Vendor Subtotal</th>
                                                    <th id="checkout-vendortotal-{{ $vendorId }}">₱{{ number_format(array_sum(array_column($items, 'price')), 2) }}</th>
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
                                @php
                                    $customerName = auth()->user()->customer
                                        ? (auth()->user()->customer->first_name && auth()->user()->customer->last_name
                                            ? auth()->user()->customer->first_name . ' ' . auth()->user()->customer->last_name
                                            : auth()->user()->name)
                                        : auth()->user()->name;
                                    $customerPhone = auth()->user()->customer ? auth()->user()->customer->phone : '';
                                    $selectedAddress = $addresses ? $addresses->firstWhere('is_default', true) : null;
                                    $selectedAddressText = $selectedAddress
                                        ? trim($selectedAddress->address_line . ', ' . $selectedAddress->city . ($selectedAddress->province ? ', ' . $selectedAddress->province : '') . ($selectedAddress->postal_code ? ' ' . $selectedAddress->postal_code : ''))
                                        : 'No address selected';
                                @endphp

                                <input type="hidden" id="customer_name" name="customer_name" value="{{ $customerName }}">
                                <input type="hidden" id="customer_phone" name="customer_phone" value="{{ $customerPhone }}">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Full Name</div>
                                        <div class="fw-semibold">{{ $customerName }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Phone Number</div>
                                        <div class="fw-semibold">{{ $customerPhone ?: 'Not set' }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Email</div>
                                        <div class="fw-semibold">{{ auth()->user()->email }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Delivery Address</div>
                                        <div class="fw-semibold" id="customerDeliveryAddress">{{ $selectedAddressText }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Options -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Delivery Options</h5>
                            </div>
                            <div class="card-body">
                                @foreach($groupedCart as $vendorId => $items)
                                    <div class="mb-4">
                                        <label for="delivery_type_{{ $vendorId }}" class="form-label fw-semibold">
                                            {{ $vendorInfo[$vendorId]->business_name }}
                                        </label>
                                        <select class="form-select delivery-option" name="delivery_type_{{ $vendorId }}" 
                                                id="delivery_type_{{ $vendorId }}" data-vendor-id="{{ $vendorId }}">
                                            <option value="">-- Select Delivery Option --</option>
                                            @if(isset($vendorInfo[$vendorId]))
                                                @if($vendorInfo[$vendorId]->weekend_pickup_enabled)
                                                    <option value="weekend_pickup">🏪 Weekend Pickup (Free)</option>
                                                @endif
                                                @if($vendorInfo[$vendorId]->weekday_delivery_enabled)
                                                    <option value="weekday_delivery">🚚 Weekday Delivery (₱50)</option>
                                                @endif
                                                @if($vendorInfo[$vendorId]->weekend_delivery_enabled)
                                                    <option value="weekend_delivery">🚚 Weekend Delivery (₱75)</option>
                                                @endif
                                            @endif
                                        </select>
                                        @if(!isset($vendorInfo[$vendorId]) || (!$vendorInfo[$vendorId]->weekend_pickup_enabled && !$vendorInfo[$vendorId]->weekday_delivery_enabled && !$vendorInfo[$vendorId]->weekend_delivery_enabled))
                                            <p class="text-muted small mt-2">No delivery options available for this vendor.</p>
                                        @endif
                                    </div>
                                @endforeach
                                <div class="text-danger small" id="deliveryOptionsError" style="display: none;">
                                    Please select a delivery option for each shop.
                                </div>
                            </div>
                        </div>

                        <!-- AANI Market Pickup Information (for pickup orders) -->
                        <div class="card mb-4" id="aaaniMarketPickupInfo" style="display: none;">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-shop me-2"></i>AANI Market Pickup</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="alert alert-info mb-0">
                                        <p class="mb-2"><strong>📍 Pickup Location:</strong></p>
                                        <p class="mb-2">AANI Weekend Market, Arca South (formerly FTI Complex), Taguig City</p>
                                        <hr class="my-2">
                                        <p class="mb-2"><strong>🕐 Pickup Hours:</strong></p>
                                        <p class="mb-2">Saturday and Sunday, 5:00 AM to 2:00 PM</p>
                                        <hr class="my-2">
                                        <p class="mb-0"><strong>📋 Pickup Instruction:</strong></p>
                                        <p class="mb-0">A unique pickup code will be provided to you once your items are ready for collection at the market.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Address (for delivery orders) -->
                        <div class="card mb-4" id="deliveryAddressSection" style="display: none;">
                            <div class="card-header">
                                <h5 class="mb-0">Delivery Address</h5>
                            </div>
                            <div class="card-body">
                                @if($addresses && $addresses->count() > 0)
                                    <div class="mb-3">
                                        <label class="form-label">Select Address <span class="text-danger">*</span></label>
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
                                    <strong id="orderSubtotal">₱{{ number_format($totalPrice, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Delivery Fees:</span>
                                    <strong id="deliveryFees">₱0.00</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <span><strong>Total:</strong></span>
                                    <strong class="text-primary" id="totalAmount">₱{{ number_format($totalPrice, 2) }}</strong>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg" id="placeOrderButton">
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
// ── Seed item data from Blade ──────────────────────────────────────────────
const checkoutItems = {
    @foreach($groupedCart as $vendorId => $items)
        @foreach($items as $itemId => $item)
        '{{ $itemId }}': {
            unitPrice: {{ $item['item']->price_per_unit }},
            qty:       {{ $item['qty'] }},
            vendorId:  {{ $vendorId }}
        },
        @endforeach
    @endforeach
};

// ── Pending sync state ─────────────────────────────────────────────────────
let pendingSync = false;
const syncTimers = {};

// ── Format helper ──────────────────────────────────────────────────────────
function formatPeso(amount) {
    return '₱' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ── Persist qty to backend ─────────────────────────────────────────────────
function syncQtyToBackend(itemId, qty) {
    return fetch('/cart/add/' + itemId + '?quantity=' + qty).catch(() => {});
}

// ── Qty change ─────────────────────────────────────────────────────────────
function checkoutChangeQty(itemId, vendorId, delta) {
    const d = checkoutItems[itemId];
    const newQty = d.qty + delta;

    if (newQty < 1) {
        if (!confirm('Remove this item from your order?')) return;
        // Flush all pending syncs, then remove
        Promise.all(
            Object.keys(syncTimers).map(id => {
                clearTimeout(syncTimers[id].timer);
                return syncQtyToBackend(id, syncTimers[id].qty);
            })
        ).then(() => {
            window.location.href = '/cart/remove/' + itemId;
        });
        return;
    }

    d.qty = newQty;

    // Update qty display
    document.getElementById('checkout-qty-' + itemId).textContent = newQty;

    // Update row total
    const rowTotal = d.unitPrice * newQty;
    const rowEl = document.getElementById('checkout-rowtotal-' + itemId);
    if (rowEl) rowEl.textContent = formatPeso(rowTotal);

    // Recalculate vendor subtotal
    let vendorSubtotal = 0;
    Object.values(checkoutItems).forEach(item => {
        if (item.vendorId === vendorId) vendorSubtotal += item.unitPrice * item.qty;
    });
    const vendorEl = document.getElementById('checkout-vendortotal-' + vendorId);
    if (vendorEl) vendorEl.textContent = formatPeso(vendorSubtotal);

    // Recalculate order summary
    calculateDeliveryFees();

    // Mark pending and debounce backend sync
    pendingSync = true;
    clearTimeout(syncTimers[itemId]?.timer);
    syncTimers[itemId] = {
        qty: newQty,
        timer: setTimeout(() => {
            syncQtyToBackend(itemId, newQty).then(() => {
                delete syncTimers[itemId];
                if (Object.keys(syncTimers).length === 0) pendingSync = false;
            });
        }, 400)
    };
}

// ── Delivery fee + subtotal calculation ───────────────────────────────────
function calculateDeliveryFees() {
    let hasWeekdayDelivery = false;
    let hasWeekendDelivery = false;

    @foreach($groupedCart as $vendorId => $items)
        const deliverySelect{{ $vendorId }} = document.querySelector('select[name="delivery_type_{{ $vendorId }}"]');
        if (deliverySelect{{ $vendorId }}) {
            if (deliverySelect{{ $vendorId }}.value === 'weekday_delivery') hasWeekdayDelivery = true;
            if (deliverySelect{{ $vendorId }}.value === 'weekend_delivery')  hasWeekendDelivery = true;
        }
    @endforeach

    let subtotal = 0;
    Object.values(checkoutItems).forEach(i => subtotal += i.unitPrice * i.qty);

    let deliveryFees = 0;
    if (hasWeekdayDelivery) deliveryFees += 50;
    if (hasWeekendDelivery) deliveryFees += 75;

    document.getElementById('orderSubtotal').textContent = formatPeso(subtotal);
    document.getElementById('deliveryFees').textContent  = formatPeso(deliveryFees);
    document.getElementById('totalAmount').textContent   = formatPeso(subtotal + deliveryFees);
}

// ── Warn on accidental navigation while sync pending ──────────────────────
window.addEventListener('beforeunload', function (e) {
    if (pendingSync) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// ── Page init ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const deliveryAddressSection  = document.getElementById('deliveryAddressSection');
    const aaaniMarketPickupInfo   = document.getElementById('aaaniMarketPickupInfo');
    const deliveryOptions         = document.querySelectorAll('.delivery-option');
    const addressInputs           = document.querySelectorAll('input[name="selected_address"]');
    const customerDeliveryAddress = document.getElementById('customerDeliveryAddress');
    const placeOrderButton        = document.getElementById('placeOrderButton');
    const deliveryOptionsError    = document.getElementById('deliveryOptionsError');

    function updateFulfillmentDisplay() {
        let hasDelivery = false;
        let hasPickup   = false;

        deliveryOptions.forEach(select => {
            if (select.value === 'weekday_delivery' || select.value === 'weekend_delivery') {
                hasDelivery = true;
            } else if (select.value === 'weekend_pickup') {
                hasPickup = true;
            }
        });

        deliveryAddressSection.style.display = hasDelivery ? 'block' : 'none';
        aaaniMarketPickupInfo.style.display  = hasPickup   ? 'block' : 'none';

        calculateDeliveryFees();
        validateDeliveryOptions();
    }

    function validateDeliveryOptions() {
        let allSelected = true;
        deliveryOptions.forEach(select => {
            if (!select.value) {
                allSelected = false;
                select.classList.add('is-invalid');
            } else {
                select.classList.remove('is-invalid');
            }
        });
        if (deliveryOptionsError) deliveryOptionsError.style.display = allSelected ? 'none' : 'block';
        if (placeOrderButton)     placeOrderButton.disabled           = !allSelected;
    }

    function updateSelectedAddressDisplay() {
        if (!customerDeliveryAddress) return;

        const selected = document.querySelector('input[name="selected_address"]:checked');
        if (!selected) { customerDeliveryAddress.textContent = 'No address selected'; return; }

        const label = document.querySelector(`label[for="${selected.id}"]`);
        if (!label)  { customerDeliveryAddress.textContent = 'No address selected'; return; }

        const addressBlock = label.querySelector('div');
        if (!addressBlock) { customerDeliveryAddress.textContent = 'No address selected'; return; }

        const lines = [];
        addressBlock.querySelectorAll('div').forEach((line, index) => {
            const text = line.textContent.trim();
            if (!text || index === 0 || index === 1 || text === 'Default') return;
            lines.push(text);
        });

        customerDeliveryAddress.textContent = lines.length > 0 ? lines.join(', ') : 'No address selected';
    }

    deliveryOptions.forEach(select => select.addEventListener('change', updateFulfillmentDisplay));
    addressInputs.forEach(input  => input.addEventListener('change',  updateSelectedAddressDisplay));

    updateFulfillmentDisplay();
    updateSelectedAddressDisplay();
});
</script>
@endpush
@endsection