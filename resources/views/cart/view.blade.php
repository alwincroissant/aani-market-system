@extends('layouts.base')
@section('title', 'Shopping Cart')
@section('content')

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

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        font-size: 14px;
        line-height: 1.6;
    }

    a { color: inherit; text-decoration: none; }

    .page { padding: 28px; max-width: 1100px; margin: 0 auto; }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .page-header h1 { font-size: 20px; font-weight: 600; }
    .page-header p  { font-size: 13px; color: var(--muted); margin-top: 2px; }

    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 20px;
        align-items: start;
    }

    .cart-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .cart-card-header {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        background: var(--bg);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .cart-card-header label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--muted);
        cursor: pointer;
        user-select: none;
    }
    .cart-card-header .item-count {
        margin-left: auto;
        font-size: 12px;
        color: var(--muted);
    }

    input[type="checkbox"] {
        width: 16px; height: 16px;
        accent-color: var(--accent);
        cursor: pointer;
        flex-shrink: 0;
        margin: 0;
    }

    .cart-row {
        display: grid;
        grid-template-columns: 20px 52px 1fr auto auto auto auto;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        transition: background .12s;
    }
    .cart-row:last-child { border-bottom: none; }
    .cart-row:hover { background: #faf9f7; }
    .cart-row.unchecked { opacity: .55; }

    .cart-thumb {
        width: 52px; height: 52px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .cart-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .cart-product-name { font-size: 13.5px; font-weight: 600; line-height: 1.3; }
    .cart-product-unit { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .cart-vendor {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        font-weight: 600;
        color: var(--accent-dk);
        background: var(--accent-lt);
        padding: 2px 8px;
        border-radius: 99px;
        margin-top: 4px;
        align-self: flex-start;
    }

    .cart-price {
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        color: var(--muted);
        white-space: nowrap;
        text-align: right;
    }

    .qty-control {
        display: flex;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        background: var(--surface);
    }
    .qty-btn {
        width: 30px; height: 32px;
        background: var(--bg);
        border: none;
        cursor: pointer;
        font-size: 16px;
        color: var(--text);
        display: flex; align-items: center; justify-content: center;
        transition: background .12s;
        flex-shrink: 0;
        line-height: 1;
    }
    .qty-btn:hover { background: var(--border); }
    .qty-display {
        width: 36px; height: 32px;
        border-left: 1px solid var(--border);
        border-right: 1px solid var(--border);
        text-align: center;
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        background: var(--surface);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-row-total {
        font-family: 'DM Mono', monospace;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--accent);
        text-align: right;
        white-space: nowrap;
        min-width: 72px;
    }

    .btn-remove {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px; height: 30px;
        border-radius: 7px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--muted);
        cursor: pointer;
        transition: background .12s, border-color .12s, color .12s;
        text-decoration: none;
        flex-shrink: 0;
    }
    .btn-remove:hover {
        background: var(--danger-lt);
        border-color: #fca5a5;
        color: var(--danger);
    }
    .btn-remove svg { width: 14px; height: 14px; }

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
        font-size: 13px;
        color: var(--text);
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        padding-top: 14px;
        border-top: 1px solid var(--border);
        margin-top: 4px;
        margin-bottom: 20px;
    }
    .summary-total .label { font-size: 14px; font-weight: 600; color: var(--text); }
    .summary-total .val {
        font-family: 'DM Mono', monospace;
        font-size: 20px;
        font-weight: 600;
        color: var(--accent);
    }

    .btn-checkout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        height: 44px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
        margin-bottom: 10px;
    }
    .btn-checkout:hover { background: var(--accent-dk); }
    .btn-checkout:disabled {
        background: var(--border);
        color: var(--muted);
        cursor: not-allowed;
    }

    .btn-continue {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        height: 40px;
        background: transparent;
        color: var(--muted);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 13.5px;
        font-weight: 500;
        cursor: pointer;
        transition: background .15s, border-color .15s, color .15s;
        text-decoration: none;
    }
    .btn-continue:hover { background: var(--bg); border-color: #ccc; color: var(--text); }

    .summary-note {
        font-size: 11.5px;
        color: var(--muted);
        text-align: center;
        margin-top: 12px;
        line-height: 1.5;
    }

    .empty-state {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        padding: 64px 24px;
        text-align: center;
    }
    .empty-icon { font-size: 48px; margin-bottom: 16px; }
    .empty-state h3 { font-size: 18px; font-weight: 600; margin-bottom: 6px; }
    .empty-state p  { font-size: 14px; color: var(--muted); margin-bottom: 24px; }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 22px;
        background: var(--accent);
        color: #fff;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background .15s;
        text-decoration: none;
    }
    .btn-primary:hover { background: var(--accent-dk); }

    @media (max-width: 860px) {
        .cart-layout { grid-template-columns: 1fr; }
        .summary-card { position: static; }
    }
    @media (max-width: 600px) {
        .page { padding: 16px; }
        .cart-row {
            grid-template-columns: 20px 44px 1fr;
            grid-template-rows: auto auto;
            row-gap: 10px;
        }
        .cart-price    { display: none; }
        .qty-control   { grid-column: 2 / 4; }
        .cart-row-total { grid-column: 2; }
        .btn-remove    { grid-column: 3; justify-self: end; }
    }
