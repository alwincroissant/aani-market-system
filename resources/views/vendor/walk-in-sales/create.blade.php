@extends('layouts.base')

@section('title', 'Record Physical Sale')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-cart-plus me-2"></i>Record Physical Sale</h2>
            <p class="text-muted mb-0">Add sales made at the weekend market stall</p>
        </div>
        <a href="{{ route('vendor.walk-in-sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('vendor.walk-in-sales.store') }}" id="walkInSaleForm">
        @csrf

        {{-- Sale Date --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sale Date</label>
                        <input type="date" name="sale_date" class="form-control"
                               value="{{ old('sale_date', now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}">
                        <small class="text-muted">Leave as today or pick a past date for the weekend sale</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sale Items --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Sale Items</h5>
                    <small class="text-muted">Add each product sold. Select from your inventory or type a custom product name.</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                    <i class="bi bi-plus-circle me-1"></i> Add Item
                </button>
            </div>
            <div class="card-body">
                <div id="itemsContainer">
                    {{-- First item row (always present) --}}
                    <div class="sale-item-row border rounded p-3 mb-3" data-index="0">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Product <span class="text-danger">*</span></label>
                                <select class="form-select product-select" name="items[0][product_id]"
                                        onchange="fillProductInfo(this, 0)">
                                    <option value="">-- Custom / Not Listed --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-name="{{ $product->product_name }}"
                                                data-price="{{ $product->price_per_unit }}"
                                                data-unit="{{ $product->unit_type }}"
                                                data-stock="{{ $product->stock_quantity }}">
                                            {{ $product->product_name }}
                                            (₱{{ number_format($product->price_per_unit, 2) }}/{{ $product->unit_type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control product-name" name="items[0][product_name]"
                                       required placeholder="e.g. Tomatoes">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control item-qty" name="items[0][quantity]"
                                       required min="1" step="1" placeholder="0" oninput="updateRowTotal(0)">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Unit Price (₱) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control item-price" name="items[0][unit_price]"
                                       required min="0" step="0.01" placeholder="0.00" oninput="updateRowTotal(0)">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="w-100">
                                    <label class="form-label">Total</label>
                                    <div class="form-control-plaintext fw-bold text-success row-total" id="rowTotal_0">₱0.00</div>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                                <input type="text" class="form-control" name="items[0][notes]"
                                       placeholder="e.g. Bulk buy, regular customer, etc.">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn" onclick="removeItem(this)" style="display:none">
                                    <i class="bi bi-trash me-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grand Total & Submit --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0">Grand Total: <span class="text-success" id="grandTotal">₱0.00</span></h4>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-2"></i> Save Physical Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let itemIndex = 1; // start from 1 since 0 already exists

@php
    $productsJson = $products->map(function($p) {
        return [
            'id'    => $p->id,
            'name'  => $p->product_name,
            'price' => $p->price_per_unit,
            'unit'  => $p->unit_type,
            'stock' => $p->stock_quantity,
        ];
    })->values();
@endphp
const productsData = {!! json_encode($productsJson) !!};

function fillProductInfo(selectEl, index) {
    const opt = selectEl.options[selectEl.selectedIndex];
    const row = selectEl.closest('.sale-item-row');
    if (opt.value) {
        row.querySelector('.product-name').value = opt.dataset.name;
        row.querySelector('.item-price').value = parseFloat(opt.dataset.price).toFixed(2);
        updateRowTotal(index);
    }
}

function updateRowTotal(index) {
    const row = document.querySelector(`.sale-item-row[data-index="${index}"]`);
    if (!row) return;
    const qty   = parseFloat(row.querySelector('.item-qty').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    row.querySelector('.row-total').textContent = '₱' + (qty * price).toFixed(2);
    updateGrandTotal();
}

function updateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.sale-item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        total += qty * price;
    });
    document.getElementById('grandTotal').textContent = '₱' + total.toFixed(2);
}

function removeItem(btn) {
    btn.closest('.sale-item-row').remove();
    // Show/hide remove buttons
    toggleRemoveButtons();
    updateGrandTotal();
}

function toggleRemoveButtons() {
    const rows = document.querySelectorAll('.sale-item-row');
    rows.forEach(r => {
        r.querySelector('.remove-item-btn').style.display = rows.length > 1 ? '' : 'none';
    });
}

document.getElementById('addItemBtn').addEventListener('click', function () {
    const container = document.getElementById('itemsContainer');

    // Build product options HTML
    let optionsHtml = '<option value="">-- Custom / Not Listed --</option>';
    productsData.forEach(p => {
        optionsHtml += `<option value="${p.id}" data-name="${p.name}" data-price="${p.price}" data-unit="${p.unit}" data-stock="${p.stock}">${p.name} (₱${parseFloat(p.price).toFixed(2)}/${p.unit})</option>`;
    });

    const html = `
    <div class="sale-item-row border rounded p-3 mb-3" data-index="${itemIndex}">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Product</label>
                <select class="form-select product-select" name="items[${itemIndex}][product_id]"
                        onchange="fillProductInfo(this, ${itemIndex})">
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control product-name" name="items[${itemIndex}][product_name]"
                       required placeholder="e.g. Tomatoes">
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control item-qty" name="items[${itemIndex}][quantity]"
                       required min="1" step="1" placeholder="0" oninput="updateRowTotal(${itemIndex})">
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit Price (₱) <span class="text-danger">*</span></label>
                <input type="number" class="form-control item-price" name="items[${itemIndex}][unit_price]"
                       required min="0" step="0.01" placeholder="0.00" oninput="updateRowTotal(${itemIndex})">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="w-100">
                    <label class="form-label">Total</label>
                    <div class="form-control-plaintext fw-bold text-success row-total" id="rowTotal_${itemIndex}">₱0.00</div>
                </div>
            </div>
            <div class="col-md-10">
                <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                <input type="text" class="form-control" name="items[${itemIndex}][notes]"
                       placeholder="e.g. Bulk buy, regular customer, etc.">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn" onclick="removeItem(this)">
                    <i class="bi bi-trash me-1"></i> Remove
                </button>
            </div>
        </div>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
    itemIndex++;
    toggleRemoveButtons();
});

// Initial toggle
toggleRemoveButtons();
</script>
@endsection
