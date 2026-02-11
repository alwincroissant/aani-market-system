@extends('layouts.base')

@section('title', 'Add New Product')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Add New Product</h2>
                    <p class="text-muted mb-0">List your products for customers to discover</p>
                </div>
                <div>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Products
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="productForm" enctype="multipart/form-data">
        <div class="row">
            <!-- Left Column - Image Upload -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Product Images</h5>
                        <p class="text-muted mb-0 small">Add up to 5 images. First image will be the cover.</p>
                    </div>
                    <div class="card-body">
                        <!-- Main Image Preview -->
                        <div class="mb-3">
                            <div id="mainImagePreview" class="border rounded d-flex align-items-center justify-content-center" style="height: 400px; background: #f8f9fa;">
                                <div class="text-center">
                                    <i class="bi bi-image fs-1 text-muted mb-3"></i>
                                    <p class="text-muted mb-3">Main product image</p>
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('mainImage').click()">
                                        <i class="bi bi-upload me-2"></i>Upload Image
                                    </button>
                                </div>
                            </div>
                            <input type="file" id="mainImage" name="main_image" class="d-none" accept="image/*" onchange="previewMainImage(this)">
                        </div>

                        <!-- Thumbnail Carousel -->
                        <div class="mb-3">
                            <label class="form-label small text-muted">Additional Images</label>
                            <div class="d-flex gap-2" id="thumbnailContainer">
                                <div class="thumbnail-slot border rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #f8f9fa; cursor: pointer;" onclick="document.getElementById('thumbnail1').click()">
                                    <i class="bi bi-plus text-muted"></i>
                                </div>
                                <div class="thumbnail-slot border rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #f8f9fa; cursor: pointer;" onclick="document.getElementById('thumbnail2').click()">
                                    <i class="bi bi-plus text-muted"></i>
                                </div>
                                <div class="thumbnail-slot border rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #f8f9fa; cursor: pointer;" onclick="document.getElementById('thumbnail3').click()">
                                    <i class="bi bi-plus text-muted"></i>
                                </div>
                                <div class="thumbnail-slot border rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #f8f9fa; cursor: pointer;" onclick="document.getElementById('thumbnail4').click()">
                                    <i class="bi bi-plus text-muted"></i>
                                </div>
                            </div>
                            <input type="file" id="thumbnail1" name="thumbnail1" class="d-none" accept="image/*" onchange="previewThumbnail(this, 1)">
                            <input type="file" id="thumbnail2" name="thumbnail2" class="d-none" accept="image/*" onchange="previewThumbnail(this, 2)">
                            <input type="file" id="thumbnail3" name="thumbnail3" class="d-none" accept="image/*" onchange="previewThumbnail(this, 3)">
                            <input type="file" id="thumbnail4" name="thumbnail4" class="d-none" accept="image/*" onchange="previewThumbnail(this, 4)">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Product Details -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Product Details</h5>
                    </div>
                    <div class="card-body">
                        <!-- Product Name -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Product Name *</label>
                            <input type="text" class="form-control form-control-lg" name="name" placeholder="e.g., Fresh Organic Tomatoes" required>
                            <small class="text-muted">Use descriptive names that customers will search for</small>
                        </div>

                        <!-- Price Box -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Price *</label>
                            <div class="price-box bg-primary text-white rounded p-4 text-center">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-transparent border-0 text-white">₱</span>
                                    <input type="number" class="form-control form-control-lg bg-transparent border-0 text-white text-center price-display" name="price" placeholder="0.00" step="0.01" min="0" required>
                                    <span class="input-group-text bg-transparent border-0 text-white">.00</span>
                                </div>
                                <small class="opacity-75">per unit/kilogram</small>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Category *</label>
                            <select class="form-select form-select-lg" name="category" required>
                                <option value="">Select Category</option>
                                <option value="vegetables">🥬 Organic Vegetables</option>
                                <option value="seafood">🐟 Seafood</option>
                                <option value="plants">🌱 Plants (Edible/Ornamental)</option>
                                <option value="native">🏺 Native Products</option>
                            </select>
                        </div>

                        <!-- Stock Level -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Stock Level *</label>
                                <input type="number" class="form-control form-control-lg" name="stock_level" placeholder="0" min="0" required>
                                <small class="text-muted">Available quantity</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit</label>
                                <select class="form-select form-select-lg" name="unit">
                                    <option value="kg">Kilograms</option>
                                    <option value="pcs">Pieces</option>
                                    <option value="bunch">Bunch</option>
                                    <option value="pack">Pack</option>
                                </select>
                            </div>
                        </div>

                        <!-- Farm Origin -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Farm Origin *</label>
                            <input type="text" class="form-control form-control-lg" name="farm_origin" placeholder="e.g., Green Valley Organic Farm, Batangas" required>
                            <small class="text-muted">Where your product is sourced from</small>
                        </div>

                        <!-- Pre-order Option -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="available_preorder" id="availablePreorder">
                                <label class="form-check-label fw-semibold" for="availablePreorder">
                                    <i class="bi bi-clock me-2"></i>Available for Pre-order
                                </label>
                            </div>
                            <small class="text-muted">Allow customers to order before stock is available</small>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Product Description</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Describe your product's quality, taste, benefits..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                <i class="bi bi-plus-circle me-2"></i>Add Product
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg" onclick="window.location.href='{{ route('products.index') }}'">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.price-box {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border-radius: 12px;
}

.price-display {
    font-size: 2rem;
    font-weight: bold;
}

.price-display::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.thumbnail-slot {
    transition: all 0.3s ease;
}

.thumbnail-slot:hover {
    border-color: #0d6efd;
    transform: scale(1.05);
}

.thumbnail-slot img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

#mainImagePreview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.form-control-lg, .form-select-lg {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control-lg:focus, .form-select-lg:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>

<script>
function previewMainImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('mainImagePreview').innerHTML = 
                `<img src="${e.target.result}" alt="Main product image">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewThumbnail(input, index) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const thumbnailSlot = document.querySelectorAll('.thumbnail-slot')[index - 1];
            thumbnailSlot.innerHTML = `<img src="${e.target.result}" alt="Thumbnail ${index}">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form submission
document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding Product...';
    submitBtn.disabled = true;
    
    // Submit to backend
    fetch('{{ route("products.store") }}', {
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
                <i class="bi bi-check-circle me-2"></i>Product added successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Redirect to products page after 2 seconds
            setTimeout(() => {
                window.location.href = '{{ route("products.index") }}';
            }, 2000);
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

// Price formatting
document.querySelector('input[name="price"]').addEventListener('input', function(e) {
    if (e.target.value.includes('.')) {
        const parts = e.target.value.split('.');
        if (parts[1].length > 2) {
            e.target.value = parts[0] + '.' + parts[1].substring(0, 2);
        }
    }
});
</script>
@endsection
