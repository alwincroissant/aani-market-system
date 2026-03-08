@extends('layouts.base')

@section('title', 'Order Details - Vendor Dashboard')

@push('styles')
<style>
    .status-save-form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .status-save-form .form-select {
        min-width: 180px;
    }

    .status-save-form .btn {
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .order-item-status-badge {
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Order Details</h2>
        <div>
            <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    @if($order->isNotEmpty())
        @php
            $orderData = $order->first();
        @endphp

        <!-- Order Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order #{{ $orderData->order_reference }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <p><strong>Name:</strong> {{ $orderData->first_name }} {{ $orderData->last_name }}</p>
                        <p><strong>Email:</strong> {{ $orderData->email }}</p>
                        <p><strong>Phone:</strong> {{ $orderData->phone }}</p>
                        @if($orderData->delivery_address)
                            <p><strong>Delivery Address:</strong> {{ $orderData->delivery_address }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <p><strong>Date:</strong> {{ $orderData->order_date->format('M d, Y H:i') }}</p>
                        <p><strong>Fulfillment:</strong> {{ ucfirst($orderData->fulfillment_type) }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ 
                                $orderData->order_status === 'pending' ? 'warning' : 
                                ($orderData->order_status === 'confirmed' ? 'info' : 
                                ($orderData->order_status === 'ready' ? 'primary' : 
                                ($orderData->order_status === 'completed' ? 'success' : 
                                ($orderData->order_status === 'cancelled' ? 'danger' : 'secondary'
                                ))))
                            }}">
                                {{ ucfirst($orderData->order_status) }}
                            </span>
                        </p>
                        @if($orderData->fulfillment_type === 'weekend_pickup' && $orderData->pickup_code && $orderData->order_status !== 'cancelled')
                            <p><strong>Pickup Code:</strong> <span class="badge bg-success fs-6">{{ $orderData->pickup_code }}</span></p>
                        @endif
                        @if($orderData->notes)
                            <p><strong>Customer Notes:</strong> {{ $orderData->notes }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <!-- Batch Update Form -->
                <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle"></i>
                        <span>Update all items in this order at once:</span>
                    </div>
                    <form action="{{ route('vendor.orders.batch-update-status', $orderData->id) }}" method="POST" class="d-flex align-items-center gap-2">
                        @csrf
                        @method('PUT')
                        <select class="form-select form-select-sm" name="item_status" style="min-width: 200px;">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="ready">Ready for Pickup</option>
                            <option value="preparing">Preparing</option>
                            <option value="awaiting_rider">Awaiting Rider</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check2-all"></i> Update All Items
                        </button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product_image_url)
                                                <img src="{{ asset($item->product_image_url) }}" alt="{{ $item->product_name }}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                                <small class="text-muted">{{ $item->description }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->quantity }} {{ $item->unit_type }}</td>
                                    <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td>₱{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                    <td>
                                        <span class="badge order-item-status-badge bg-{{
                                            $item->item_status === 'pending' ? 'warning' :
                                            ($item->item_status === 'confirmed' ? 'info' :
                                            ($item->item_status === 'ready' ? 'primary' :
                                            ($item->item_status === 'preparing' ? 'secondary' :
                                            ($item->item_status === 'awaiting_rider' ? 'warning' :
                                            ($item->item_status === 'out_for_delivery' ? 'info' :
                                            ($item->item_status === 'delivered' ? 'success' :
                                            ($item->item_status === 'completed' ? 'success' :
                                            ($item->item_status === 'cancelled' ? 'danger' : 'secondary'))))))))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->item_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('vendor.orders.items.update-status', $item->item_id) }}" method="POST" class="status-save-form">
                                            @csrf
                                            @method('PUT')
                                            <select class="form-select form-select-sm" name="item_status">
                                                <option value="pending" {{ $item->item_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="confirmed" {{ $item->item_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="ready" {{ $item->item_status === 'ready' ? 'selected' : '' }}>Ready for Pickup</option>
                                                <option value="preparing" {{ $item->item_status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                                <option value="awaiting_rider" {{ $item->item_status === 'awaiting_rider' ? 'selected' : '' }}>Awaiting Rider</option>
                                                <option value="out_for_delivery" {{ $item->item_status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                                <option value="delivered" {{ $item->item_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                <option value="completed" {{ $item->item_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $item->item_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-save"></i> Save
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Order Total -->
                <div class="row mt-4">
                    <div class="col-md-6 offset-md-6">
                        @php
                            $vendorSubtotal = $order->sum(function ($line) {
                                return ((float) $line->quantity) * ((float) $line->unit_price);
                            });
                        @endphp
                        <table class="table">
                            <tr>
                                <td><strong>Subtotal:</strong></td>
                                <td>₱{{ number_format($vendorSubtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total:</strong></td>
                                <td><strong>₱{{ number_format($vendorSubtotal, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Pickup Code Generation -->
                @if($orderData->fulfillment_type === 'weekend_pickup' && $orderData->order_status === 'ready' && !$orderData->pickup_code)
                    <div class="text-center mt-4">
                        <button class="btn btn-success" onclick="generatePickupCode({{ $orderData->id }})">
                            <i class="bi bi-qr-code"></i> Generate Pickup Code
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
function generatePickupCode(orderId) {
    fetch(`{{ route('vendor.orders.pickup-code', ':orderId') }}`.replace(':orderId', orderId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Pickup code generated: ' + data.pickup_code, 'success');
            location.reload();
        } else {
            showToast('Failed to generate pickup code: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error generating pickup code', 'error');
    });
}

function showToast(message, type) {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection
