@extends('layouts.base')

@section('title', 'Orders Report - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Orders Report</h2>
        <div>
            <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.reports.orders') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', now()->subDays(30)->toDateString()) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', now()->toDateString()) }}">
                    </div>
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
                                <th>Date</th>
                                <th>Status</th>
                                <th>Fulfillment</th>
                                <th>Total</th>
                                <th>Items</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->order_reference }}</td>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $order->order_status === 'pending' ? 'warning' : 
                                            $order->order_status === 'confirmed' ? 'info' : 
                                            $order->order_status === 'ready' ? 'primary' : 
                                            $order->order_status === 'completed' ? 'success' : 
                                            $order->order_status === 'cancelled' ? 'danger' : 'secondary' 
                                        }}">
                                            {{ ucfirst($order->order_status) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($order->fulfillment_type) }}</td>
                                    <td>₱{{ number_format($order->total ?? 0, 2) }}</td>
                                    <td>
                                        {{-- We need to get item count from order_items --}}
                                        {{-- For now, showing placeholder --}}
                                        <span class="badge bg-info">Items</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails({{ $order->id }})">
                                                <i class="bi bi-eye"></i>
                                            </button>
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

<script>
function viewOrderDetails(orderId) {
    // Placeholder for order details modal
    alert('Order details functionality coming soon for Order #' + orderId);
}
</script>
@endsection
