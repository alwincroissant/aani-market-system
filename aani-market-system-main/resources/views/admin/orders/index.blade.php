@extends('layouts.base')

@section('title', 'Orders Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Orders Management</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('admin.orders.index') }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="">All Statuses</option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fulfillment_type" class="form-label">Fulfillment Type</label>
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
                                    </div>
                                    <div class="col-md-3">
                                        <label for="has_pickup_code" class="form-label">Pickup Code</label>
                                        <select name="has_pickup_code" id="has_pickup_code" class="form-select">
                                            <option value="">All Orders</option>
                                            <option value="yes" {{ request('has_pickup_code') == 'yes' ? 'selected' : '' }}>Has Pickup Code</option>
                                            <option value="no" {{ request('has_pickup_code') == 'no' ? 'selected' : '' }}>No Pickup Code</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="search" class="form-label">Search</label>
                                        <input type="text" name="search" id="search" class="form-control" 
                                               placeholder="Order #, Name, Email" value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="date_from" class="form-label">Date From</label>
                                        <input type="date" name="date_from" id="date_from" class="form-control" 
                                               value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="date_to" class="form-label">Date To</label>
                                        <input type="date" name="date_to" id="date_to" class="form-control" 
                                               value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-funnel"></i> Filter
                                        </button>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary w-100">
                                            <i class="bi bi-x-circle"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    @if(request()->hasAny(['status', 'fulfillment_type', 'search', 'date_from', 'date_to', 'has_pickup_code']))
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>Active Filters:</strong>
                                    @if(request('status'))
                                        <span class="badge bg-primary me-1">Status: {{ ucfirst(request('status')) }}</span>
                                    @endif
                                    @if(request('fulfillment_type'))
                                        <span class="badge bg-primary me-1">Type: {{ str_replace('_', ' ', request('fulfillment_type')) }}</span>
                                    @endif
                                    @if(request('has_pickup_code'))
                                        <span class="badge bg-primary me-1">Pickup Code: {{ request('has_pickup_code') == 'yes' ? 'Has Code' : 'No Code' }}</span>
                                    @endif
                                    @if(request('search'))
                                        <span class="badge bg-primary me-1">Search: {{ request('search') }}</span>
                                    @endif
                                    @if(request('date_from'))
                                        <span class="badge bg-primary me-1">From: {{ request('date_from') }}</span>
                                    @endif
                                    @if(request('date_to'))
                                        <span class="badge bg-primary me-1">To: {{ request('date_to') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
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
                                        <td><strong>{{ $order->order_reference }}</strong></td>
                                        <td>{{ $order->first_name }} {{ $order->last_name }}</td>
                                        <td>{{ $order->email }}</td>
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
                                                    <span>🏪 Pickup</span>
                                                    @break
                                                @case('weekday_delivery')
                                                    <span>🚚 Weekday Delivery</span>
                                                    @break
                                                @case('weekend_delivery')
                                                    <span>🚚 Weekend Delivery</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>{{ $order->item_count }}</td>
                                        <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            @if($order->pickup_code)
                                                <span class="badge bg-success">{{ $order->pickup_code }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        @if($orders->isEmpty())
                            <div class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No orders found matching your criteria.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
