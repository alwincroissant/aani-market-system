@extends('layouts.base')

@section('title', 'Create Customer Account')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pb-0 text-center">
                <h3 class="fw-bold">Join AANI Market</h3>
                <p class="text-muted mb-0">Create a customer account to browse stalls and place orders.</p>
            </div>
            <div class="card-body mt-3">
                <form action="{{ route('user.register') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required placeholder="At least 8 characters">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Create customer account</button>
                </form>
                <div class="mt-3 text-center small">
                    <p class="mb-1">Already have an account? <a href="{{ route('auth.login') }}">Login here</a></p>
                    <p class="mb-0 text-muted">Are you a vendor? <a href="{{ route('vendor.register') }}">Apply for a vendor account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

