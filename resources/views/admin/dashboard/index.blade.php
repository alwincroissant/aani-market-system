@extends('layouts.base')
@section('title', 'Admin Dashboard')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --bg:        #F5F4F0;
        --surface:   #FFFFFF;
        --border:    #E4E2DC;
        --text:      #1A1916;
        --muted:     #7A7871;
        --accent:    #1D6F42;
        --accent-lt: #EAF4EE;
        --danger:    #C0392B;
        --warn:      #D97706;
        --warn-lt:   #FEF3C7;
        --radius:    10px;
        --shadow:    0 1px 3px rgba(0,0,0,.07), 0 4px 12px rgba(0,0,0,.04);
    }

    /* Admin dashboard overrides */
    .admin-page { background: var(--bg); }

    /* Page sections */
    .page-header { margin-bottom: 24px; }
    .page-header h1 { font-size: 24px; font-weight: 600; color: var(--text); }
    .page-header p  { font-size: 13px; color: var(--muted); margin-top: 4px; }

    /* Stat Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 14px;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px 20px 16px;
        box-shadow: var(--shadow);
    }
    .stat-label {
        font-size: 11.5px;
        font-weight: 500;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 10px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 600;
        font-family: 'DM Mono', monospace;
        line-height: 1;
        color: var(--text);
    }
    .stat-sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 6px;
    }

    /* Occupancy card */
    .occupancy-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 24px;
    }
    .occupancy-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .occupancy-header .stat-label { margin: 0; }
    .occupancy-pct {
        font-size: 13px;
        font-weight: 600;
        font-family: 'DM Mono', monospace;
        color: var(--accent);
    }

    .progress-track {
        height: 7px;
        background: var(--bg);
        border-radius: 99px;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .progress-fill {
        height: 100%;
        background: var(--accent);
        border-radius: 99px;
        transition: width .6s ease;
    }

    .occupancy-meta {
        display: flex;
        gap: 20px;
    }
    .occ-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: var(--muted);
    }
    .occ-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .occ-dot.occupied  { background: var(--accent); }
    .occ-dot.available { background: var(--border); }
    .occ-item strong   { color: var(--text); font-weight: 600; font-family: 'DM Mono', monospace; }

    /* Quick Actions */
    .quick-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        transition: all .15s;
        box-shadow: var(--shadow);
    }
    .action-btn:hover { background: var(--bg); border-color: #ccc; }
    .action-btn.primary {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
    }
    .action-btn.primary:hover { background: #185c37; }
    .action-btn svg { width: 14px; height: 14px; }

    /* Section Header */
    .section-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .section-title { font-size: 14px; font-weight: 600; color: var(--text); }
    .section-sub   { font-size: 12px; color: var(--muted); margin-top: 1px; }
    .section-link  { font-size: 12.5px; color: var(--accent); text-decoration: none; font-weight: 500; }
    .section-link:hover { text-decoration: underline; }

    /* Tables */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    table { width: 100%; border-collapse: collapse; font-family: 'DM Sans', sans-serif; }
    thead tr { background: var(--bg); border-bottom: 1px solid var(--border); }
    th {
        text-align: left;
        padding: 10px 16px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--muted);
    }
    td {
        padding: 11px 16px;
        font-size: 13.5px;
        border-bottom: 1px solid var(--border);
        color: var(--text);
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #faf9f7; }

    .mono { font-family: 'DM Mono', monospace; font-size: 13px; }

    /* Status badges */
    .badge {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 99px;
        font-size: 11.5px;
        font-weight: 500;
    }
    .badge-pending   { background: var(--warn-lt);  color: var(--warn); }
    .badge-confirmed,
    .badge-ready,
    .badge-preparing,
    .badge-awaiting_rider,
    .badge-out_for_delivery { background: #e8eef7; color: #1f4f82; }
    .badge-completed { background: var(--accent-lt); color: var(--accent); }
    .badge-delivered { background: #e1f3ed; color: #176b4b; }
    .badge-cancelled { background: #fde8e7; color: var(--danger); }

    /* Two-col grid */
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 36px 20px;
        color: var(--muted);
        font-size: 13.5px;
    }

    @media (max-width: 1100px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .two-col    { grid-template-columns: 1fr; }
    }
    @media (max-width: 720px) {
        .stats-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="container" style="padding: 20px;">

        {{-- Page Header --}}
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>AANI Market Operations Management System</p>
        </div>

            {{-- Stat Cards --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value">{{ $stats['total_users'] }}</div>
                    <div class="stat-sub">Registered accounts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Vendors</div>
                    <div class="stat-value">{{ $stats['total_vendors'] }}</div>
                    <div class="stat-sub">Active market stalls</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value">{{ $stats['total_products'] }}</div>
                    <div class="stat-sub">Across all vendors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value">{{ $stats['total_orders'] }}</div>
                    <div class="stat-sub">All time</div>
                </div>
            </div>

            {{-- Stall Occupancy --}}
            @php
                $occupancyRate = $stats['total_stalls'] > 0
                    ? ($stats['occupied_stalls'] / $stats['total_stalls']) * 100
                    : 0;
            @endphp
            <div class="occupancy-card">
                <div class="occupancy-header">
                    <div class="stat-label">Stall Occupancy</div>
                    <div class="occupancy-pct">{{ number_format($occupancyRate, 1) }}% Occupied</div>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $occupancyRate }}%"></div>
                </div>
                <div class="occupancy-meta">
                    <div class="occ-item">
                        <span class="occ-dot occupied"></span>
                        <strong>{{ $stats['occupied_stalls'] }}</strong> Occupied
                    </div>
                    <div class="occ-item">
                        <span class="occ-dot available"></span>
                        <strong>{{ $stats['total_stalls'] - $stats['occupied_stalls'] }}</strong> Available
                    </div>
                    <div class="occ-item">
                        <strong>{{ $stats['total_stalls'] }}</strong> Total Stalls
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="section-header">
                <div>
                    <div class="section-title">Quick Actions</div>
                </div>
            </div>
            <div class="quick-actions" style="margin-bottom: 28px;">
                <a href="{{ route('admin.users.index') }}" class="action-btn primary">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    Manage Users
                </a>
                <a href="{{ route('admin.map.index') }}" class="action-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Edit Market Map
                </a>
                <a href="{{ route('stock.index') }}" class="action-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 01-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 011-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 011.52 0C14.51 3.81 17 5 19 5a1 1 0 011 1z"/></svg>
                    Manage Stocks
                </a>
                <a href="{{ route('admin.reports.sales') }}" class="action-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    View Reports
                </a>
                <a href="{{ route('admin.stall-payments') }}" class="action-btn">
                    <i class="bi bi-cash-coin"></i> Vendor Stall Payments
                </a>
            </div>

            {{-- Tables Row --}}
            <div class="two-col">

                {{-- Recent Orders --}}
                <div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Recent Orders</div>
                        </div>
                        <span class="section-sub">Last 5 orders</span>
                    </div>
                    <div class="card">
                        @if($recentOrders->count() > 0)
                            <table>
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
                                        <td class="mono">{{ $order->order_number }}</td>
                                        <td>{{ $order->business_name }}</td>
                                        <td class="mono">₱{{ number_format($order->total, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ strtolower($order->status) }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="empty-state">No recent orders</div>
                        @endif
                    </div>
                </div>

                {{-- Top Vendors --}}
                <div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Top Vendors by Sales</div>
                        </div>
                        <span class="section-sub">Top 5 vendors</span>
                    </div>
                    <div class="card">
                        @if($topVendors->count() > 0)
                            <table>
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
                                        <td class="mono">{{ $vendor->total_orders }}</td>
                                        <td class="mono">₱{{ number_format($vendor->total_sales, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="empty-state">No sales data available</div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Top Products --}}
            <div class="section-header">
                <div>
                    <div class="section-title">Top Products Across Market</div>
                    <div class="section-sub">Best selling products from all vendors</div>
                </div>
                <span class="section-sub">Top 10 products</span>
            </div>
            <div class="card">
                @if($topProducts->count() > 0)
                    <table>
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
                                <td style="color: var(--muted);">{{ $product->vendor_name }}</td>
                                <td class="mono">{{ $product->total_sold }}</td>
                                <td class="mono">₱{{ number_format($product->total_revenue, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">No product sales data available</div>
                @endif
            </div>

    </div>{{-- /container --}}
</div>{{-- /admin-page --}}
@endsection