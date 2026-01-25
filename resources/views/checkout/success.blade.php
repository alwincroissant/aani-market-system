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
                                <h6 class="card-title">Need Help?</h6>
                                <p class="small text-muted mb-2">
                                    • Contact us at support@aanimarket.com<br>
                                    • Call us at +63 912 345 6789<br>
                                    • Live chat available 9AM-6PM
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        Continue Shopping
                    </a>
                    <a href="{{ route('cart.view') }}" class="btn btn-outline-secondary">
                        View Cart
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
