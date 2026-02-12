@extends('layouts.base')

@section('title', 'Browse Shops')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Browse Shops</h2>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Home
            </a>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-funnel"></i> Filters & Search
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('shop.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search shops or products...">
                        </div>
                        <div class="col-md-2">
                            <label for="section" class="form-label">Section</label>
                            <select class="form-select" id="section" name="section" disabled>
                                <option value="">All Sections</option>
                                <option value="">Not Available</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="service" class="form-label">Service</label>
                            <select class="form-select" id="service" name="service">
                                <option value="">All Services</option>
                                <option value="weekend_pickup" {{ request('service') == 'weekend_pickup' ? 'selected' : '' }}>
                                    Weekend Pickup
                                </option>
                                <option value="weekday_delivery" {{ request('service') == 'weekday_delivery' ? 'selected' : '' }}>
                                    Weekday Delivery
                                </option>
                                <option value="weekend_delivery" {{ request('service') == 'weekend_delivery' ? 'selected' : '' }}>
                                    Weekend Delivery
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Sort Featured</label>
                            <select class="form-select" id="sort_by" name="sort_by" disabled>
                                <option value="">Featured Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="product_sort_by" class="form-label">Sort Products</label>
                            <select class="form-select" id="product_sort_by" name="product_sort_by">
                                <option value="created_at" {{ request('product_sort_by') == 'created_at' ? 'selected' : '' }}>Newest First</option>
                                <option value="price_low" {{ request('product_sort_by') == 'price_low' ? 'selected' : '' }}>Price (Low to High)</option>
                                <option value="price_high" {{ request('product_sort_by') == 'price_high' ? 'selected' : '' }}>Price (High to Low)</option>
                                <option value="name" {{ request('product_sort_by') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_name }}" {{ request('category') == $category->category_name ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="price_min" class="form-label">Min Price</label>
                            <input type="number" class="form-control" id="price_min" name="price_min" value="{{ request('price_min') }}" placeholder="0" min="0" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label for="price_max" class="form-label">Max Price</label>
                            <input type="number" class="form-control" id="price_max" name="price_max" value="{{ request('price_max') }}" placeholder="9999" min="0" step="0.01">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Apply Filters
                            </button>
                            <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
                {{-- Debug block removed --}}
            </div>
        </div>
    </div>
</div>

<!-- Featured Vendors -->
<div class="row mb-5">
    <div class="col-12">
        <h4 class="mb-3">Featured Vendors</h4>
        <div class="row">
            @foreach($featuredVendors as $vendor)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                @if($vendor->logo_url)
                                    <img src="{{ asset($vendor->logo_url) }}" alt="{{ $vendor->business_name }}" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-shop text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $vendor->business_name }}</h6>
                                    <small class="text-muted">{{ $vendor->product_count ?? 0 }} products</small>
                                </div>
                            </div>
                            @if($vendor->business_description)
                                <p class="card-text small text-muted">{{ Str::limit($vendor->business_description, 100) }}</p>
                            @endif
                            @if($vendor->min_price || $vendor->max_price)
                                <div class="mb-2">
                                    <small class="text-muted">Price Range: </small>
                                    <small class="fw-bold">₱{{ number_format($vendor->min_price ?? 0, 2) }} - ₱{{ number_format($vendor->max_price ?? 0, 2) }}</small>
                                </div>
                            @endif
                            <div class="mt-auto">
                                <a href="{{ route('shop.show', $vendor->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-shop"></i> Visit Shop
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Filtered Products -->
<div class="row">
    <div class="col-12">
        <h4 class="mb-3">Products</h4>
        <div class="row">
            @foreach($products as $product)
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100">
                        @if($product->product_image_url)
                            <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="bi bi-image text-muted fs-1"></i>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ $product->product_name }}</h6>
                            <p class="card-text small text-muted mb-2">{{ $product->business_name }}</p>
                            @if($product->category_name)
                                <span class="badge bg-secondary mb-2">{{ $product->category_name }}</span>
                            @endif
                            @if($product->description)
                                <p class="card-text text-muted small">{{ Str::limit($product->description, 60) }}</p>
                            @endif
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-primary">₱{{ number_format($product->price_per_unit, 2) }}</span>
                                    <span class="text-muted small">/ {{ $product->unit_type }}</span>
                                </div>

                                <!-- Add To Cart Section -->
                                <div class="mb-2">
                                    <div class="input-group mb-2" style="max-width: 120px;">
                                        <input type="number" id="quantityInput-{{ $product->id }}" class="form-control form-control-sm" value="1" min="1" max="99">
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm w-100" onclick="addToCart({{ $product->id }})">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </div>

                                <div class="d-flex justify-content-between mt-2">
                                    <a href="{{ route('shop.product', $product->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('shop.show', $product->vendor_id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-shop"></i> Shop
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($products->count() === 0)
            <div class="alert alert-info">
                <h5>No Products Found</h5>
                <p>Try adjusting your filters to see more products.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function addToCart(productId) {
    const quantity = parseInt(document.getElementById('quantityInput-' + productId).value);
    
    window.location.href = `/cart/add/${productId}`;
}
</script>
@endpush
