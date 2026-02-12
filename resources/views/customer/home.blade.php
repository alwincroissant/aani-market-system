@extends('layouts.base')

@section('title', 'AANI Market - Home')

@section('content')
<div class="container-fluid">
    <!-- Hero Section -->
    <div class="hero-banner p-4 p-md-5 rounded-4 position-relative overflow-hidden bg-primary text-white">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="display-5 fw-bold text-dark mb-3">Welcome to AANI Market</h1>
                <p class="lead mb-4">Your neighborhood wet market, <span class="text-warning">online</span>.</p>
                <p class="mb-4">Browse fresh produce, seafood, plants, and more from local vendors.</p>
                <div class="d-flex gap-3 mb-4">
                    <a href="{{ route('auth.register') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-person-plus me-2"></i> Create Account
                    </a>
                    <a href="{{ route('auth.login') }}" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                    </a>
                </div>
            </div>
            <div class="col-md-5">
                <div class="text-center">
                    <i class="bi bi-shop display-1 text-white mb-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-shop display-1 text-primary mb-3"></i>
                    <h5 class="card-title">{{ App\Models\Vendor::count() }}</h5>
                    <p class="text-muted">Active Vendors</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-box display-1 text-success mb-3"></i>
                    <h5 class="card-title">{{ App\Models\Product::count() }}</h5>
                    <p class="text-muted">Products Available</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-cart3 display-1 text-info mb-3"></i>
                    <h5 class="card-title">{{ \Illuminate\Support\Facades\DB::table('orders')->count() }}</h5>
                    <p class="text-muted">Orders Placed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 class="mb-3">Ready to Start Selling?</h3>
                    <p class="lead mb-4">Join our community of local vendors and reach customers directly.</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('auth.register') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-shop me-2"></i> Register as Customer
                        </a>
                        <a href="{{ route('vendor.register') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-bag-plus me-2"></i> Apply as Vendor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
