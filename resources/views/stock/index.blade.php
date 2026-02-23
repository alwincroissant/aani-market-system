@extends('layouts.base')

@section('title', 'Stock Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Stock Management</h2>
                <p class="text-muted mb-0">Manage inventory levels and track product availability</p>
            </div>
            <div>
                <button onclick="showBulkUpdateModal()" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat"></i> Bulk Update
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('stock.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search products..." value="{{ request('search') }}">
                    </div>

                    @if(isset($vendors) && auth()->user()->role === 'administrator')
                    <div class="col-md-3">
                        <label for="vendor_id" class="form-label">Shop</label>
                        <select name="vendor_id" id="vendor_id" class="form-select">
                            <option value="">All Shops</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}" {{ (string)request('vendor_id') === (string)$v->id ? 'selected' : '' }}>{{ $v->business_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label for="stock_status" class="form-label">Stock Status</label>
                        <select name="stock_status" id="stock_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Product</th>
                        <th>Vendor</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                        <th>Tracking</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" 
                                       value="{{ $product->id }}" onchange="updateSelectedCount()">
                            </td>
                            <td>
                                <strong>{{ $product->product_name }}</strong><br>
                                <small class="text-muted">{{ $product->unit_type }}</small>
                            </td>
                            <td>{{ $product->vendor->business_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $product->category->category_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <strong class="{{ $product->stock_quantity <= $product->minimum_stock ? 'text-danger' : '' }}">
                                    {{ $product->stock_quantity }}
                                </strong>
                            </td>
                            <td>{{ $product->minimum_stock }}</td>
                            <td>
                                @php
                                    $badgeClass = match($product->stock_status) {
                                        'In stock' => 'bg-success',
                                        'Low stock' => 'bg-warning',
                                        'Out of stock' => 'bg-danger',
                                        'Backorder' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $product->stock_status }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $product->track_stock ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $product->track_stock ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('stock.edit', $product) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <h5 class="mt-3">No products found</h5>
                                <p class="text-muted">Get started by adding some products to your inventory.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUpdateModalLabel">
                    <i class="bi bi-arrow-repeat me-2"></i>Bulk Stock Update
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkUpdateForm" method="POST" action="{{ route('stock.bulk-update') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="selectedCount">0</span> product(s) selected
                    </div>
                    
                    <div class="mb-3">
                        <label for="updateType" class="form-label">Update Type</label>
                        <select class="form-select" id="updateType" name="update_type" required>
                            <option value="">Select update type</option>
                            <option value="add">Add to Stock</option>
                            <option value="subtract">Subtract from Stock</option>
                            <option value="set">Set Exact Amount</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stockAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="stockAmount" name="amount" 
                               min="0" step="1" required>
                    </div>

                    <input type="hidden" name="product_ids" id="productIds">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const selectAll = document.getElementById('selectAll');
    checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const count = checked.length;
    const countDisplay = document.getElementById('selectedCount');
    if (countDisplay) {
        countDisplay.textContent = count;
    }
}

function showBulkUpdateModal() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one product');
        return;
    }
    
    const ids = Array.from(checked).map(cb => cb.value);
    document.getElementById('productIds').value = ids.join(',');
    
    const modal = new bootstrap.Modal(document.getElementById('bulkUpdateModal'));
    modal.show();
}
</script>
@endsection