</style>

<div class="page">

    <div class="page-header">
        <div>
            <h1>Shopping Cart</h1>
            @if($products && count($products) > 0)
                <p>{{ count($products) }} item{{ count($products) !== 1 ? 's' : '' }} in your cart</p>
            @endif
        </div>
    </div>

    @if($products && count($products) > 0)

    <div class="cart-layout">

        {{-- ── Cart Items ── --}}
        <div class="cart-card">
            <div class="cart-card-header">
                <label>
                    <input type="checkbox" id="selectAll" checked>
                    Select All
                </label>
                <span class="item-count" id="checkedCount">{{ count($products) }} selected</span>
            </div>

            @foreach($products as $itemId => $item)
            <div class="cart-row" id="row-{{ $itemId }}">
                {{-- Checkbox --}}
                <input type="checkbox"
                       class="item-checkbox"
                       data-item-id="{{ $itemId }}"
                       checked>

                {{-- Thumb --}}
                <div class="cart-thumb">
                    @if(!empty($item['item']->product_image_url))
                        <img src="{{ asset($item['item']->product_image_url) }}" alt="{{ $item['item']->product_name }}">
                    @else
                        🛍
                    @endif
                </div>

                {{-- Info --}}
                <div>
                    <div class="cart-product-name">{{ $item['item']->product_name }}</div>
                    <div class="cart-product-unit">per {{ $item['item']->unit_type }}</div>
                    @if($item['item']->vendor)
                        <span class="cart-vendor">{{ $item['item']->vendor->business_name }}</span>
                    @endif
                </div>

                {{-- Unit price --}}
                <div class="cart-price">₱{{ number_format($item['item']->price_per_unit, 2) }}</div>

                {{-- Qty --}}
                <div class="qty-control">
                    <button type="button" class="qty-btn" onclick="changeQty('{{ $itemId }}', -1)">−</button>
                    <span class="qty-display" id="qty-{{ $itemId }}">{{ $item['qty'] }}</span>
                    <button type="button" class="qty-btn" onclick="changeQty('{{ $itemId }}', 1)">+</button>
                </div>

                {{-- Row total --}}
                <div class="cart-row-total" id="rowtotal-{{ $itemId }}">₱{{ number_format($item['price'], 2) }}</div>

                {{-- Remove --}}
                <a href="{{ route('removeItem', $itemId) }}" class="btn-remove" title="Remove item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                        <path d="M10 11v6M14 11v6"/>
                        <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                    </svg>
                </a>
            </div>
            @endforeach
        </div>

        {{-- ── Summary ── --}}
        <div class="summary-card">
            <div class="summary-header">Order Summary</div>
            <div class="summary-body">

                <div class="summary-line">
                    <span>Subtotal</span>
                    <span class="val" id="selectedSubtotal">₱{{ number_format($totalPrice, 2) }}</span>
                </div>
                <div class="summary-line">
                    <span>Items selected</span>
                    <span class="val" id="itemsSelected">{{ count($products) }}</span>
                </div>

                <div class="summary-total">
                    <span class="label">Total</span>
                    <span class="val" id="selectedTotal">₱{{ number_format($totalPrice, 2) }}</span>
                </div>

                <button type="button" class="btn-checkout" onclick="proceedToCheckout()" id="checkoutBtn">
                    Proceed to Checkout
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>

                <a href="{{ route('home') }}" class="btn-continue">
                    ← Continue Shopping
                </a>

                <p class="summary-note">Only selected items will be included in checkout.</p>
            </div>
        </div>

    </div>

    @else

    {{-- Empty State --}}
    <div class="empty-state">
        <div class="empty-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Browse the market and add items to your cart to get started.</p>
        <a href="{{ route('home') }}" class="btn-primary">Browse Shops</a>
    </div>

    @endif

</div>

{{-- ── Stock Toast ── --}}
<div id="stockToast" style="
    position: fixed;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%) translateY(12px);
    background: #1A1916;
    color: #fff;
    font-size: 13.5px;
    font-weight: 500;
    padding: 10px 18px;
    border-radius: 9px;
    box-shadow: 0 4px 20px rgba(0,0,0,.18);
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s ease, transform .2s ease;
    white-space: nowrap;
    z-index: 999;
    display: flex;
    align-items: center;
    gap: 8px;
