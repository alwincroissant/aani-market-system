@extends('layouts.base')

@section('title', 'My Orders')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

    .orders-page {
        --bg: #f5f4f0;
        --surface: #ffffff;
        --border: #e4e2dc;
        --text: #1a1916;
        --muted: #7a7871;
        --accent: #1d6f42;
        --accent-soft: #eaf4ee;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 14px rgba(0, 0, 0, .05);
        font-family: 'DM Sans', sans-serif;
        color: var(--text);
    }

    .orders-head {
        background: linear-gradient(145deg, #f8f7f3 0%, #ffffff 70%);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        padding: 18px 20px;
    }

    .orders-subtitle {
        color: var(--muted);
        margin-bottom: 0;
    }

    .orders-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        box-shadow: var(--shadow);
        padding: 14px 16px;
        height: 100%;
    }

    .orders-stat-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .orders-stat-value {
        font-family: 'DM Mono', monospace;
        font-size: 24px;
        line-height: 1.2;
        color: var(--accent);
    }

    .orders-filter-card,
    .orders-table-card,
    .orders-empty-card {
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .orders-filter-head,
    .orders-table-head {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .orders-filter-title {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .orders-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: var(--accent);
        background: var(--accent-soft);
        border: 1px solid #cce2d4;
        border-radius: 999px;
        padding: 3px 9px;
        white-space: nowrap;
    }

    .orders-filter-body {
        padding: 16px;
        background: var(--surface);
    }

    .orders-filter-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .orders-filter-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .orders-filter-body .form-select {
        border-color: var(--border);
    }

    .orders-table-head h5 {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .orders-table-card .card-body {
        padding: 0;
    }

    .orders-table {
        margin-bottom: 0;
    }

    .orders-table thead th {
        font-size: 11px;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        border-bottom: 1px solid var(--border);
        background: #faf9f7;
        padding-top: 12px;
        padding-bottom: 12px;
    }

    .orders-table tbody td {
        border-bottom-color: var(--border);
    }

    .orders-table tbody tr:hover {
        background: #fcfbf8;
    }

    .orders-total {
        font-family: 'DM Mono', monospace;
        color: var(--accent);
    }

    .orders-empty-card .card-body {
        padding-top: 56px;
        padding-bottom: 56px;
    }

    @media (max-width: 767px) {
        .orders-head {
            padding: 14px;
        }

        .orders-filter-head,
        .orders-table-head {
            flex-wrap: wrap;
        }
    }
</style>
@endpush

@section('content')
<div class="container orders-page">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <div class="orders-head d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1"><i class="bi bi-receipt"></i> My Orders</h2>
                    <p class="orders-subtitle">Track your recent purchases and fulfillment updates.</p>
                </div>
                <a href="{{ route('shop.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-shop"></i> Continue Shopping
                </a>
            </div>

            <div class="mb-4">
                <div class="row g-3 text-center text-md-start">
                    <div class="col-md-4">
                        <div class="orders-stat">
                            <div class="orders-stat-label">Total Orders</div>
                            <div class="orders-stat-value">{{ $orders->count() }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="orders-stat">
                            <div class="orders-stat-label">Active Orders</div>
                            <div class="orders-stat-value">{{ $orders->whereIn('order_status', ['pending', 'confirmed', 'ready', 'preparing', 'awaiting_rider', 'out_for_delivery', 'delivered'])->count() }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="orders-stat">
                            <div class="orders-stat-label">Completed Orders</div>
                            <div class="orders-stat-value">{{ $orders->where('order_status', 'completed')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 orders-filter-card">
                <div class="orders-filter-head">
                    <h6 class="orders-filter-title mb-0"><i class="bi bi-funnel me-1"></i> Order Filters</h6>
                    @if(!empty($selectedStatus) && !empty($availableStatuses[$selectedStatus]))
                        <span class="orders-filter-chip">
                            <i class="bi bi-tag"></i> {{ $availableStatuses[$selectedStatus] }}
                        </span>
                    @else
                        <span class="text-muted small">Showing all statuses</span>
                    @endif
                </div>
                <div class="orders-filter-body">
                    <form method="GET" action="{{ route('customer.orders.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="status" class="orders-filter-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach(($availableStatuses ?? []) as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" {{ ($selectedStatus ?? null) === $statusValue ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 orders-filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel"></i> Apply Filter
                            </button>
                            <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($orders->count() > 0)
                <div class="card orders-table-card">
                    <div class="orders-table-head">
                        <h5><i class="bi bi-list-ul me-1"></i> Order History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle orders-table">
                                <thead>
                                    <tr>
                                        <th>Order Reference</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Fulfillment Type</th>
                                        <th>Total Amount</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong>{{ $order->order_reference }}</strong>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</td>
                                            <td>
                                                @switch($order->order_status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                        @break
                                                    @case('confirmed')
                                                        <span class="badge bg-info">Confirmed</span>
                                                        @break
                                                    @case('ready')
                                                        <span class="badge bg-primary">Ready</span>
                                                        @break
                                                    @case('preparing')
                                                        <span class="badge bg-secondary">Preparing</span>
                                                        @break
                                                    @case('awaiting_rider')
                                                        <span class="badge bg-warning">Awaiting Rider</span>
                                                        @break
                                                    @case('out_for_delivery')
                                                        <span class="badge bg-info">Out for Delivery</span>
                                                        @break
                                                    @case('delivered')
                                                        <span class="badge bg-success">Delivered</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">Completed</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">Cancelled</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @switch($order->fulfillment_type)
                                                    @case('weekend_pickup')
                                                        <span class="text-muted"><i class="bi bi-shop-window"></i> Weekend Pickup</span>
                                                        @break
                                                    @case('weekday_delivery')
                                                        <span class="text-muted"><i class="bi bi-truck"></i> Weekday Delivery</span>
                                                        @break
                                                    @case('weekend_delivery')
                                                        <span class="text-muted"><i class="bi bi-truck"></i> Weekend Delivery</span>
                                                        @break
                                                    @default
                                                        <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $order->fulfillment_type)) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <span class="orders-total">₱{{ number_format($order->total_amount ?? 0, 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('customer.orders.show', $order->order_reference) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card orders-empty-card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-5 text-muted"></i>
                        <h5 class="mt-3">No Orders Found</h5>
                        <p class="text-muted mb-4">
                            @if(!empty($selectedStatus) && !empty($availableStatuses[$selectedStatus]))
                                You don't have any {{ strtolower($availableStatuses[$selectedStatus]) }} orders right now.
                            @else
                                You haven't placed any orders yet. Start shopping to see your orders here.
                            @endif
                        </p>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary">
                            <i class="bi bi-shop"></i> Browse Shops
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
