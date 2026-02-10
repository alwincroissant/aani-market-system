@extends('layouts.base')

@section('title', 'Vendor Dashboard')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
.product-image-main {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 15px;
}

.thumbnail-carousel {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 10px 0;
}

.thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.thumbnail:hover {
    border-color: #0d6efd;
    transform: scale(1.05);
}

.thumbnail.active {
    border-color: #0d6efd;
}

.price-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin: 20px 0;
}

.price-display {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.price-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-buy-now {
    background: #0d6efd;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-buy-now:hover {
    background: #0b5ed7;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
}

.category-badge {
    background: #e9ecef;
    color: #495057;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.origin-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #0d6efd;
}

.stock-status {
    font-size: 0.9rem;
    font-weight: 600;
}

.stock-low {
    color: #dc3545;
}

.stock-good {
    color: #198754;
}

.pre-order-badge {
    background: #ffc107;
    color: #212529;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Vendor Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">Vendor Dashboard</h4>
                            <p class="text-muted mb-0">Manage your products and store settings</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input" type="checkbox" id="liveStatusToggle" {{ auth()->user()->vendor->is_live ?? false ? 'checked' : '' }}>
                                <label class="form-check-label" for="liveStatusToggle">
                                    <i class="bi bi-broadcast"></i> Live Now
                                </label>
                            </div>
                            <button class="btn btn-outline-primary ms-3" onclick="uploadStallBanner()">
                                <i class="bi bi-image"></i> Upload Banner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Sales Today</h5>
                    <h3>₱{{ number_format(1250, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Orders</h5>
                    <h3>{{ 8 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Alerts</h5>
                    <h3>{{ 3 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Weekly Sales</h5>
                    <canvas id="weeklySalesChart" width="100" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Management Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Product Management</h5>
                </div>
                <div class="card-body">
                    <!-- Add New Product Button -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <button class="btn btn-primary" onclick="showAddProductModal()">
                                <i class="bi bi-plus-circle"></i> Add New Product
                            </button>
                        </div>
                    </div>

                    <!-- Sample Product Display (Shopee Layout) -->
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Left Column - Image and Thumbnails -->
                            <div class="position-sticky" style="top: 20px;">
                                <img src="https://via.placeholder.com/600x400/28a745/ffffff?text=Fresh+Tomatoes" 
                                     alt="Product Image" 
                                     class="product-image-main"
                                     id="mainProductImage">
                                
                                <!-- Thumbnail Carousel -->
                                <div class="thumbnail-carousel">
                                    <img src="https://via.placeholder.com/80x80/28a745/ffffff?text=Tomato+1" 
                                         alt="Thumbnail 1" 
                                         class="thumbnail active"
                                         onclick="changeMainImage('https://via.placeholder.com/600x400/28a745/ffffff?text=Fresh+Tomatoes')">
                                    <img src="https://via.placeholder.com/80x80/28a745/ffffff?text=Tomato+2" 
                                         alt="Thumbnail 2" 
                                         class="thumbnail"
                                         onclick="changeMainImage('https://via.placeholder.com/600x400/28a745/ffffff?text=Fresh+Tomatoes+Side')">
                                    <img src="https://via.placeholder.com/80x80/28a745/ffffff?text=Tomato+3" 
                                         alt="Thumbnail 3" 
                                         class="thumbnail"
                                         onclick="changeMainImage('https://via.placeholder.com/600x400/28a745/ffffff?text=Fresh+Tomatoes+Top')">
                                    <img src="https://via.placeholder.com/80x80/28a745/ffffff?text=Tomato+4" 
                                         alt="Thumbnail 4" 
                                         class="thumbnail"
                                         onclick="changeMainImage('https://via.placeholder.com/600x400/28a745/ffffff?text=Fresh+Tomatoes+Package')">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Right Column - Product Details -->
                            <h1>Fresh Organic Tomatoes</h1>
                            
                            <!-- Price Box -->
                            <div class="price-box">
                                <div class="price-display">₱120.00</div>
                                <div class="price-label">per kilogram</div>
                            </div>
                            
                            <!-- Category and Origin -->
                            <div class="mb-3">
                                <span class="category-badge">
                                    <i class="bi bi-basket"></i> Organic Vegetables
                                </span>
                            </div>
                            
                            <div class="origin-info">
                                <h6 class="mb-2"><i class="bi bi-geo-alt"></i> Farm Origin</h6>
                                <p class="mb-1"><strong>Farm:</strong> Green Valley Organic Farm</p>
                                <p class="mb-1"><strong>Location:</strong> Batangas, Philippines</p>
                                <p class="mb-0"><strong>Harvest Date:</strong> February 8, 2026</p>
                            </div>
                            
                            <!-- Stock Status -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="stock-status stock-good">
                                        <i class="bi bi-check-circle"></i> In Stock (50 kg available)
                                    </span>
                                    <span class="pre-order-badge">
                                        <i class="bi bi-clock"></i> Available for Pre-order
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="action-buttons">
                                <button class="btn btn-buy-now flex-fill" onclick="buyNow()">
                                    <i class="bi bi-cart-plus"></i> Buy Now
                                </button>
                                <button class="btn btn-outline-secondary" onclick="editProduct()">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="product_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price (₱)</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="vegetables">Organic Vegetables</option>
                                    <option value="seafood">Seafood</option>
                                    <option value="plants">Plants (Edible/Ornamental)</option>
                                    <option value="native">Native Products</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stock Level</label>
                                <input type="number" class="form-control" name="stock_level" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Farm Location/Origin</label>
                        <input type="text" class="form-control" name="farm_origin" placeholder="e.g., Green Valley Organic Farm, Batangas" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="available_preorder" id="availablePreorder">
                            <label class="form-check-label" for="availablePreorder">
                                <i class="bi bi-clock"></i> Available for Pre-order
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Save Product</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function changeMainImage(src) {
    document.getElementById('mainProductImage').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    event.target.classList.add('active');
}

function showAddProductModal() {
    const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
    modal.show();
}

function saveProduct() {
    const form = document.getElementById('addProductForm');
    const formData = new FormData(form);
    
    // Here you would typically send this to your backend
    console.log('Saving product:', Object.fromEntries(formData));
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
    
    // Show success message
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="bi bi-check-circle"></i> Product added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container-fluid').prepend(alertDiv);
    
    // Clear form
    form.reset();
}

function buyNow() {
    console.log('Buy Now clicked');
    // Implement buy now functionality
}

function editProduct() {
    console.log('Edit Product clicked');
    // Implement edit functionality
}

function uploadStallBanner() {
    console.log('Upload Stall Banner clicked');
    // Implement banner upload functionality
}

// Live Status Toggle
document.getElementById('liveStatusToggle').addEventListener('change', function() {
    const isLive = this.checked;
    
    // Send to backend
    fetch('/vendor/update-live-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ is_live: isLive })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle"></i> Store status updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').prepend(alertDiv);
        }
    })
    .catch(error => console.error('Error updating live status:', error));
});

// Weekly Sales Chart
const ctx = document.getElementById('weeklySalesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Sales (₱)',
            data: [1200, 1900, 1500, 2100, 2300, 1800, 2500],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush
