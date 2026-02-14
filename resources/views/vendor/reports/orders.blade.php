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

    <!-- Export Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="{{ route('vendor.reports.orders.export-pdf') }}" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
                <a href="{{ route('vendor.reports.orders.export-csv') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-csv"></i> Export CSV
                </a>
            </div>
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
                                        @php
                                            $badgeClass = 'secondary';
                                            if ($order->order_status === 'pending') $badgeClass = 'warning';
                                            elseif ($order->order_status === 'confirmed') $badgeClass = 'info';
                                            elseif ($order->order_status === 'ready') $badgeClass = 'primary';
                                            elseif ($order->order_status === 'preparing') $badgeClass = 'secondary';
                                            elseif ($order->order_status === 'awaiting_rider') $badgeClass = 'warning';
                                            elseif ($order->order_status === 'out_for_delivery') $badgeClass = 'info';
                                            elseif ($order->order_status === 'delivered') $badgeClass = 'success';
                                            elseif ($order->order_status === 'completed') $badgeClass = 'success';
                                            elseif ($order->order_status === 'cancelled') $badgeClass = 'danger';
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">
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

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading order details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    // Show loading state
    const contentDiv = document.getElementById('orderDetailsContent');
    contentDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading order details...</p>
        </div>
    `;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    
    // Fetch order details
    fetch(`/vendor/orders/${orderId}`)
        .then(response => response.text())
        .then(html => {
            // Parse the HTML response to extract the order details
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Find the order details section
            const orderDetails = doc.querySelector('.card.mb-4');
            if (orderDetails) {
                contentDiv.innerHTML = orderDetails.innerHTML;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Order details not found.
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching order details:', error);
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Failed to load order details. Please try again.
                </div>
            `;
        });
}
</script>
@endsection
