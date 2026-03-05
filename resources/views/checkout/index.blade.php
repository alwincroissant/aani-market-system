@extends('layouts.base')

@section('title', 'Checkout')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --bg:        #F5F4F0;
        --surface:   #FFFFFF;
        --border:    #E4E2DC;
        --text:      #1A1916;
        --muted:     #7A7871;
        --accent:    #1D6F42;
        --accent-lt: #EAF4EE;
        --accent-dk: #155232;
        --danger:    #DC2626;
        --danger-lt: #FEE2E2;
        --radius:    10px;
        --shadow:    0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.05);
    }

    .page {
        padding: 28px;
        max-width: 1200px;
        margin: 0 auto;
        font-family: 'DM Sans', sans-serif;
        color: var(--text);
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .page-header h1 { font-size: 22px; font-weight: 600; margin: 0; }
    .page-header p  { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
        align-items: start;
    }

    .checkout-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 16px;
    }

    .checkout-card-header {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .checkout-card-header h5 {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .vendor-name { font-size: 15px; font-weight: 600; color: var(--text); text-transform: none; letter-spacing: 0; }
    .item-count { font-size: 12px; color: var(--muted); margin-left: 6px; }

    .badge-chip {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--muted);
        margin-left: 6px;
    }

    .checkout-card-body { padding: 14px 18px 18px; }

    .order-table { width: 100%; border-collapse: collapse; }
    .order-table thead th {
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        padding: 10px 8px;
        border-bottom: 1px solid var(--border);
    }
    .order-table tbody td {
        padding: 12px 8px;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
        vertical-align: middle;
    }
    .order-table tfoot th {
        padding: 12px 8px;
        font-size: 13px;
        color: var(--text);
        background: #faf9f7;
    }

    .checkout-product {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .checkout-product-image {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--bg);
    }

    .product-name { font-size: 13.5px; font-weight: 600; line-height: 1.35; }
    .product-unit { font-size: 12px; color: var(--muted); }
    .mono { font-family: 'DM Mono', monospace; }

    .readonly-qty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        padding: 4px 10px;
        border-radius: 999px;
        background: var(--bg);
        border: 1px solid var(--border);
        font-weight: 600;
        font-family: 'DM Mono', monospace;
    }

    .label-sm {
        font-size: 11px;
        letter-spacing: .06em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .form-control,
    .form-select {
        border-color: var(--border);
        border-radius: 8px;
        font-size: 13px;
        color: var(--text);
    }

    .summary-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        overflow: hidden;
        position: sticky;
        top: 20px;
    }

    .summary-header {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        background: var(--bg);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .summary-body { padding: 20px; }

    .summary-line {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-size: 13.5px;
        margin-bottom: 10px;
        color: var(--muted);
    }

    .summary-line .val {
        font-family: 'DM Mono', monospace;
        color: var(--text);
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        padding-top: 14px;
        border-top: 1px solid var(--border);
        margin-top: 6px;
        margin-bottom: 18px;
    }

    .summary-total .label { font-weight: 600; font-size: 14px; }
    .summary-total .val {
        font-family: 'DM Mono', monospace;
        font-size: 20px;
        font-weight: 600;
        color: var(--accent);
    }

    .btn-place-order {
        width: 100%;
        height: 44px;
        border: none;
        border-radius: 8px;
        background: var(--accent);
        color: #fff;
        font-weight: 600;
        transition: background .15s;
    }
    .btn-place-order:hover { background: var(--accent-dk); }
    .btn-place-order:disabled {
        background: var(--border);
        color: var(--muted);
        cursor: not-allowed;
    }

    .btn-secondary-soft {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 40px;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--muted);
        background: transparent;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: background .15s, color .15s;
    }
    .btn-secondary-soft:hover { background: var(--bg); color: var(--text); }

    .helper-note {
        margin-top: 10px;
        font-size: 11.5px;
        color: var(--muted);
        text-align: center;
    }

    .pickup-note {
        border: 1px solid var(--border);
        background: #faf9f7;
        border-radius: 8px;
        padding: 12px;
        font-size: 13px;
        color: var(--text);
    }

    .empty-state {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        padding: 64px 24px;
        text-align: center;
    }
    .empty-state h3 { font-size: 18px; font-weight: 600; margin-bottom: 6px; }
    .empty-state p { font-size: 14px; color: var(--muted); margin-bottom: 20px; }

    @media (max-width: 920px) {
        .checkout-layout { grid-template-columns: 1fr; }
        .summary-card { position: static; }
    }

    @media (max-width: 640px) {
        .page { padding: 16px; }
        .checkout-card-body { padding: 12px; }
        .order-table thead { display: none; }
        .order-table tbody tr { display: block; border-bottom: 1px solid var(--border); padding: 8px 0; }
        .order-table tbody td { display: flex; justify-content: space-between; border-bottom: none; padding: 6px 4px; }
        .order-table tbody td:first-child { display: block; }
    }
