@extends('layouts.base')

@section('title', 'Sign in to AANI Market')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pb-0 text-center">
                <h3 class="fw-bold">Welcome back</h3>
                <p class="text-muted mb-0">Sign in to manage your cart and orders.</p>
            </div>
            <div class="card-body mt-3">
                <form action="{{ route('user.signin') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check d-flex justify-content-between">
                        <div>
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        {{-- <a href="#" class="small">Forgot password?</a> --}}
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Sign in</button>
                </form>
                <div class="mt-3 text-center small">
                    <p class="mb-1">New to AANI Market? <a href="{{ route('auth.register') }}">Create a customer account</a></p>
                    <p class="mb-0 text-muted">Want to sell at the market? <a href="{{ route('vendor.register') }}">Apply as a vendor</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

