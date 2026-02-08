@extends('layouts.base')

@section('title', 'Vendor Profile')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Vendor Profile</h2>
            
            <!-- Banner Section -->
            <div class="card mb-4">
                <div class="position-relative" style="height: 200px; overflow: hidden;">
                    @if($vendor->banner_url)
                        <img src="{{ asset($vendor->banner_url) }}" alt="Banner" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <div class="bg-primary d-flex align-items-center justify-content-center h-100">
                            <i class="bi bi-shop text-white" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
                <div class="card-body text-center" style="margin-top: -50px;">
                    <div class="d-inline-block">
                        @if($vendor->logo_url)
                            <img src="{{ asset($vendor->logo_url) }}" alt="Logo" 
                                 class="rounded-circle border border-white border-3" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center border border-white border-3" 
                                 style="width: 100px; height: 100px;">
                                <i class="bi bi-shop text-white" style="font-size: 2.5rem;"></i>
                            </div>
                        @endif
                    </div>
                    <h4 class="mt-3">{{ $vendor->business_name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Business Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="business_name" class="form-label">Business Name</label>
                                    <input type="text" class="form-control" id="business_name" name="business_name" 
                                           value="{{ $vendor->business_name ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ auth()->user()->email }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="{{ $vendor->phone ?? '' }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="business_description" class="form-label">Business Description</label>
                                    <textarea class="form-control" id="business_description" name="business_description" rows="3" 
                                              placeholder="Brief description of your business...">{{ $vendor->description ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="vendor_bio" class="form-label">Vendor Bio</label>
                                    <textarea class="form-control" id="vendor_bio" name="vendor_bio" rows="4" 
                                              placeholder="Tell customers about your story, products, and what makes your business special...">{{ $vendor->vendor_bio ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="logo_url" class="form-label">Business Logo</label>
                                    <input type="file" class="form-control" id="logo_url" name="logo_url" 
                                           accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF (Max 2MB)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="banner_url" class="form-label">Business Banner</label>
                                    <input type="file" class="form-control" id="banner_url" name="banner_url" 
                                           accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF (Max 4MB)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
