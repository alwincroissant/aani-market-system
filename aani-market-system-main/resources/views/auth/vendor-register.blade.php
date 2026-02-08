@extends('layouts.base')

@section('title', 'Apply as Vendor')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pb-0 text-center">
                <h3 class="fw-bold">Become an AANI Vendor</h3>
                <p class="text-muted mb-0">Submit your application to sell at the weekend market.</p>
            </div>
            <div class="card-body mt-3">
                <form action="{{ route('vendor.register.submit') }}" method="POST">
                    @csrf
                    <h5 class="mb-3">Account details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label">Contact phone</label>
                            <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                   id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}">
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Business details</h5>
                    <div class="mb-3">
                        <label for="business_name" class="form-label">Business / Stall name</label>
                        <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                               id="business_name" name="business_name" value="{{ old('business_name') }}" required>
                        @error('business_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="owner_name" class="form-label">Owner name</label>
                        <input type="text" class="form-control @error('owner_name') is-invalid @enderror" 
                               id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required>
                        @error('owner_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="business_description" class="form-label">What do you sell?</label>
                        <textarea class="form-control @error('business_description') is-invalid @enderror" 
                                  id="business_description" name="business_description" rows="3" placeholder="Short description of your products and sourcing.">{{ old('business_description') }}</textarea>
                        @error('business_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Services you can offer</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="weekend_pickup_enabled" name="weekend_pickup_enabled" {{ old('weekend_pickup_enabled', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="weekend_pickup_enabled">
                                🏪 Weekend pickup at the market
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="weekday_delivery_enabled" name="weekday_delivery_enabled" {{ old('weekday_delivery_enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="weekday_delivery_enabled">
                                🚚 Weekday delivery
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="weekend_delivery_enabled" name="weekend_delivery_enabled" {{ old('weekend_delivery_enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="weekend_delivery_enabled">
                                🚚 Weekend delivery
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">Admins may adjust these settings after reviewing your application.</small>
                    </div>

                    <div class="alert alert-info small">
                        Your application will be reviewed by the market administrators. Once approved, you will receive an email and be able to log in as a vendor.
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2">Submit vendor application</button>
                </form>
                <div class="mt-3 text-center small">
                    <p class="mb-0 text-muted">Already have an account? <a href="{{ route('auth.login') }}">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

