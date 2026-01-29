@extends('layouts.base')

@section('title', 'Delivery Addresses')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Delivery Addresses</h2>
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Profile
                </a>
            </div>
            
            <!-- Add New Address -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Add New Address</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.addresses.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="address_line" class="form-label">Address Line</label>
                                    <input type="text" class="form-control @error('address_line') is-invalid @enderror" 
                                           id="address_line" name="address_line" 
                                           value="{{ old('address_line') }}" required>
                                    @error('address_line')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" 
                                           value="{{ old('city') }}" required>
                                    @error('city')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="province" class="form-label">Province</label>
                                    <input type="text" class="form-control @error('province') is-invalid @enderror" 
                                           id="province" name="province" 
                                           value="{{ old('province') }}" required>
                                    @error('province')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" 
                                           value="{{ old('postal_code') }}" placeholder="1234">
                                    @error('postal_code')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input @error('is_default') is-invalid @enderror" 
                                   type="checkbox" id="is_default" name="is_default" value="1">
                            <label class="form-check-label" for="is_default">
                                Set as default delivery address
                            </label>
                            @error('is_default')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Address
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Existing Addresses -->
            @if($addresses->count() > 0)
                <div class="row">
                    @foreach($addresses as $address)
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    @if($address->is_default)
                                        <span class="badge bg-primary mb-2">Default</span>
                                    @endif
                                    
                                    <h6 class="card-title">{{ $address->address_line }}</h6>
                                    <p class="card-text text-muted">
                                        <strong>Recipient:</strong> {{ $address->recipient_name ?? 'Not specified' }}<br>
                                        <strong>Contact:</strong> {{ $address->recipient_phone ?? 'Not specified' }}<br>
                                        {{ $address->city }}, {{ $address->province }}<br>
                                        @if($address->postal_code) {{ $address->postal_code }} @endif
                                    </p>
                                    
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            @if(!$address->is_default)
                                                <form action="{{ route('profile.addresses.update', $address->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="address_line" value="{{ $address->address_line }}">
                                                    <input type="hidden" name="city" value="{{ $address->city }}">
                                                    <input type="hidden" name="province" value="{{ $address->province }}">
                                                    <input type="hidden" name="postal_code" value="{{ $address->postal_code }}">
                                                    <input type="hidden" name="is_default" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-star"></i> Set Default
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="editAddress({{ $address->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('profile.addresses.delete', $address->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this address?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <h5>No Addresses Yet</h5>
                    <p>Add your first delivery address above to get started!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function editAddress(id) {
    // This would open a modal or redirect to edit form
    alert('Edit functionality would be implemented here with a modal or edit form.');
}
</script>
@endsection
