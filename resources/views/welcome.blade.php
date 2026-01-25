@extends('layouts.base')

@section('title', 'AANI Market - Interactive Map')

@section('content')
<!-- Admin Viewing Site Banner -->
@if(auth()->check() && auth()->user()->role === 'administrator' && request('view_site'))
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-0">
            <div>
                <i class="bi bi-eye me-2"></i>
                <strong>Viewing as Customer</strong> - You are viewing the public site as an administrator
            </div>
            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-speedometer2"></i> Back to Admin
            </a>
        </div>
    </div>
</div>
@endif

<div class="row mb-4">
    <div class="col-12">
        <h1 class="mb-4">AANI Market</h1>
        <p class="lead text-muted">Fresh local produce and artisanal goods from your community</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-5">
    <div class="col-md-4 mb-3">
        <div class="card h-100 text-center">
            <div class="card-body">
                <i class="bi bi-shop fs-1 text-primary mb-3"></i>
                <h5>Browse Shops</h5>
                <p class="text-muted">Explore all vendors and their products</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-right"></i> Browse Now
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100 text-center">
            <div class="card-body">
                <i class="bi bi-map fs-1 text-success mb-3"></i>
                <h5>Market Map</h5>
                <p class="text-muted">Find vendors and their locations</p>
                <button class="btn btn-success" onclick="document.getElementById('market-map-section').scrollIntoView({behavior: 'smooth'})">
                    <i class="bi bi-arrow-right"></i> View Map
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100 text-center">
            <div class="card-body">
                <i class="bi bi-basket fs-1 text-warning mb-3"></i>
                <h5>Shop Online</h5>
                <p class="text-muted">Order from multiple vendors</p>
                <a href="{{ route('shop.index') }}" class="btn btn-warning">
                    <i class="bi bi-arrow-right"></i> Start Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Featured Vendors -->
@if($featuredVendors->count() > 0)
<div class="row mb-5">
    <div class="col-12">
        <h3 class="mb-4">Featured Vendors</h3>
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
                                    <small class="text-muted">{{ $vendor->product_count }} products</small>
                                </div>
                            </div>
                            @if($vendor->business_description)
                                <p class="card-text small text-muted">{{ Str::limit($vendor->business_description, 80) }}</p>
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
        <div class="text-center mt-3">
            <a href="{{ route('shop.index') }}" class="btn btn-outline-primary">
                View All Vendors <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endif

<!-- Recent Products -->
@if($featuredProducts->count() > 0)
<div class="row mb-5">
    <div class="col-12">
        <h3 class="mb-4">Recent Products</h3>
        <div class="row">
            @foreach($featuredProducts as $product)
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
                            @if($product->description)
                                <p class="card-text text-muted small">{{ Str::limit($product->description, 60) }}</p>
                            @endif
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-primary">₱{{ number_format($product->price_per_unit, 2) }}</span>
                                    <span class="text-muted small">/ {{ $product->unit_type }}</span>
                                </div>
                                <div class="btn-group w-100">
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
        <div class="text-center mt-3">
            <a href="{{ route('shop.index') }}" class="btn btn-outline-primary">
                Browse All Products <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endif

