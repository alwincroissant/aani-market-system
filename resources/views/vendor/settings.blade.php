@extends('layouts.base')

@section('title', 'Store Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Store Settings</h2>
                    <p class="text-muted mb-0">Customize your storefront appearance</p>
                </div>
                <div>
                    <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="storeSettingsForm" enctype="multipart/form-data">
        <div class="row">
            <!-- Store Banner Section -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Store Banner</h5>
                        <p class="text-muted mb-0 small">This banner will be displayed to customers when they view your shop</p>
                    </div>
                    <div class="card-body">
                        <!-- Current Banner Preview -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Current Banner</label>
                            <div id="bannerPreview" class="border rounded" style="height: 200px; background: #f8f9fa; overflow: hidden;">
                                @if($vendor->banner_image)
                                    <img src="{{ asset('storage/' . $vendor->banner_image) }}" alt="Store Banner" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <div class="text-center">
                                            <i class="bi bi-image fs-1 text-muted mb-3"></i>
                                            <p class="text-muted">No banner uploaded</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Upload New Banner -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Upload New Banner</label>
                            <div class="d-flex gap-3">
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('bannerInput').click()">
                                    <i class="bi bi-upload me-2"></i>Choose Banner
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="removeBanner()">
                                    <i class="bi bi-trash me-2"></i>Remove Current
                                </button>
                            </div>
                            <input type="file" id="bannerInput" name="banner" class="d-none" accept="image/*" onchange="previewBanner(this)">
                            <small class="text-muted">Recommended size: 1200x300px. Max file size: 2MB</small>
                        </div>
                    </div>
                </div>

                <!-- Store Information -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Store Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Store Name</label>
                                <input type="text" class="form-control" name="store_name" value="{{ $vendor->store_name ?? auth()->user()->name . '\'s Store' }}" placeholder="Enter your store name">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="tel" class="form-control" name="contact_number" value="{{ $vendor->contact_number ?? '' }}" placeholder="09XXXXXXXXX">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Store Description</label>
                            <textarea class="form-control" name="store_description" rows="3" placeholder="Tell customers about your store...">{{ $vendor->store_description ?? '' }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Business Hours</label>
                                <input type="text" class="form-control" name="business_hours" value="{{ $vendor->business_hours ?? '8:00 AM - 6:00 PM' }}" placeholder="e.g., 8:00 AM - 6:00 PM">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Delivery Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="delivery_available" id="deliveryAvailable" {{ $vendor->delivery_available ?? false ? 'checked' : '' }}>
                                    <label class="form-check-label" for="deliveryAvailable">
                                        Available for delivery
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Farm Location Details -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Farm Location Details</h5>
                        <p class="text-muted mb-0 small">Help customers find where your products come from</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Farm Name</label>
                                <input type="text" class="form-control" name="farm_name" value="{{ $vendor->farm_name ?? '' }}" placeholder="e.g., Green Valley Organic Farm">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Region/Province</label>
                                <select class="form-select" name="region">
                                    <option value="">Select Region</option>
                                    <option value="NCR" {{ ($vendor->region ?? '') == 'NCR' ? 'selected' : '' }}>National Capital Region</option>
                                    <option value="CALABARZON" {{ ($vendor->region ?? '') == 'CALABARZON' ? 'selected' : '' }}>CALABARZON</option>
                                    <option value="MIMAROPA" {{ ($vendor->region ?? '') == 'MIMAROPA' ? 'selected' : '' }}>MIMAROPA</option>
                                    <option value="Bicol Region" {{ ($vendor->region ?? '') == 'Bicol Region' ? 'selected' : '' }}>Bicol Region</option>
                                    <option value="Western Visayas" {{ ($vendor->region ?? '') == 'Western Visayas' ? 'selected' : '' }}>Western Visayas</option>
                                    <option value="Central Visayas" {{ ($vendor->region ?? '') == 'Central Visayas' ? 'selected' : '' }}>Central Visayas</option>
                                    <option value="Eastern Visayas" {{ ($vendor->region ?? '') == 'Eastern Visayas' ? 'selected' : '' }}>Eastern Visayas</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Complete Address</label>
                            <textarea class="form-control" name="complete_address" rows="2" placeholder="Street address, city, province">{{ $vendor->complete_address ?? '' }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Farm Size (hectares)</label>
                                <input type="number" class="form-control" name="farm_size" value="{{ $vendor->farm_size ?? '' }}" step="0.1" min="0" placeholder="0.0">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Years in Operation</label>
                                <input type="number" class="form-control" name="years_in_operation" value="{{ $vendor->years_in_operation ?? '' }}" min="0" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Preview -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 position-sticky" style="top: 20px;">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Store Preview</h5>
                        <p class="text-muted mb-0 small">How customers will see your store</p>
                    </div>
                    <div class="card-body">
                        <!-- Store Card Preview -->
                        <div class="border rounded mb-3">
                            <div id="previewBanner" class="bg-primary" style="height: 80px;">
                                @if($vendor->banner_image)
                                    <img src="{{ asset('storage/' . $vendor->banner_image) }}" alt="Banner" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 text-white">
                                        <i class="bi bi-shop fs-3"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <h6 class="mb-1" id="previewStoreName">{{ $vendor->store_name ?? auth()->user()->name . '\'s Store' }}</h6>
                                <p class="text-muted small mb-2" id="previewDescription">{{ $vendor->store_description ?? 'Fresh local products from our farm' }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <span id="previewRegion">{{ $vendor->region ?? 'Philippines' }}</span>
                                    </small>
                                    <span class="badge bg-success">
                                        <i class="bi bi-circle-fill me-1"></i>Open
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-primary">{{ \App\Models\Product::where('vendor_id', $vendor->id)->count() }}</div>
                                    <small class="text-muted">Products</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-success">4.8</div>
                                    <small class="text-muted">Rating</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-info">156</div>
                                    <small class="text-muted">Sales</small>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-2"></i>Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function previewBanner(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('bannerPreview').innerHTML = 
                `<img src="${e.target.result}" alt="Banner preview" style="width: 100%; height: 100%; object-fit: cover;">`;
            document.getElementById('previewBanner').innerHTML = 
                `<img src="${e.target.result}" alt="Banner preview" style="width: 100%; height: 100%; object-fit: cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeBanner() {
    document.getElementById('bannerPreview').innerHTML = `
        <div class="d-flex align-items-center justify-content-center h-100">
            <div class="text-center">
                <i class="bi bi-image fs-1 text-muted mb-3"></i>
                <p class="text-muted">No banner uploaded</p>
            </div>
        </div>
    `;
    document.getElementById('previewBanner').innerHTML = `
        <div class="d-flex align-items-center justify-content-center h-100 text-white">
            <i class="bi bi-shop fs-3"></i>
        </div>
    `;
    document.getElementById('bannerInput').value = '';
}

// Live preview updates
document.querySelector('input[name="store_name"]').addEventListener('input', function(e) {
    document.getElementById('previewStoreName').textContent = e.target.value || 'Your Store Name';
});

document.querySelector('textarea[name="store_description"]').addEventListener('input', function(e) {
    document.getElementById('previewDescription').textContent = e.target.value || 'Fresh local products from our farm';
});

document.querySelector('select[name="region"]').addEventListener('change', function(e) {
    document.getElementById('previewRegion').textContent = e.target.value || 'Philippines';
});

// Form submission
document.getElementById('storeSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    // Submit to backend
    fetch('{{ route("vendor.update-settings") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>Store settings saved successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Remove alert after 3 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred. Please try again.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
@endsection
