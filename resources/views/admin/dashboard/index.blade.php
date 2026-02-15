@extends('layouts.base')

@section('title', 'Admin Dashboard')

@section('content')
<!-- Admin Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-success d-flex justify-content-between align-items-center mb-0">
            <div>
                <i class="bi bi-shield-check me-2"></i>
                <strong>Admin Panel</strong> - You are viewing the system as an administrator
            </div>
            <a href="{{ route('home', ['view_site' => 1]) }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-eye"></i> View Site
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h2>Admin Dashboard</h2>
        <p class="text-muted">AANI Market Operations Management System</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1 text-success">{{ $stats['total_users'] }}</h4>
                        <p class="mb-0 text-muted">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1 text-success">{{ $stats['total_vendors'] }}</h4>
                        <p class="mb-0 text-muted">Total Vendors</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-shop fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1 text-success">{{ $stats['total_products'] }}</h4>
                        <p class="mb-0 text-muted">Total Products</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1 text-success">{{ $stats['total_orders'] }}</h4>
                        <p class="mb-0 text-muted">Total Orders</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart-check fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stall Statistics -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Stall Occupancy</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h3 class="text-success">{{ $stats['occupied_stalls'] }}</h3>
                        <p class="mb-0">Occupied</p>
                    </div>
                    <div class="col-6">
                        <h3 class="text-muted">{{ $stats['total_stalls'] - $stats['occupied_stalls'] }}</h3>
                        <p class="mb-0">Available</p>
                    </div>
                </div>
                <div class="progress mt-3">
                    <?php $occupancyRate = $stats['total_stalls'] > 0 ? ($stats['occupied_stalls'] / $stats['total_stalls']) * 100 : 0; ?>
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $occupancyRate }}%">
                        {{ number_format($occupancyRate, 1) }}% Occupied
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                    <a href="{{ route('admin.map.index') }}" class="btn btn-outline-success">
                        <i class="bi bi-map"></i> Edit Market Map
                    </a>
                    <a href="{{ route('stock.index') }}" class="btn btn-success">
                        <i class="bi bi-box-seam"></i> Stock Management
                    </a>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-primary">
                        <i class="bi bi-graph-up"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders & Top Vendors -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Vendor</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->business_name }}</td>
                                        <td>₱{{ number_format($order->total, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">No recent orders</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Vendors by Sales</h5>
            </div>
            <div class="card-body">
                @if($topVendors->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Orders</th>
                                    <th>Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topVendors as $vendor)
                                    <tr>
                                        <td>{{ $vendor->business_name }}</td>
                                        <td>{{ $vendor->total_orders }}</td>
                                        <td>₱{{ number_format($vendor->total_sales, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">No sales data available</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Top Products Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Products Across Market</h5>
                    <p class="text-muted mb-0 small">Best selling products from all vendors</p>
                </div>
                <div class="card-body">
                    @if($topProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Vendor</th>
                                        <th>Units Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topProducts as $product)
                                        <tr>
                                            <td>{{ $product->product_name }}</td>
                                            <td>{{ $product->vendor_name }}</td>
                                            <td>{{ $product->total_sold }}</td>
                                            <td>₱{{ number_format($product->total_revenue, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No product sales data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
