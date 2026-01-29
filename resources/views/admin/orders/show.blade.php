@extends('layouts.base')

@section('title', 'Order ' . $order->order_reference)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Order {{ $order->order_reference }}</h4>
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
                            @endswitch
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p class="mb-1"><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('F d, Y g:i A') }}</p>
                            <p class="mb-1"><strong>Fulfillment Type:</strong> 
                                @switch($order->fulfillment_type)
                                    @case('weekend_pickup')
                                        <span>🏪 Weekend Pickup</span>
                                        @break
                                    @case('weekday_delivery')
                                        <span>🚚 Weekday Delivery</span>
                                        @break
                                    @case('weekend_delivery')
                                        <span>🚚 Weekend Delivery</span>
                                        @break
                                @endswitch
                            </p>
                            @if($order->delivery_address)
                                <p class="mb-1"><strong>Delivery Address:</strong><br>{{ $order->delivery_address }}</p>
                            @endif
                            @if($order->pickup_code)
                                <p class="mb-1"><strong>Pickup Code:</strong> <span class="badge bg-success fs-6">{{ $order->pickup_code }}</span></p>
                            @endif
                            @if($order->notes)
                                <p class="mb-1"><strong>Notes:</strong><br>{{ $order->notes }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $order->first_name }} {{ $order->last_name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>
                            @if($order->phone)
                                <p class="mb-1"><strong>Phone:</strong> {{ $order->phone }}</p>
                            @endif
                            
                            <div class="mt-3">
                                <h6>Update Status</h6>
                                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-8">
                                            <select name="order_status" class="form-select">
                                                <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="ready" {{ $order->order_status === 'ready' ? 'selected' : '' }}>Ready for Pickup</option>
                                                <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-primary w-100">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Vendor</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItems as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->business_name }}</td>
                                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->quantity }} {{ $item->unit_type }}</td>
                                        <td>₱{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                                        <td>
                                            @switch($item->item_status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge bg-info">Confirmed</span>
                                                    @break
                                                @case('ready')
                                                    <span class="badge bg-primary">Ready</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Completed</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                    @break
                                            @endswitch
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pickup Code Verification</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="pickupCodeInput" class="form-label">Enter Pickup Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="pickupCodeInput" placeholder="Enter 6-digit code" maxlength="8">
                            <button class="btn btn-primary" onclick="verifyPickupCode()">Verify</button>
                        </div>
                    </div>
                    
                    <div id="verificationResult" class="d-none"></div>
                    
                    <div id="orderDetails" class="d-none">
                        <h6>Order Details</h6>
                        <div id="orderInfo"></div>
                        <button class="btn btn-success w-100 mt-2" onclick="markPickupUsed()" id="markUsedBtn">Mark as Picked Up</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function verifyPickupCode() {
    const code = document.getElementById('pickupCodeInput').value.trim();
    const resultDiv = document.getElementById('verificationResult');
    const orderDetailsDiv = document.getElementById('orderDetails');
    const orderInfoDiv = document.getElementById('orderInfo');
    
    if (!code) {
        showResult('Please enter a pickup code.', 'danger');
        return;
    }
    
    fetch('{{ route("admin.orders.verifyPickupCode") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ pickup_code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const order = data.order;
            showResult('Pickup code verified successfully!', 'success');
            
            orderInfoDiv.innerHTML = `
                <p><strong>Order:</strong> ${order.order_reference}</p>
                <p><strong>Customer:</strong> ${order.first_name} ${order.last_name}</p>
                <p><strong>Type:</strong> ${order.fulfillment_type.replace('_', ' ')}</p>
                <p><strong>Status:</strong> <span class="badge bg-primary">${order.order_status}</span></p>
            `;
            
            orderDetailsDiv.classList.remove('d-none');
        } else {
            showResult(data.message, 'danger');
            orderDetailsDiv.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResult('Error verifying pickup code.', 'danger');
    });
}

function markPickupUsed() {
    const code = document.getElementById('pickupCodeInput').value.trim();
    
    fetch('{{ route("admin.orders.markPickupUsed") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ pickup_code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResult(data.message, 'success');
            document.getElementById('orderDetails').classList.add('d-none');
            document.getElementById('pickupCodeInput').value = '';
            
            // Reload page after 2 seconds to show updated status
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showResult(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResult('Error marking pickup as used.', 'danger');
    });
}

function showResult(message, type) {
    const resultDiv = document.getElementById('verificationResult');
    resultDiv.className = `alert alert-${type}`;
    resultDiv.textContent = message;
    resultDiv.classList.remove('d-none');
}
</script>
@endsection
