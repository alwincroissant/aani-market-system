@extends('layouts.base')

@section('title', 'Order Details - Vendor Dashboard')

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

        <!-- Debug: Show order count -->
        <div class="alert alert-info">
            Order items found: {{ $order->count() }}
        </div>

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
                        @if($orderData->pickup_code)
                            <p><strong>Pickup Code:</strong> <span class="badge bg-success">{{ $orderData->pickup_code }}</span></p>
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
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Vendor Notes</th>
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
                                        <select class="form-select form-select-sm item-status" data-item-id="{{ $item->item_id }}">
                                            <option value="pending" {{ $item->item_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ $item->item_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="ready" {{ $item->item_status === 'ready' ? 'selected' : '' }}>Ready</option>
                                            <option value="completed" {{ $item->item_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $item->item_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>
                                        <textarea class="form-control form-control-sm vendor-notes" data-item-id="{{ $item->item_id }}" rows="2" placeholder="Add notes...">{{ $item->vendor_notes }}</textarea>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary save-item" data-item-id="{{ $item->item_id }}">
                                            <i class="bi bi-save"></i> Save
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Order Total -->
                <div class="row mt-4">
                    <div class="col-md-6 offset-md-6">
                        <table class="table">
                            <tr>
                                <td><strong>Subtotal:</strong></td>
                                <td>₱{{ number_format($order->sum('quantity * unit_price'), 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total:</strong></td>
                                <td><strong>₱{{ number_format($order->sum('quantity * unit_price'), 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Pickup Code Generation -->
                @if($orderData->fulfillment_type === 'pickup' && !$orderData->pickup_code)
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
document.addEventListener('DOMContentLoaded', function() {
    // Auto-save status changes
    document.querySelectorAll('.item-status').forEach(select => {
        select.addEventListener('change', function() {
            updateItemStatus(this.dataset.itemId, this.value);
        });
    });

    // Save button functionality
    document.querySelectorAll('.save-item').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const notes = document.querySelector(`.vendor-notes[data-item-id="${itemId}"]`).value;
            const status = document.querySelector(`.item-status[data-item-id="${itemId}"]`).value;
            
            // Update both status and notes
            updateItemAndNotes(itemId, status, notes);
        });
    });
});

function updateItemAndNotes(itemId, status, notes) {
    console.log('Updating item and notes:', itemId, status, notes);
    
    // First update status
    fetch(`{{ route('vendor.orders.items.update-status', ':itemId') }}`.replace(':itemId', itemId), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            item_status: status
        })
    })
    .then(response => {
        console.log('Status update response:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Status update data:', data);
        if (data.success) {
            // Then update notes
            fetch(`{{ route('vendor.orders.items.update-notes', ':itemId') }}`.replace(':itemId', itemId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    vendor_notes: notes
                })
            })
            .then(response => {
                console.log('Notes update response:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Notes update data:', data);
                if (data.success) {
                    showToast('Status and notes updated successfully', 'success');
                } else {
                    showToast('Status updated but failed to update notes: ' + data.message, 'warning');
                }
            })
            .catch(error => {
                console.error('Error updating notes:', error);
                showToast('Status updated but failed to update notes', 'warning');
            });
        } else {
            showToast('Failed to update status: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        showToast('Error updating status', 'error');
    });
}

function updateItemStatus(itemId, status) {
    console.log('Updating item status:', itemId, status);
    
    fetch(`{{ route('vendor.orders.items.update-status', ':itemId') }}`.replace(':itemId', itemId), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            item_status: status
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            let message = 'Status updated successfully';
            if (data.order_status_updated) {
                message = 'Status and main order updated successfully';
            }
            showToast(message, 'success');
        } else {
            showToast('Failed to update status: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        showToast('Error updating status', 'error');
    });
}

function updateVendorNotes(itemId, notes) {
    console.log('Updating vendor notes:', itemId, notes);
    
    fetch(`{{ route('vendor.orders.items.update-notes', ':itemId') }}`.replace(':itemId', itemId), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            vendor_notes: notes
        })
    })
    .then(response => {
        console.log('Notes response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Notes response data:', data);
        if (data.success) {
            showToast('Notes updated successfully', 'success');
        } else {
            showToast('Failed to update notes: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating notes:', error);
        showToast('Error updating notes', 'error');
    });
}

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
