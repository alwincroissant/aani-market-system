@extends('layouts.base')

@section('title', 'Store Settings')

@section('content')
{{-- Toast notification container - positioned at top center of viewport --}}
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1050">
    <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i> <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    {{-- Page header with title and back button --}}
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
            {{-- Left column: Main settings forms --}}
            <div class="col-lg-8">
                {{-- Store Banner Section --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Store Banner</h5>
                        <p class="text-muted mb-0 small">This banner will be displayed to customers when they view your shop.</p>
                    </div>
                    <div class="card-body">
                        {{-- Current banner preview (200px height) --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Current Banner</label>
                            <div id="bannerPreview" class="border rounded" style="height: 200px; background: #f8f9fa; overflow: hidden;">
                                @if($vendor->banner_url)
                                    <img src="{{ asset('storage/' . $vendor->banner_url) }}" alt="Store Banner" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    {{-- Placeholder when no banner exists --}}
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <div class="text-center">
                                            <i class="bi bi-image fs-1 text-muted mb-3"></i>
                                            <p class="text-muted">No banner uploaded</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Banner upload controls --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Upload New Banner</label>
                            <div class="d-flex gap-3">
                                {{-- Triggers hidden file input --}}
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('bannerInput').click()">
                                    <i class="bi bi-upload me-2"></i>Choose Banner
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="removeBanner()">
                                    <i class="bi bi-trash me-2"></i>Remove Current
                                </button>
                            </div>
                            {{-- Hidden file input (styled button triggers this) --}}
                            <input type="file" id="bannerInput" name="banner" class="d-none" accept="image/*" onchange="previewBanner(this)">
                            <small class="text-muted">Recommended size: 1200x300px. Max file size: 2MB</small>
                        </div>
                    </div>
                </div>

                {{-- Store Logo Section --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Store Logo</h5>
                        <p class="text-muted mb-0 small">Your logo will be displayed alongside your store name.</p>
                    </div>
                    <div class="card-body">
                        {{-- Current logo preview --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Current Logo</label>
                            <div class="d-flex align-items-center">
                                <div id="logoPreview" class="border rounded" style="width: 120px; height: 120px; background: #f8f9fa; overflow: hidden;">
                                    @if($vendor->logo_url)
                                        <img src="{{ asset('storage/' . $vendor->logo_url) }}" alt="Store Logo" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        {{-- Placeholder when no logo exists --}}
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-shop fs-1 text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Logo upload controls --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Upload New Logo</label>
                            <div class="d-flex gap-3">
                                {{-- Triggers hidden file input --}}
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('logoInput').click()">
                                    <i class="bi bi-upload me-2"></i>Choose Logo
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="removeLogo()">
                                    <i class="bi bi-trash me-2"></i>Remove Current
                                </button>
                            </div>
                            {{-- Hidden file input (styled button triggers this) --}}
                            <input type="file" id="logoInput" name="logo" class="d-none" accept="image/*" onchange="previewLogo(this)">
                            <small class="text-muted">Recommended size: 500x500px (square). Max file size: 2MB</small>
                        </div>
                    </div>
                </div>

                {{-- Store Information Section --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Store Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Business Name</label>
                                <input type="text" class="form-control" name="business_name" value="{{ $vendor->business_name ?? auth()->user()->name . '\'s Store' }}">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Contact Phone</label>
                                <input type="tel" class="form-control" name="contact_phone" value="{{ $vendor->contact_phone ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Business Description</label>
                            <textarea class="form-control" name="business_description" rows="3">{{ $vendor->business_description ?? '' }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Business Hours</label>
                                <input type="text" class="form-control" name="business_hours" value="{{ $vendor->business_hours ?? '8:00 AM - 6:00 PM' }}">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Delivery Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="delivery_available" id="deliveryAvailable" {{ ($vendor->delivery_available ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="deliveryAvailable">Available for delivery</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Farm Location Details Section --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Farm Location Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Farm Name</label>
                                <input type="text" class="form-control" name="farm_name" value="{{ $vendor->farm_name ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Region/Province</label>
                                {{-- Philippine regions dropdown --}}
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
                            <textarea class="form-control" name="complete_address" rows="2">{{ $vendor->complete_address ?? '' }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Farm Size (hectares)</label>
                                {{-- Accepts decimal values (step="0.1") --}}
                                <input type="number" class="form-control" name="farm_size" value="{{ $vendor->farm_size ?? '' }}" step="0.1">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Years in Operation</label>
                                <input type="number" class="form-control" name="years_in_operation" value="{{ $vendor->years_in_operation ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right column: Live preview sidebar --}}
            <div class="col-lg-4">
                {{-- Sticky sidebar that stays in view while scrolling --}}
                <div class="card shadow-sm border-0 position-sticky" style="top: 20px;">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Store Preview</h5>
                    </div>
                    <div class="card-body">
                        {{-- Mini store card preview (updates in real-time) --}}
                        <div class="border rounded mb-3 overflow-hidden">
                            {{-- Banner preview (80px height for compact view) --}}
                            <div id="previewBanner" class="bg-primary" style="height: 80px;">
                                @if($vendor->banner_url)
                                    <img src="{{ asset('storage/' . $vendor->banner_url) }}" alt="Banner" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 text-white">
                                        <i class="bi bi-shop fs-3"></i>
                                    </div>
                                @endif
                            </div>
                            {{-- Store info preview --}}
                            <div class="p-3">
                                {{-- Logo and Store Name --}}
                                <div class="d-flex align-items-center mb-2">
                                    <div id="previewLogo" class="me-2" style="width: 40px; height: 40px;">
                                        @if($vendor->logo_url)
                                            <img src="{{ asset('storage/' . $vendor->logo_url) }}" alt="Logo" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 100%; height: 100%;">
                                                <i class="bi bi-shop text-white"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <h6 class="mb-0" id="previewStoreName">{{ $vendor->business_name ?? auth()->user()->name . '\'s Store' }}</h6>
                                </div>
                                <p class="text-muted small mb-2" id="previewDescription">{{ $vendor->business_description ?? 'Fresh local products from our farm' }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <span id="previewRegion">{{ $vendor->region ?? 'Philippines' }}</span>
                                    </small>
                                    {{-- Status badge (currently static) --}}
                                    <span class="badge bg-success">
                                        <i class="bi bi-circle-fill me-1"></i>Open
                                    </span>
                                </div>
                            </div>
                        </div>

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
// Display Bootstrap toast notification
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const toastBody = document.getElementById('toastMessage');
    toastBody.textContent = message;
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Preview banner image before upload
function previewBanner(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgHtml = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            document.getElementById('bannerPreview').innerHTML = imgHtml;
            document.getElementById('previewBanner').innerHTML = imgHtml;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove banner and restore default placeholders
function removeBanner() {
    const placeholder = `<div class="d-flex align-items-center justify-content-center h-100 text-center">
        <div><i class="bi bi-image fs-1 text-muted mb-3"></i><p class="text-muted">No banner uploaded</p></div>
    </div>`;
    document.getElementById('bannerPreview').innerHTML = placeholder;
    document.getElementById('previewBanner').innerHTML = `<div class="d-flex align-items-center justify-content-center h-100 text-white bg-primary"><i class="bi bi-shop fs-3"></i></div>`;
    document.getElementById('bannerInput').value = '';
}

// Preview logo image before upload
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgHtml = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            document.getElementById('logoPreview').innerHTML = imgHtml;
            document.getElementById('previewLogo').innerHTML = `<img src="${e.target.result}" alt="Logo" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove logo and restore default placeholder
function removeLogo() {
    const placeholder = `<div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-shop fs-1 text-muted"></i></div>`;
    document.getElementById('logoPreview').innerHTML = placeholder;
    
    const previewPlaceholder = `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 100%; height: 100%;"><i class="bi bi-shop text-white"></i></div>`;
    document.getElementById('previewLogo').innerHTML = previewPlaceholder;
    document.getElementById('logoInput').value = '';
}

// Real-time preview: Update business name as user types
document.querySelector('input[name="business_name"]').addEventListener('input', function(e) {
    document.getElementById('previewStoreName').textContent = e.target.value || 'Your Store Name';
});

// Real-time preview: Update description as user types
document.querySelector('textarea[name="business_description"]').addEventListener('input', function(e) {
    document.getElementById('previewDescription').textContent = e.target.value || 'Fresh local products';
});

// Real-time preview: Update region when dropdown changes
document.querySelector('select[name="region"]').addEventListener('change', function(e) {
    document.getElementById('previewRegion').textContent = e.target.value || 'Philippines';
});

// Handle form submission via AJAX
document.getElementById('storeSettingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Clear previous validation errors
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    // Show loading state
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;

    try {
        const formData = new FormData(form);
        // Manually handle checkbox value (checked = 1, unchecked = 0)
        const deliveryCheckbox = form.querySelector('input[name="delivery_available"]');
        formData.set('delivery_available', deliveryCheckbox.checked ? '1' : '0');

        const response = await fetch('{{ route("vendor.update-settings") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            showToast(data.message || 'Settings saved successfully!');
            
            // If a new banner was uploaded, update the preview with the saved version
            if (data.banner_url) {
                const bannerImgHtml = `<img src="${data.banner_url}" style="width: 100%; height: 100%; object-fit: cover;">`;
                document.getElementById('bannerPreview').innerHTML = bannerImgHtml;
                document.getElementById('previewBanner').innerHTML = bannerImgHtml;
            }
            
            // If a new logo was uploaded, update the preview with the saved version
            if (data.logo_url) {
                const logoImgHtml = `<img src="${data.logo_url}" style="width: 100%; height: 100%; object-fit: cover;">`;
                document.getElementById('logoPreview').innerHTML = logoImgHtml;
                document.getElementById('previewLogo').innerHTML = `<img src="${data.logo_url}" alt="Logo" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
        } else if (response.status === 422) {
            // Validation errors - display them next to fields
            showToast('Please check for errors', 'danger');
            Object.keys(data.errors).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = data.errors[key][0];
                    input.closest('div').appendChild(errorDiv);
                }
            });
        } else {
            throw new Error(data.message || 'Server error');
        }
    } catch (error) {
        showToast(error.message, 'danger');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});
</script>
@endsection