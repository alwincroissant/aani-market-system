@extends('layouts.base')

@section('title', 'Product Details')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Product Details</h4>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($product->product_image_url)
                        <div class="col-md-4 mb-3">
                            <img src="{{ asset($product->product_image_url) }}" alt="{{ $product->product_name }}" class="img-fluid rounded">
                        </div>
                    @endif
                    <div class="{{ $product->product_image_url ? 'col-md-8' : 'col-md-12' }}">
                        <h5>{{ $product->product_name }}</h5>
                        <p class="text-muted">Category: <span class="badge" style="background-color: {{ $product->color_code ?? '#6c757d' }}">{{ $product->category_name }}</span></p>
                        <p><strong>Price:</strong> ₱{{ number_format($product->price_per_unit, 2) }} per {{ $product->unit_type }}</p>
                        @if($product->description)
                            <p><strong>Description:</strong></p>
                            <p>{{ $product->description }}</p>
                        @endif
                        <p>
                            <strong>Status:</strong> 
                            @if($product->is_available)
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-danger">Not Available</span>
                            @endif
                        </p>
                        <p><strong>Vendor:</strong> {{ $product->business_name }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

