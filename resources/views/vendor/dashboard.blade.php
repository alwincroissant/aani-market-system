@extends('layouts.base')

@section('title', 'Vendor Dashboard')

@section('content')

<div class="container-fluid py-4">
    {{-- Header Section with Shop Status --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="mb-1">Vendor Dashboard</h2>
                    <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6" id="vendorStatus">
                        <i class="bi bi-circle-fill me-1"></i> Store Active
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">Shop Status</h6>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="shopStatusToggle"
                                    {{ $vendor->is_live ? 'checked' : '' }}>
                                <label class="form-check-label" for="shopStatusToggle">
                                    <span id="statusText">{{ $vendor->is_live ? 'Open Now' : 'Closed' }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="text-end">
                            <i class="bi bi-shop fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Key Metrics Row --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Sales Today</h6>
                            <h3 class="mb-0 text-primary">₱{{ number_format($todaySales, 2) }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-cash-stack fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Pending Orders</h6>
                            <h3 class="mb-0 text-warning">{{ $pendingOrders }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-clock-history fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Low Stock Items</h6>
                            <h3 class="mb-0 text-danger">{{ $lowStockProducts }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Dashboard Grid --}}
    <div class="row">
        {{-- Business Insights Chart (2/3 width) --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">Business Insights</h5>
                    <p class="text-muted mb-0">Weekly Revenue Overview</p>
                </div>
                <div class="card-body">
                    <canvas id="weeklyRevenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Products (1/3 width) --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">Top Products</h5>
                    <p class="text-muted mb-0">Best Selling Items</p>
                </div>
                <div class="card-body">
                    <div id="topProductsList">
                        @if($topProducts && $topProducts->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($topProducts as $product)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $product->product_name }}</h6>
                                            <small class="text-muted">Category: {{ $product->category_id ?? 'General' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary rounded-pill">{{ $product->total_sold }} sold</span>
                                            <div class="small text-muted">₱{{ number_format($product->total_revenue, 2) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No sales data available yet</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Row --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('products.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-2"></i>Add New Product
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-box me-2"></i>Manage Products
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('stock.index') }}" class="btn btn-success w-100">
                                <i class="bi bi-box-seam me-2"></i>Stock Management
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('vendor.reports.sales') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-graph-up me-2"></i>View Reports
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('vendor.settings') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-gear me-2"></i>Store Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shop Status Toggle
    const shopStatusToggle = document.getElementById('shopStatusToggle');
    const statusText = document.getElementById('statusText');
    const vendorStatus = document.getElementById('vendorStatus');
    
    shopStatusToggle.addEventListener('change', function() {
        const isLive = this.checked;
        
        // Disable toggle during request
        this.disabled = true;
        
        fetch('{{ route("vendor.update-live-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                is_live: isLive
            })
        })
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug log
            if (data.success) {
                statusText.textContent = isLive ? 'Open Now' : 'Closed';
                vendorStatus.className = isLive ? 'badge bg-success fs-6' : 'badge bg-secondary fs-6';
                vendorStatus.innerHTML = isLive ?
                    '<i class="bi bi-circle-fill me-1"></i> Store Active' :
                    '<i class="bi bi-circle me-1"></i> Store Closed';
            } else {
                this.checked = !this.checked;
                alert('Failed to update shop status: ' + data.message);
            }
        })
        .catch(error => {
            this.checked = !this.checked;
            console.error('Error:', error);
            alert('Network error occurred. Please try again.');
        })
        .finally(() => {
            this.disabled = false; // Re-enable toggle
        });
    });

    // Weekly Revenue Chart
    const ctx = document.getElementById('weeklyRevenueChart').getContext('2d');
    const weeklySales = @json($weeklySales);
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: days,
            datasets: [{
                label: 'Daily Revenue',
                data: weeklySales,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection