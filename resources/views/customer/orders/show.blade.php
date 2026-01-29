@extends('layouts.base')

@section('title', 'Order ' . $order->order_reference)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Order Header -->
            <div class="card mb-4">
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
                            @if($order->pickup_code && $order->order_status === 'ready')
                                <p class="mb-1"><strong>Pickup Code:</strong> <span class="badge bg-success fs-6">{{ $order->pickup_code }}</span></p>
                                <p class="mb-1 text-muted small"><i class="bi bi-info-circle"></i> Your order is ready for pickup. Bring this code to the AANI Weekend Market.</p>
                            @elseif($order->pickup_code && $order->order_status === 'confirmed')
                                <p class="mb-1 text-muted small"><i class="bi bi-clock"></i> Pickup code will be available when your order is ready for pickup.</p>
                            @endif
                            @if($order->notes)
                                <p class="mb-1"><strong>Notes:</strong><br>{{ $order->notes }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $order->first_name }} {{ $order->last_name }}</p>
                            @if($order->phone)
                                <p class="mb-1"><strong>Phone:</strong> {{ $order->phone }}</p>
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
                        <table class="table">
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
                                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->quantity }} {{ $item->unit_type }}</td>
                                        <td>₱{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="4">Subtotal</th>
                                    <th>₱{{ number_format($subtotal, 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4">Market Fee</th>
                                    <th>₱{{ number_format($marketFee, 2) }}</th>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="4">Total</th>
                                    <th>₱{{ number_format($totalAmount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-4">
                <a href="{{ route('customer.orders.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Orders
                </a>
                @if($order->order_status === 'pending')
                    <button class="btn btn-danger float-end" onclick="confirmCancelOrder()">
                        <i class="bi bi-x-circle"></i> Cancel Order
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmCancelOrder() {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        // Add cancel order functionality here
        alert('Order cancellation functionality would be implemented here.');
    }
}
</script>
@endsection
