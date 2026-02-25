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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required placeholder="At least 8 characters">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone') }}" required placeholder="09XXXXXXXXX">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address_line" class="form-label">Address line <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('address_line') is-invalid @enderror"
                               id="address_line" name="address_line" value="{{ old('address_line') }}" required placeholder="House number, street, barangay">
                        @error('address_line')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       id="city" name="city" value="{{ old('city') }}" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('province') is-invalid @enderror"
                                       id="province" name="province" value="{{ old('province') }}" required>
                                @error('province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="postal_code" class="form-label">Postal code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                               id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required placeholder="4-digit code">
                        @error('postal_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