">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
         style="width:15px;height:15px;color:#FBBF24;flex-shrink:0">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <span id="stockToastMsg"></span>
</div>

@push('scripts')
<script>
// ── Seed item data from Blade ──────────────────────────────────────────────
const itemData = {
    @foreach($products as $itemId => $item)
    '{{ $itemId }}': {
        unitPrice: {{ $item['item']->price_per_unit }},
        qty: {{ $item['qty'] }},
        stock: {{ $item['item']->stock_quantity }}
    },
    @endforeach
};

// ── Pending sync state ─────────────────────────────────────────────────────
let pendingSync = false;
const syncTimers = {};

// ── Format helper ──────────────────────────────────────────────────────────
function formatPeso(amount) {
    return '₱' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ── Persist qty to backend, returns a Promise ──────────────────────────────
function syncQtyToBackend(itemId, qty) {
    return fetch('/cart/add/' + itemId + '?quantity=' + qty).catch(() => {});
}

// ── Stock toast ────────────────────────────────────────────────────────────
let toastTimer = null;
function showToast(message) {
    const toast = document.getElementById('stockToast');
    const msg   = document.getElementById('stockToastMsg');
    msg.textContent = message;
    toast.style.opacity   = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(-50%) translateY(12px)';
    }, 3000);
}

// ── Change qty ─────────────────────────────────────────────────────────────
function changeQty(itemId, delta) {
    const d = itemData[itemId];
    const newQty = d.qty + delta;

    if (newQty < 1) {
        // Sync any other pending changes first, then navigate
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

    // ── Stock check ────────────────────────────────────────────────────────
    if (newQty > d.stock) {
        showToast(`Only ${d.stock} unit${d.stock !== 1 ? 's' : ''} available in stock.`);
        return;
    }

    d.qty = newQty;

    // Update qty display
    document.getElementById('qty-' + itemId).textContent = newQty;

    // Update row total
    const rowTotal = d.unitPrice * newQty;
    const rowTotalEl = document.getElementById('rowtotal-' + itemId);
    if (rowTotalEl) rowTotalEl.textContent = formatPeso(rowTotal);

    updateTotals();

    // Mark as pending and debounce the backend sync
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

// ── Update summary totals ──────────────────────────────────────────────────
function updateTotals() {
    const checkboxes     = document.querySelectorAll('.item-checkbox');
    const subtotalEl     = document.getElementById('selectedSubtotal');
    const totalEl        = document.getElementById('selectedTotal');
    const countEl        = document.getElementById('itemsSelected');
    const checkedCountEl = document.getElementById('checkedCount');
    const checkoutBtn    = document.getElementById('checkoutBtn');
    let subtotal = 0, count = 0;

    checkboxes.forEach(cb => {
        const row = document.getElementById('row-' + cb.dataset.itemId);
        if (cb.checked) {
            const d = itemData[cb.dataset.itemId];
            subtotal += d.unitPrice * d.qty;
            count++;
            row?.classList.remove('unchecked');
        } else {
            row?.classList.add('unchecked');
        }
    });

    if (subtotalEl)     subtotalEl.textContent    = formatPeso(subtotal);
    if (totalEl)        totalEl.textContent        = formatPeso(subtotal);
    if (countEl)        countEl.textContent        = count;
    if (checkedCountEl) checkedCountEl.textContent = count + ' selected';
    if (checkoutBtn)    checkoutBtn.disabled       = count === 0;
}

// ── Checkout: flush all pending syncs first, THEN submit ──────────────────
function proceedToCheckout() {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one item to checkout.');
        return;
    }

    const selectedItems = Array.from(checked).map(cb => cb.dataset.itemId);

    // Flush all pending debounced syncs before navigating
    const flushPromises = Object.keys(syncTimers).map(id => {
        clearTimeout(syncTimers[id].timer);
        return syncQtyToBackend(id, syncTimers[id].qty);
    });

    Promise.all(flushPromises).then(() => {
        pendingSync = false;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('checkout.index') }}';

        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const items = document.createElement('input');
        items.type = 'hidden'; items.name = 'selected_items';
        items.value = JSON.stringify(selectedItems);
        form.appendChild(items);

        document.body.appendChild(form);
        form.submit();
    });
}

// ── Warn if user navigates away via browser back/other links while pending ─
window.addEventListener('beforeunload', function (e) {
    if (pendingSync) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// ── Page init ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const selectAll  = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.item-checkbox');

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateTotals();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const all  = Array.from(checkboxes).every(c => c.checked);
            const some = Array.from(checkboxes).some(c => c.checked);
            selectAll.checked       = all;
            selectAll.indeterminate = some && !all;
            updateTotals();
        });
    });

    updateTotals();
});
</script>
@endpush

@endsection