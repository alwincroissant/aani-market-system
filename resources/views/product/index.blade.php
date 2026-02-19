@extends('layouts.base')

@section('title', request('show') == 'deleted' ? 'Inactive Products' : 'My Products')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>{{ request('show') == 'deleted' ? 'Inactive Products' : 'My Products' }}</h2>
            @if(request('show') != 'deleted')
                <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
            @endif
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('products.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Product name..." value="{{ request('search') }}">
                    </div>
                    @if(request('show') != 'deleted')
                        <div class="col-md-3">
                            <label for="status" class="form-label">Stock Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="price_range" class="form-label">Price Range</label>
                            <select name="price_range" id="price_range" class="form-select">
                                <option value="">All Prices</option>
                                <option value="0-100" {{ request('price_range') == '0-100' ? 'selected' : '' }}>₱0 - ₱100</option>
                                <option value="100-500" {{ request('price_range') == '100-500' ? 'selected' : '' }}>₱100 - ₱500</option>
                                <option value="500-1000" {{ request('price_range') == '500-1000' ? 'selected' : '' }}>₱500 - ₱1000</option>
                                <option value="1000+" {{ request('price_range') == '1000+' ? 'selected' : '' }}>₱1000+</option>
                            </select>
                        </div>
                    @endif
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('products.index', ['show' => 'deleted']) }}" class="btn btn-outline-danger {{ request('show') == 'deleted' ? 'active' : '' }}">
                            <i class="bi bi-trash"></i> Deleted
                        </a>
                        @if(request('show') == 'deleted')
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Active
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Batch Operations -->
<div class="row mb-3" id="batchOpsDiv" style="display: none;">
    <div class="col-12">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <span><strong id="selectedCount">0</strong> product(s) selected</span>
            <div class="d-flex gap-2">
                @if(request('show') != 'deleted')
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#batchStockModal">
                        <i class="bi bi-arrow-repeat"></i> Batch Update Stock
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmBatchDelete()">
                        <i class="bi bi-trash"></i> Batch Delete
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-success" onclick="confirmBatchRestore()">
                        <i class="bi bi-arrow-counterclockwise"></i> Batch Restore
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmBatchForceDelete()">
                        <i class="bi bi-trash-fill"></i> Permanently Delete
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Unit</th>
                        @if(request('show') != 'deleted')
                            <th>Stock</th>
                        @endif
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}" onchange="updateBatchOps()">
                            </td>
                            <td>{{ $product->product_name }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $product->color_code ?? '#6c757d' }};">
                                    {{ $product->category_name }}
                                </span>
                            </td>
                            <td>₱{{ number_format($product->price_per_unit, 2) }}</td>
                            <td>{{ $product->unit_type }}</td>
                            @if(request('show') != 'deleted')
                                <td>
                                    <strong>{{ $product->stock_quantity }}</strong>
                                </td>
                            @endif
                            <td>
                                @php
                                    if (request('show') == 'deleted') {
                                        $stockStatus = 'Deleted';
                                        $badgeClass = 'bg-secondary';
                                    } else {
                                        $stockStatus = 'In Stock';
                                        $badgeClass = 'bg-success';
                                        if ($product->stock_quantity == 0) {
                                            $stockStatus = 'Out of Stock';
                                            $badgeClass = 'bg-danger';
                                        } elseif ($product->stock_quantity < 10) {
                                            $stockStatus = 'Low Stock';
                                            $badgeClass = 'bg-warning';
                                        }
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $stockStatus }}</span>
                            </td>
                            <td>
                                @if(request('show') != 'deleted')
                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#stockModal" 
                                            onclick="openStockModal({{ $product->id }}, '{{ $product->product_name }}', {{ $product->stock_quantity }}, '{{ $product->category_id }}', '{{ $product->description }}', '{{ $product->price_per_unit }}', '{{ $product->unit_type }}', '{{ $product->minimum_stock }}', '{{ $product->track_stock ? 1 : 0 }}', '{{ $product->is_available ? 1 : 0 }}')">
                                        <i class="bi bi-boxes"></i> Stock
                                    </button>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('products.restore', $product->id) }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete? Cannot undo!')" title="Permanently delete">
                                            <i class="bi bi-trash-fill"></i> Force Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <h5 class="mt-3">No products found</h5>
                                <p class="text-muted"><a href="{{ route('products.create') }}">Create your first product</a></p>
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

