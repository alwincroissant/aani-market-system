@extends('layouts.base')

@section('title', 'Product Details')

@section('content')
<style>
    .product-image-stage {
        width: 100%;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e4e2dc;
        border-radius: 8px;
        background: #f8f7f4;
        padding: 8px;
    }

    .product-main-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
    }

    .product-thumbs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        min-height: 64px;
    }

    .product-thumb {
        width: 60px;
        height: 60px;
        border: 2px solid #e4e2dc;
        border-radius: 6px;
        overflow: hidden;
        cursor: pointer;
        background: #fff;
    }

    .product-thumb.active {
        border-color: #1d6f42;
    }

    .product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Product Details</h4>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                @php
                    $allImages = collect();
                    if ($product->product_image_url) {
                        $allImages->push((object) ['image_url' => $product->product_image_url]);
                    }
                    foreach ($additionalImages as $img) {
                        $allImages->push($img);
                    }
                @endphp

                <div class="row">
                    @if($allImages->count() > 0)
                        <div class="col-md-4 mb-3">
                            <div class="product-image-stage">
                                <img id="vendorMainProductImage" src="{{ asset($allImages->first()->image_url) }}" alt="{{ $product->product_name }}" class="product-main-image">
                            </div>

                            @if($allImages->count() > 1)
                                <div class="product-thumbs">
                                    @foreach($allImages as $index => $img)
                                        <div class="product-thumb {{ $index === 0 ? 'active' : '' }}" onclick="setVendorMainImage('{{ asset($img->image_url) }}', this)">
                                            <img src="{{ asset($img->image_url) }}" alt="Image {{ $index + 1 }}">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="{{ $allImages->count() > 0 ? 'col-md-8' : 'col-md-12' }}">
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

<script>
    function setVendorMainImage(imageUrl, thumbEl) {
        const mainImage = document.getElementById('vendorMainProductImage');
        if (mainImage) {
            mainImage.src = imageUrl;
        }

        document.querySelectorAll('.product-thumb').forEach(function (el) {
            el.classList.remove('active');
        });

        if (thumbEl) {
            thumbEl.classList.add('active');
        }
    }
</script>
@endsection

