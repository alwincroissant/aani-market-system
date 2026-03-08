@extends('layouts.base')

@section('title', 'Edit Physical Sale')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil-square me-2"></i>Edit Physical Sale</h2>
            <p class="text-muted mb-0">Update this walk-in sale record</p>
        </div>
        <a href="{{ route('vendor.walk-in-sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('vendor.walk-in-sales.update', $sale->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sale Date</label>
                        <input type="date" name="sale_date" class="form-control"
                               value="{{ old('sale_date', optional($sale->sale_date)->toDateString()) }}"
                               max="{{ now()->toDateString() }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Product</label>
                        <select class="form-select" id="product_id" name="product_id">
                            <option value="">-- Custom / Not Listed --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-name="{{ $product->product_name }}"
                                        data-price="{{ $product->price_per_unit }}"
                                        {{ old('product_id', $sale->product_id) == $product->id ? 'selected' : '' }}>
                                    {{ $product->product_name }} (₱{{ number_format($product->price_per_unit, 2) }}/{{ $product->unit_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="product_name" name="product_name"
                               value="{{ old('product_name', $sale->product_name) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity"
                               min="1" step="1" value="{{ old('quantity', $sale->quantity) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Unit Price (₱) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="unit_price" name="unit_price"
                               min="0" step="0.01" value="{{ old('unit_price', $sale->unit_price) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Total</label>
                        <div class="form-control-plaintext fw-bold text-success fs-5" id="rowTotal">₱0.00</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" maxlength="500">{{ old('notes', $sale->notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('vendor.walk-in-sales.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const productSelect = document.getElementById('product_id');
    const productNameInput = document.getElementById('product_name');
    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unit_price');
    const totalOutput = document.getElementById('rowTotal');

    function updateTotal() {
        const qty = parseInt(quantityInput.value || '0', 10) || 0;
        const price = parseFloat(unitPriceInput.value || '0') || 0;
        totalOutput.textContent = '₱' + (qty * price).toFixed(2);
    }

    productSelect.addEventListener('change', function () {
        const opt = productSelect.options[productSelect.selectedIndex];
        if (opt && opt.value) {
            if (opt.dataset.name) {
                productNameInput.value = opt.dataset.name;
            }
            if (opt.dataset.price) {
                unitPriceInput.value = parseFloat(opt.dataset.price).toFixed(2);
            }
        }
        updateTotal();
    });

    quantityInput.addEventListener('input', updateTotal);
    unitPriceInput.addEventListener('input', updateTotal);
    updateTotal();
})();
</script>
@endsection