<!-- Market Map Section -->
<div id="market-map-section" class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-map"></i> Market Map
                </h4>
            </div>
            <div class="card-body">
                @if($mapImage)
                    <div id="marketMap" style="height: 600px; border: 2px solid #ddd; border-radius: 4px;"></div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-map text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">Market Map Coming Soon</h5>
                        <p class="text-muted">The interactive market map is currently being set up. Please check back later or browse our shops directly.</p>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-shop"></i> Browse Shops Instead
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($mapImage)
    const mapImageUrl = '{{ asset($mapImage) }}';
    let map, imageOverlay, markers = [];
    
    // Initialize map
    map = L.map('marketMap', {
        crs: L.CRS.Simple,
        minZoom: -2,
        maxZoom: 2
    });
    
    // Load image and set bounds
    const img = new Image();
    img.onload = function() {
        const bounds = [[0, 0], [this.height, this.width]];
        imageOverlay = L.imageOverlay(mapImageUrl, bounds).addTo(map);
        map.fitBounds(bounds);
        
        // Add stall markers
        loadStalls();
    };
    img.onerror = function() {
        document.getElementById('marketMap').innerHTML = '<div class="alert alert-danger m-3">Failed to load map image</div>';
    };
    img.src = mapImageUrl;
    
    // Load existing stalls
    function loadStalls() {
        @foreach($stalls as $stall)
            @if($stall->x1 && $stall->y1 && $stall->x2 && $stall->y2)
                addStallRectangle({{ $stall->stall_id }}, {{ $stall->x1 }}, {{ $stall->y1 }}, {{ $stall->x2 }}, {{ $stall->y2 }}, '{{ $stall->stall_number }}', {{ $stall->vendor_id }}, '{{ $stall->business_name }}', '{{ $stall->section_code ?? '' }}', {{ $stall->weekend_pickup_enabled ? 'true' : 'false' }}, {{ $stall->weekday_delivery_enabled ? 'true' : 'false' }}, {{ $stall->weekend_delivery_enabled ? 'true' : 'false' }});
            @endif
        @endforeach
    }
    
    // Add stall rectangle
    function addStallRectangle(id, x1, y1, x2, y2, stallNumber, vendorId, businessName, sectionCode, weekendPickup, weekdayDelivery, weekendDelivery) {
        // Section-specific colors
        const sectionColors = {
            'VEG': 'rgba(34, 139, 34, 0.7)',     // Forest Green for Vegetables
            'PLT': 'rgba(148, 0, 211, 0.7)',     // Dark Violet for Plants/Flowers
            'MF': 'rgba(139, 69, 19, 0.7)',      // Saddle Brown for Meat & Fish
            'FD': 'rgba(255, 140, 0, 0.7)'       // Dark Orange for Food
        };
        
        const fillColor = sectionColors[sectionCode] || 'rgba(0, 123, 255, 0.7)';
        const borderColor = fillColor.replace('0.7', '1');
        
        // Ensure proper ordering
        const bounds = [[Math.min(y1, y2), Math.min(x1, x2)], [Math.max(y1, y2), Math.max(x1, x2)]];
        
        const rectangle = L.rectangle(bounds, {
            color: borderColor,
            fillColor: fillColor,
            weight: 2,
            opacity: 0.8,
            fillOpacity: 0.5
        }).addTo(map);
        
        rectangle.stallData = { id, stallNumber, vendorId, businessName, bounds };
        markers.push(rectangle);
        
        // Add stall number label in center
        const center = L.latLng(
            (bounds[0][0] + bounds[1][0]) / 2,
            (bounds[0][1] + bounds[1][1]) / 2
        );
        
        // Build service badges
        let serviceBadges = [];
        if (weekendPickup) serviceBadges.push('🏪 Weekend Pickup');
        if (weekdayDelivery) serviceBadges.push('🚚 Weekday Delivery');
        if (weekendDelivery) serviceBadges.push('🚚 Weekend Delivery');
        
        const label = L.divIcon({
            className: 'stall-label',
            html: `<div style="background: white; border: 3px solid ${borderColor}; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.3); cursor: pointer;">${stallNumber}</div>`,
            iconSize: [35, 35],
            iconAnchor: [17.5, 17.5]
        });
        
        const labelMarker = L.marker(center, { icon: label }).addTo(map);
        labelMarker.stallData = rectangle.stallData;
        markers.push(labelMarker);
        
        // Popup content
        const popupContent = `
            <div style="min-width: 200px;">
                <h6><strong>${businessName}</strong></h6>
                <p class="mb-1"><strong>Stall:</strong> ${stallNumber}</p>
                <p class="mb-1"><strong>Section:</strong> ${sectionCode || 'N/A'}</p>
                ${serviceBadges.length > 0 ? '<p class="mb-2"><strong>Services:</strong><br>' + serviceBadges.join(' | ') + '</p>' : ''}
                <button class="btn btn-primary btn-sm" onclick="window.location.href='/shop/${vendorId}'">
                    🛒 View Shop
                </button>
            </div>
        `;
        
        rectangle.bindPopup(popupContent);
        labelMarker.bindPopup(popupContent);
        
        // Click handler - redirect to shop
        [rectangle, labelMarker].forEach(element => {
            element.on('click', function(e) {
                if (!e.originalEvent.target.closest('.leaflet-popup-content')) {
                    window.location.href = `/shop/${vendorId}`;
                }
            });
            
            // Hover effect
            element.on('mouseover', function() {
                this.setStyle({ fillOpacity: 0.8, weight: 3 });
            });
            
            element.on('mouseout', function() {
                this.setStyle({ fillOpacity: 0.5, weight: 2 });
            });
        });
    }
    @endif
});
</script>
@endpush
