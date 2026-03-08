@extends('layouts.base')

@section('title', 'Vendor Dashboard')

@section('content')

<style>
    .vendor-dashboard {
        --bg: #F5F4F0;
        --surface: #FFFFFF;
        --border: #E4E2DC;
        --text: #1A1916;
        --muted: #7A7871;
        --accent: #1D6F42;
        --accent-lt: #EAF4EE;
        --accent-dk: #155232;
        --warm: #D97706;
        --shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.05);

        background: var(--bg);
        color: var(--text);
        border-radius: 12px;
        padding: 8px;
    }

    .vendor-dashboard .card {
        background: var(--surface);
        border: 1px solid var(--border) !important;
        box-shadow: var(--shadow) !important;
    }

    .vendor-dashboard h2,
    .vendor-dashboard h3,
    .vendor-dashboard h5,
    .vendor-dashboard h6 {
        color: var(--text);
    }

    .vendor-dashboard .text-muted {
        color: var(--muted) !important;
    }

    .vendor-dashboard .bg-primary,
    .vendor-dashboard .btn-primary,
    .vendor-dashboard .badge.bg-primary {
        background-color: var(--accent) !important;
        border-color: var(--accent) !important;
        color: #fff !important;
    }

    .vendor-dashboard .btn-primary:hover {
        background-color: var(--accent-dk) !important;
        border-color: var(--accent-dk) !important;
    }

    .vendor-dashboard .btn-outline-primary {
        color: var(--accent) !important;
        border-color: #b7d7c3 !important;
    }

    .vendor-dashboard .btn-outline-primary:hover {
        background-color: var(--accent-lt) !important;
        border-color: #8fbfa5 !important;
        color: var(--accent-dk) !important;
    }

    .vendor-dashboard .text-primary {
        color: var(--accent) !important;
    }

    .vendor-dashboard .text-info {
        color: var(--accent-dk) !important;
    }

    .vendor-dashboard .border-info {
        border-color: #8fbfa5 !important;
    }

    .vendor-dashboard .btn-outline-warning {
        color: var(--warm) !important;
        border-color: #f2c588 !important;
    }

    .vendor-dashboard .btn-outline-warning:hover {
        background-color: #FEF3C7 !important;
        color: #92400e !important;
        border-color: #f2c588 !important;
    }

    .vendor-dashboard .shop-status-card,
    .vendor-dashboard .shop-status-card h6,
    .vendor-dashboard .shop-status-card .form-check-label,
    .vendor-dashboard .shop-status-card #statusText,
    .vendor-dashboard .shop-status-card i {
        color: #fff !important;
    }
</style>

<div class="container-fluid py-4 vendor-dashboard">
    {{-- Header Section with Shop Status --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="mb-1">Vendor Dashboard</h2>
                    <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
                </div>
                <div>
                    <span class="badge {{ $vendor->is_live ? 'bg-success' : 'bg-secondary' }} fs-6" id="vendorStatus">
                        @if($vendor->is_live)
                            <i class="bi bi-circle-fill me-1"></i> Store Active
                        @else
                            <i class="bi bi-circle me-1"></i> Store Closed
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white shop-status-card">
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

    {{-- Unpaid Bills Alert --}}
    @if($unpaidBillsCount > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm d-flex justify-content-between align-items-center" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Unpaid Bills</h5>
                        <p class="mb-0">
                            You have <strong>{{ $unpaidBillsCount }}</strong> unpaid bill{{ $unpaidBillsCount > 1 ? 's' : '' }} 
                            totaling <strong>₱{{ number_format($totalUnpaidAmount, 2) }}</strong>
                        </p>
                        @if($unpaidBills->where('status', 'overdue')->count() > 0)
                            <small class="text-danger">
                                <i class="bi bi-clock-fill"></i> {{ $unpaidBills->where('status', 'overdue')->count() }} overdue payment(s)
                            </small>
                        @endif
                    </div>
                </div>
                <a href="{{ route('vendor.stall-payments') }}" class="btn btn-warning">
                    <i class="bi bi-cash-coin me-1"></i> Pay Now
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Key Metrics Row --}}
    <div class="row mb-4">
        <div class="col-md-3">
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
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-3 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Physical Sales</h6>
                            <h3 class="mb-0 text-success">₱{{ number_format($todayPhysicalSales, 2) }}</h3>
                            <small class="text-muted">Walk-in / Weekend</small>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-shop-window fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-3 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Online Sales</h6>
                            <h3 class="mb-0 text-info">₱{{ number_format($todayOnlineSales, 2) }}</h3>
                            <small class="text-muted">Orders / Delivery</small>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-globe fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Unfulfilled Orders</h6>
                            <h3 class="mb-0 text-warning">{{ $unfulfilledOrders }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-clock-history fs-1"></i>
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
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('stock.index') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-box-seam me-2"></i>Manage Stocks
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('vendor.stall-payments') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-cash-coin me-2"></i>Stall Payment
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('vendor.walk-in-sales.create') }}" class="btn btn-success w-100">
                                <i class="bi bi-shop-window me-2"></i>Record Physical Sale
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('vendor.walk-in-sales.index') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-list-check me-2"></i>View Physical Sales
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
                window.location.reload();
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
                borderColor: '#1D6F42',
                backgroundColor: 'rgba(29, 111, 66, 0.12)',
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