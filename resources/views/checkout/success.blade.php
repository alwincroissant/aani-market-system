@extends('layouts.base')

@section('title', 'Order Success')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h2 class="mb-3">Order Placed Successfully!</h2>
                <p class="text-muted mb-4">
                    Thank you for your order. We've received your order and will process it shortly.
                </p>
                
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">What's Next?</h6>
                                <p class="small text-muted mb-2">
                                    • You'll receive an order confirmation via email<br>
                                    • Vendors will contact you for delivery/pickup arrangements<br>
                                    • You can track your order status in your account
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Pickup Information</h6>
                                <p class="small text-muted mb-2">
                                    • For pickup orders: Pickup code will be available when order is ready<br>
                                    • Check your order details on weekends for pickup code<br>
                                    • Pickup location: AANI Weekend Market, Taguig<br>
                                    • Operating hours: Saturday & Sunday 5:00 AM - 2:00 PM
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-primary">
                        View My Orders
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
