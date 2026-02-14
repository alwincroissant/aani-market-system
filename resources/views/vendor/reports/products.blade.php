@extends('layouts.base')

@section('title', 'Products Report - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Products Report</h2>
        <div>
            <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-box display-1 text-primary mb-3"></i>
                    <h5 class="card-title">{{ $products->count() }}</h5>
                    <p class="text-muted">Total Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-check-circle display-1 text-success mb-3"></i>
                    <h5 class="card-title">{{ $products->where('is_available', true)->count() }}</h5>
                    <p class="text-muted">Available</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-x-circle display-1 text-danger mb-3"></i>
                    <h5 class="card-title">{{ $products->where('is_available', false)->count() }}</h5>
                    <p class="text-muted">Unavailable</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-cart3 display-1 text-info mb-3"></i>
                    <h5 class="card-title">{{ $products->sum('total_sold') }}</h5>
                    <p class="text-muted">Total Sold</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="{{ route('vendor.reports.products.export-pdf') }}" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
                <a href="{{ route('vendor.reports.products.export-csv') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-csv"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Product Performance</h5>
        </div>
        <div class="card-body">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Total Sold</th>
                                <th>Revenue</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($product->product_image_url)
                                                <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}" class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $product->product_name }}</div>
                                                <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $product->category_name ?? 'General' }}</span>
                                    </td>
                                    <td>₱{{ number_format($product->price_per_unit, 2) }}</td>
                                    <td>
                                        @if($product->is_available)
                                            <span class="badge bg-success">Available</span>
                                        @else
                                            <span class="badge bg-danger">Unavailable</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->total_sold }}</td>
                                    <td>₱{{ number_format($product->total_sold * $product->price_per_unit, 2) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-info btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-box display-1 text-muted mb-3"></i>
                    <h4 class="text-muted">No products found</h4>
                    <p class="text-muted">You haven't added any products yet.</p>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Your First Product
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
