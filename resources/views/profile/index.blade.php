@extends('layouts.base')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">My Profile</h2>
            
            <!-- Profile Picture Section -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($user->profile_picture)
                            <img src="{{ asset($user->profile_picture) }}" alt="Profile Picture" 
                                 class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px; margin: 0 auto;">
                                <i class="bi bi-person text-white" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                    </div>
                    <h5>{{ $customer->first_name ?? 'First Name' }} {{ $customer->last_name ?? 'Last Name' }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="{{ $customer->first_name ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="{{ $customer->last_name ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ auth()->user()->email }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="{{ $customer->phone ?? '' }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3" 
                                              placeholder="Tell us about yourself...">{{ $user->bio ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture" 
                                           accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF (Max 2MB)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('profile.addresses') }}" class="btn btn-outline-primary">
                                <i class="bi bi-geo-alt"></i> Manage Addresses
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.change-password') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" 
                                           name="current_password" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" required minlength="8">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="new_password_confirmation" 
                                           name="new_password_confirmation" required>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-lock"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
