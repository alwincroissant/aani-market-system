@extends('layouts.base')

@section('title', 'Order ' . $order->order_reference)

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

    .order-detail-page {
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

    .order-shell {
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        overflow: hidden;
        background: var(--surface);
    }

    .order-shell-head {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        background: var(--bg);
    }

    .order-shell-title {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .order-hero {
        background: linear-gradient(145deg, #f8f7f3 0%, #ffffff 70%);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        padding: 18px 20px;
    }

    .order-ref {
        font-family: 'DM Mono', monospace;
        font-size: 15px;
    }

    .order-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .order-meta-card {
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 12px;
        background: #fff;
    }

    .meta-label {
        font-size: 11px;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 700;
        margin-bottom: 5px;
    }

    .meta-value {
        font-size: 14px;
    }

    .mono-value {
        font-family: 'DM Mono', monospace;
    }

    .order-table {
        margin-bottom: 0;
    }

    .order-table thead th {
        font-size: 11px;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        border-bottom: 1px solid var(--border);
        background: #faf9f7;
        padding-top: 12px;
        padding-bottom: 12px;
    }

    .order-table tbody td {
        border-bottom-color: var(--border);
    }

    .order-table tbody tr:hover {
        background: #fcfbf8;
    }

    .order-total-row th {
        background: var(--accent-soft);
        border-top: 1px solid #d6e9dd;
        color: #155232;
    }

    .order-actions {
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
        padding: 14px;
        background: #fff;
    }

    @media (max-width: 767px) {
        .order-meta {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="container order-detail-page">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <div class="order-hero mb-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="mb-1">Order Details</h4>
                        <div class="order-ref">{{ $order->order_reference }}</div>
                    </div>
                    <div>
                        @switch($order->order_status)
                            @case('pending')
                                <span class="badge bg-warning fs-6">Pending</span>
                                @break
                            @case('confirmed')
                                <span class="badge bg-info fs-6">Confirmed</span>
                                @break
                            @case('ready')
                                <span class="badge bg-primary fs-6">Ready</span>
                                @break
                            @case('completed')
                                <span class="badge bg-success fs-6">Completed</span>
                                @break
                            @case('cancelled')
                                <span class="badge bg-danger fs-6">Cancelled</span>
                                @break
                            @default
                                <span class="badge bg-secondary fs-6">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
                        @endswitch
                        @if($order->fulfillment_type === 'weekend_pickup' && $order->order_status === 'ready' && $order->pickup_code)
                            <span class="badge bg-success fs-6 ms-1">Code: {{ $order->pickup_code }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="order-shell mb-4">
                <div class="order-shell-head">
                    <h5 class="order-shell-title"><i class="bi bi-info-circle me-1"></i> Order Information</h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="order-meta mb-3">
                        <div class="order-meta-card">
                            <div class="meta-label">Order Date</div>
                            <div class="meta-value">{{ \Carbon\Carbon::parse($order->order_date)->format('F d, Y g:i A') }}</div>
                        </div>
                        <div class="order-meta-card">
                            <div class="meta-label">Fulfillment Type</div>
                            <div class="meta-value">
                                @switch($order->fulfillment_type)
                                    @case('weekend_pickup')
                                        <i class="bi bi-shop-window me-1"></i> Weekend Pickup
                                        @break
                                    @case('weekday_delivery')
                                        <i class="bi bi-truck me-1"></i> Weekday Delivery
                                        @break
                                    @case('weekend_delivery')
                                        <i class="bi bi-truck me-1"></i> Weekend Delivery
                                        @break
                                    @default
                                        {{ ucfirst(str_replace('_', ' ', $order->fulfillment_type)) }}
                                @endswitch
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="order-meta-card h-100">
                                <div class="meta-label">Customer</div>
                                <div class="meta-value">{{ $order->first_name }} {{ $order->last_name }}</div>
                                @if($order->phone)
                                    <div class="text-muted small mt-1">{{ $order->phone }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="order-meta-card h-100">
                                <div class="meta-label">Delivery Address</div>
                                <div class="meta-value">{{ $order->delivery_address ?: 'No delivery address required for this order.' }}</div>
                            </div>
                        </div>
                        @if($order->fulfillment_type === 'weekend_pickup' && $order->pickup_code && $order->order_status !== 'cancelled')
                            <div class="col-12">
                                <div class="order-meta-card">
                                    <div class="meta-label">Pickup Code</div>
                                    <div class="meta-value mono-value">
                                        <span class="badge bg-success fs-6">{{ $order->pickup_code }}</span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-info-circle"></i>
                                        @if($order->order_status === 'ready')
                                            Bring this code to AANI Weekend Market for pickup.
                                        @else
                                            Keep this code for your pickup once the order is ready.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($order->notes)
                            <div class="col-12">
                                <div class="order-meta-card">
                                    <div class="meta-label">Notes</div>
                                    <div class="meta-value">{{ $order->notes }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="order-shell mb-4">
                <div class="order-shell-head">
                    <h5 class="order-shell-title"><i class="bi bi-box-seam me-1"></i> Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle order-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Vendor</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItems as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->business_name }}</td>
                                        <td class="mono-value">₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->quantity }} {{ $item->unit_type }}</td>
                                        <td class="mono-value">₱{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="order-total-row">
                                    <th colspan="4">Total</th>
                                    <th class="mono-value">₱{{ number_format($totalAmount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="order-actions d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Orders
                </a>
                <div>
                    @if($order->order_status === 'pending')
                        <form action="{{ route('customer.orders.cancel', $order->order_reference) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.')">
                                <i class="bi bi-x-circle"></i> Cancel Order
                            </button>
                        </form>
                    @elseif(in_array($order->order_status, ['ready', 'preparing', 'awaiting_rider', 'out_for_delivery', 'delivered']))
                        <form action="{{ route('customer.orders.mark-complete', $order->order_reference) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Mark this order as completed?')">
                                <i class="bi bi-check-circle"></i> Mark as Complete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