<!-- Stock Management Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockModalLabel">
                    <i class="bi bi-boxes me-2"></i>Manage Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="stockManageForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="product_name" id="productName">
                <input type="hidden" name="category_id" id="categoryId">
                <input type="hidden" name="description" id="description">
                <input type="hidden" name="price_per_unit" id="pricePerUnit">
                <input type="hidden" name="unit_type" id="unitType">
                <input type="hidden" name="minimum_stock" id="minimumStock">
                <input type="hidden" name="track_stock" id="trackStock">
                <input type="hidden" name="is_available" id="isAvailable">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="productTitle" class="form-label">Product</label>
                        <input type="text" class="form-control" id="productTitle" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="currentStock" class="form-label">Current Stock</label>
                        <input type="number" class="form-control" id="currentStock" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="newStock" class="form-label">New Stock Quantity</label>
                        <input type="number" class="form-control" id="newStock" name="stock_quantity" min="0" required>
                    </div>
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

<!-- Batch Stock Update Modal -->
<div class="modal fade" id="batchStockModal" tabindex="-1" aria-labelledby="batchStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchStockModalLabel">
                    <i class="bi bi-arrow-repeat me-2"></i>Batch Update Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="batchStockForm" method="POST" action="{{ route('products.batch-update') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong id="batchSelectedCount">0</strong> product(s) selected
                    </div>
                    
                    <div class="mb-3">
                        <label for="batchActionType" class="form-label">Action</label>
                        <select class="form-select" id="batchActionType" name="action" required>
                            <option value="">Select action</option>
                            <option value="add">Add to Stock</option>
                            <option value="subtract">Subtract from Stock</option>
                            <option value="set">Set Exact Amount</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="batchAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="batchAmount" name="amount" 
                               min="0" step="1" required>
                    </div>

                    <input type="hidden" name="product_ids" id="batchProductIds">
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
    updateBatchOps();
}

function updateBatchOps() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const count = checked.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('batchOpsDiv').style.display = count > 0 ? 'block' : 'none';
}

function openStockModal(productId, productName, currentStock, categoryId, description, pricePerUnit, unitType, minimumStock, trackStock, isAvailable) {
    document.getElementById('productTitle').value = productName;
    document.getElementById('currentStock').value = currentStock;
    document.getElementById('newStock').value = currentStock;
    document.getElementById('productName').value = productName;
    document.getElementById('categoryId').value = categoryId;
    document.getElementById('description').value = description;
    document.getElementById('pricePerUnit').value = pricePerUnit;
    document.getElementById('unitType').value = unitType;
    document.getElementById('minimumStock').value = minimumStock;
    document.getElementById('trackStock').value = trackStock;
    document.getElementById('isAvailable').value = isAvailable;
    
    document.getElementById('stockManageForm').action = `/products/${productId}`;
}

// Update modal when shown
document.addEventListener('show.bs.modal', function(e) {
    if (e.target.id === 'batchStockModal') {
        const checked = document.querySelectorAll('.product-checkbox:checked');
        const ids = Array.from(checked).map(cb => cb.value);
        document.getElementById('batchProductIds').value = ids.join(',');
        document.getElementById('batchSelectedCount').textContent = checked.length;
    }
});

function confirmBatchDelete() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one product');
        return;
    }
    
    if (confirm(`Are you sure you want to soft delete ${checked.length} product(s)? They can be restored later.`)) {
        const ids = Array.from(checked).map(cb => cb.value).join(',');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("products.batch-delete") }}';
        form.innerHTML = `
            {{ csrf_field() }}
            <input type="hidden" name="product_ids" value="${ids}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmBatchRestore() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one product');
        return;
    }
    
    if (confirm(`Are you sure you want to restore ${checked.length} product(s)?`)) {
        const ids = Array.from(checked).map(cb => cb.value).join(',');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("products.batch-restore") }}';
        form.innerHTML = `
            {{ csrf_field() }}
            <input type="hidden" name="product_ids" value="${ids}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmBatchForceDelete() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one product');
        return;
    }
    
    if (confirm(`Are you sure you want to permanently delete ${checked.length} product(s)? This cannot be undone!`)) {
        const ids = Array.from(checked).map(cb => cb.value).join(',');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("products.batch-force-delete") }}';
        form.innerHTML = `
            {{ csrf_field() }}
            <input type="hidden" name="product_ids" value="${ids}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