</style>
@endpush

@section('content')
<div class="page">
    <div class="page-header">
        <div>
            <h1>Checkout</h1>
            <p>Review your order details, choose fulfillment options, and place your order.</p>
        </div>
    </div>

    @if($groupedCart && count($groupedCart) > 0)
        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf

            <div class="checkout-layout">
                <div>
                    @foreach($groupedCart as $vendorId => $items)
                        <div class="checkout-card">
                            <div class="checkout-card-header">
                                <div>
                                    <span class="vendor-name">{{ $vendorInfo[$vendorId]->business_name ?? ('Vendor #' . $vendorId) }}</span>
                                    <span class="item-count">{{ count($items) }} item{{ count($items) !== 1 ? 's' : '' }}</span>
                                </div>
                                <div>
                                    @if(isset($vendorInfo[$vendorId]) && $vendorInfo[$vendorId]->weekend_pickup_enabled)
                                        <span class="badge-chip">Pickup</span>
                                    @endif
                                    @if(isset($vendorInfo[$vendorId]) && $vendorInfo[$vendorId]->weekday_delivery_enabled)
                                        <span class="badge-chip">Weekday Delivery</span>
                                    @endif
                                    @if(isset($vendorInfo[$vendorId]) && $vendorInfo[$vendorId]->weekend_delivery_enabled)
                                        <span class="badge-chip">Weekend Delivery</span>
                                    @endif
                                </div>
                            </div>
                            <div class="checkout-card-body">
                                <div class="table-responsive">
                                    <table class="order-table" id="vendor-table-{{ $vendorId }}">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $itemId => $item)
                                                <tr id="checkout-row-{{ $itemId }}">
                                                    <td>
                                                        <div class="checkout-product">
                                                            @if(!empty($item['item']->product_image_url))
                                                                <img src="{{ asset($item['item']->product_image_url) }}" alt="{{ $item['item']->product_name }}" class="checkout-product-image">
                                                            @else
                                                                <img src="{{ asset('/images/default-product.jpg') }}" alt="No image" class="checkout-product-image">
                                                            @endif
                                                            <div>
                                                                <div class="product-name">{{ $item['item']->product_name }}</div>
                                                                <div class="product-unit">per {{ $item['item']->unit_type }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="mono">₱{{ number_format($item['item']->price_per_unit, 2) }}</td>
                                                    <td><span class="readonly-qty">{{ $item['qty'] }}</span></td>
                                                    <td class="mono" id="checkout-rowtotal-{{ $itemId }}">₱{{ number_format($item['price'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Vendor Subtotal</th>
                                                <th class="mono" id="checkout-vendortotal-{{ $vendorId }}">₱{{ number_format(array_sum(array_column($items, 'price')), 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="checkout-card">
                        <div class="checkout-card-header">
                            <h5>Customer Information</h5>
                        </div>
                        <div class="checkout-card-body">
                            <input type="hidden" id="customer_name" name="customer_name" value="{{ $customerName }}">
                            <input type="hidden" id="customer_phone" name="customer_phone" value="{{ $customerPhone }}">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="label-sm">Full Name</div>
                                    <div class="fw-semibold">{{ $customerName }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="label-sm">Phone Number</div>
                                    <div class="fw-semibold">{{ $customerPhone ?: 'Not set' }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="label-sm">Email</div>
                                    <div class="fw-semibold">{{ auth()->user()->email }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="label-sm">Delivery Address</div>
                                    <div class="fw-semibold" id="customerDeliveryAddress">{{ $selectedAddressText }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <div class="checkout-card-header">
                            <h5>Delivery Options</h5>
                        </div>
                        <div class="checkout-card-body">
                            @foreach($groupedCart as $vendorId => $items)
                                <div class="mb-3">
                                    <label for="delivery_type_{{ $vendorId }}" class="label-sm" style="display:block; margin-bottom: 8px;">
                                        {{ $vendorInfo[$vendorId]->business_name ?? ('Vendor #' . $vendorId) }}
                                    </label>
                                    <select class="form-select delivery-option" name="delivery_type_{{ $vendorId }}" id="delivery_type_{{ $vendorId }}" data-vendor-id="{{ $vendorId }}">
                                        <option value="">Select Delivery Option</option>
                                        @if(isset($vendorInfo[$vendorId]))
                                            @if($vendorInfo[$vendorId]->weekend_pickup_enabled)
                                                <option value="weekend_pickup">Weekend Pickup (Free)</option>
                                            @endif
                                            @if($vendorInfo[$vendorId]->weekday_delivery_enabled)
                                                <option value="weekday_delivery">Weekday Delivery (₱50)</option>
                                            @endif
                                            @if($vendorInfo[$vendorId]->weekend_delivery_enabled)
                                                <option value="weekend_delivery">Weekend Delivery (₱75)</option>
                                            @endif
                                        @endif
                                    </select>
                                    @if(!isset($vendorInfo[$vendorId]) || (!$vendorInfo[$vendorId]->weekend_pickup_enabled && !$vendorInfo[$vendorId]->weekday_delivery_enabled && !$vendorInfo[$vendorId]->weekend_delivery_enabled))
                                        <p class="text-muted small mt-2 mb-0">No delivery options available for this vendor.</p>
                                    @endif
                                </div>
                            @endforeach

                            <div class="text-danger small" id="deliveryOptionsError" style="display: none;">
                                Please select a delivery option for each shop.
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card" id="aaaniMarketPickupInfo" style="display: none;">
                        <div class="checkout-card-header">
                            <h5>AANI Market Pickup</h5>
                        </div>
                        <div class="checkout-card-body">
                            <div class="pickup-note">
                                <p class="mb-2"><strong>Pickup Location:</strong> AANI Weekend Market, Arca South (formerly FTI Complex), Taguig City</p>
                                <p class="mb-2"><strong>Pickup Hours:</strong> Saturday and Sunday, 5:00 AM to 2:00 PM</p>
                                <p class="mb-0"><strong>Pickup Instruction:</strong> A unique pickup code will be provided once your items are ready for collection.</p>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card" id="deliveryAddressSection" style="display: none;">
                        <div class="checkout-card-header">
                            <h5>Delivery Address</h5>
                        </div>
                        <div class="checkout-card-body">
                            @if($addresses && $addresses->count() > 0)
                                <div class="mb-3">
                                    <div class="label-sm">Select Address <span class="text-danger">*</span></div>
                                    @foreach($addresses as $address)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="selected_address" id="address_{{ $address->id }}" value="{{ $address->id }}" {{ $address->is_default ? 'checked' : '' }}>
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
                                <div class="pickup-note mb-3">
                                    <p class="mb-2">You don't have any saved addresses.</p>
                                    <a href="{{ route('profile.addresses') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus"></i> Add Address
                                    </a>
                                </div>
                            @endif

                            <div class="mb-0">
                                <label for="delivery_notes" class="label-sm">Delivery Notes</label>
                                <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="3" placeholder="Special delivery instructions, landmarks, etc."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-header">Order Summary</div>
                    <div class="summary-body">
                        <div class="summary-line">
                            <span>Subtotal</span>
                            <span class="val" id="orderSubtotal">₱{{ number_format($totalPrice, 2) }}</span>
                        </div>
                        <div class="summary-line">
                            <span>Delivery Fees</span>
                            <span class="val" id="deliveryFees">₱0.00</span>
                        </div>

                        <div class="summary-total">
                            <span class="label">Total</span>
                            <span class="val" id="totalAmount">₱{{ number_format($totalPrice, 2) }}</span>
                        </div>

                        <button type="submit" class="btn-place-order" id="placeOrderButton">Place Order</button>
                        <a href="{{ route('getCart') }}" class="btn-secondary-soft mt-2">Back to Cart</a>

                        <p class="helper-note">Delivery fees are applied based on the selected fulfillment method.</p>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="empty-state">
            <h3>Your cart is empty</h3>
            <p>Add items to your cart before checking out.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Browse Shops</a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    const baseSubtotal = Number('{{ (float) ($totalPrice ?? 0) }}');

// ── Format helper ──────────────────────────────────────────────────────────
function formatPeso(amount) {
    return '₱' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ── Delivery fee + subtotal calculation ───────────────────────────────────
function calculateDeliveryFees() {
    let hasWeekdayDelivery = false;
    let hasWeekendDelivery = false;

    document.querySelectorAll('.delivery-option').forEach((select) => {
        if (select.value === 'weekday_delivery') hasWeekdayDelivery = true;
        if (select.value === 'weekend_delivery') hasWeekendDelivery = true;
    });

    let deliveryFees = 0;
    if (hasWeekdayDelivery) deliveryFees += 50;
    if (hasWeekendDelivery) deliveryFees += 75;

    document.getElementById('orderSubtotal').textContent = formatPeso(baseSubtotal);
    document.getElementById('deliveryFees').textContent  = formatPeso(deliveryFees);
    document.getElementById('totalAmount').textContent   = formatPeso(baseSubtotal + deliveryFees);
}

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