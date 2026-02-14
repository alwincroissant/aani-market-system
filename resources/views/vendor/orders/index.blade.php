@extends('layouts.base')

@section('title', 'Orders - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">My Orders</h2>
        <div>
            <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.orders.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Fulfillment</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_reference }}</strong>
                                        @if($order->pickup_code)
                                            <br><small class="text-muted">Pickup: {{ $order->pickup_code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $order->first_name }} {{ $order->last_name }}
                                        <br><small class="text-muted">{{ $order->email }}</small>
                                    </td>
                                    <td>{{ $order->order_date->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $order->order_status === 'pending' ? 'warning' : 
                                            ($order->order_status === 'confirmed' ? 'info' : 
                                            ($order->order_status === 'ready' ? 'primary' : 
                                            ($order->order_status === 'preparing' ? 'secondary' : 
                                            ($order->order_status === 'awaiting_rider' ? 'warning' : 
                                            ($order->order_status === 'out_for_delivery' ? 'info' : 
                                            ($order->order_status === 'delivered' ? 'success' : 
                                            ($order->order_status === 'completed' ? 'success' : 
                                            ($order->order_status === 'cancelled' ? 'danger' : 'secondary'
                                            ))))))))
                                        }}">
                                            {{ ucfirst($order->order_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $order->fulfillment_type === 'pickup' ? 'success' : 'info' 
                                        }}">
                                            {{ ucfirst($order->fulfillment_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->item_count }}</td>
                                    <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                    <h4 class="text-muted">No orders found</h4>
                    <p class="text-muted">No orders match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
