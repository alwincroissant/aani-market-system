@extends('layouts.base')

@section('title', 'Orders Management')

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

    .admin-page { background: var(--bg); padding: 20px 0; }

    .page-header { margin-bottom: 24px; }
    .page-header h2, .page-header h4 { font-size: 24px; font-weight: 600; color: var(--text); margin: 0; }
    .page-header p  { font-size: 13px; color: var(--muted); margin-top: 4px; }

    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-body { padding: 20px; }

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

    .badge {
        display: inline-block;
        padding: 4px 10px;
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

    .btn-group {
        display: flex;
        gap: 6px;
    }
    .btn-outline-primary {
        padding: 6px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        color: var(--text);
        background: transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
    }
    .btn-outline-primary:hover { background: var(--bg); border-color: #999; }

    .form-control, .form-select {
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 13px;
        color: var(--text);
        background-color: white;
    }
    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-lt);
    }

    .filter-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .empty-state {
        text-align: center;
        padding: 36px 20px;
        color: var(--muted);
        font-size: 13.5px;
    }

    .mono { font-family: 'DM Mono', monospace; font-size: 13px; }

    @media (max-width: 768px) {
        .filter-section { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="admin-page">
<div class="container" style="padding: 20px;">
    <div style="margin-bottom: 24px;">
        <h2 style="margin: 0 0 4px 0; font-size: 24px; font-weight: 600; color: var(--text);">Orders Management</h2>
        <p style="margin: 0; font-size: 13px; color: var(--muted);">View and manage all market orders</p>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="{{ route('admin.orders.index') }}">
                <div class="filter-section">
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    <select name="fulfillment_type" id="fulfillment_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($fulfillmentTypes as $type)
                            <option value="{{ $type }}" {{ request('fulfillment_type') == $type ? 'selected' : '' }}>
                                @switch($type)
                                    @case('weekend_pickup')
                                        Weekend Pickup
                                        @break
                                    @case('weekday_delivery')
                                        Weekday Delivery
                                        @break
                                    @case('weekend_delivery')
                                        Weekend Delivery
                                        @break
                                @endswitch
                            </option>
                        @endforeach
                    </select>
                    <select name="has_pickup_code" id="has_pickup_code" class="form-select">
                        <option value="">All Orders</option>
                        <option value="yes" {{ request('has_pickup_code') == 'yes' ? 'selected' : '' }}>Has Pickup Code</option>
                        <option value="no" {{ request('has_pickup_code') == 'no' ? 'selected' : '' }}>No Pickup Code</option>
                    </select>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Order #, Name, Email" value="{{ request('search') }}">
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                    <button type="submit" class="btn-outline-primary" style="width: 100%;">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-funnel"></i> Filter
                                        </button>
                                    </div>
                                    <button type="submit" class="btn-outline-primary" style="width: 100%;">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            @if($orders->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Order Reference</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Pickup Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><strong class="mono">{{ $order->order_reference }}</strong></td>
                                <td>{{ $order->first_name }} {{ $order->last_name }}</td>
                                <td>{{ $order->email }}</td>
                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($order->order_status) }}">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>
                                <td>
                                    @switch($order->fulfillment_type)
                                        @case('weekend_pickup')
                                            <span>🏪 Pickup</span>
                                            @break
                                        @case('weekday_delivery')
                                            <span>🚚 Weekday</span>
                                            @break
                                        @case('weekend_delivery')
                                            <span>🚚 Weekend</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="mono">{{ $order->item_count }}</td>
                                <td class="mono">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    @if($order->pickup_code)
                                        <span class="badge badge-completed">{{ $order->pickup_code }}</span>
                                    @else
                                        <span style="color: var(--muted);">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--muted);"></i>
                    <h5 style="margin-top: 12px; color: var(--text);">No orders found</h5>
                    <p>No orders match your search criteria.</p>
                </div>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
