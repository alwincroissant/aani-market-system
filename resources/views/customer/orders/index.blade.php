@extends('layouts.base')

@section('title', 'My Orders')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">My Orders</h2>
            
            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order Reference</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Fulfillment Type</th>
                                <th>Total Amount</th>
                                <th>Actions</th>
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
                                            @case('completed')
                                                <span class="badge bg-success">Completed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @switch($order->fulfillment_type)
                                            @case('weekend_pickup')
                                                <span class="text-muted">🏪 Weekend Pickup</span>
                                                @break
                                            @case('weekday_delivery')
                                                <span class="text-muted">🚚 Weekday Delivery</span>
                                                @break
                                            @case('weekend_delivery')
                                                <span class="text-muted">🚚 Weekend Delivery</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        ₱{{ number_format($order->total_amount ?? 0, 2) }}
                                    </td>
                                    <td>
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
            @else
                <div class="alert alert-info text-center">
                    <h5>No Orders Yet</h5>
                    <p class="mb-3">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary">Browse Shops</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
