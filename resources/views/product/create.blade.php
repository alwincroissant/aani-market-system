@extends('layouts.base')

@section('title', 'Create Product')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Create New Product</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                               id="product_name" name="product_name" value="{{ old('product_name') }}" required>
                        @error('product_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price_per_unit" class="form-label">Price per Unit <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" 
                                   class="form-control @error('price_per_unit') is-invalid @enderror" 
                                   id="price_per_unit" name="price_per_unit" value="{{ old('price_per_unit') }}" required>
                            @error('price_per_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="unit_type" class="form-label">Unit Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit_type') is-invalid @enderror" 
                                    id="unit_type" name="unit_type" required>
                                <option value="">Select Unit</option>
                                <option value="kg" {{ old('unit_type') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                <option value="g" {{ old('unit_type') == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                <option value="piece" {{ old('unit_type') == 'piece' ? 'selected' : '' }}>Piece</option>
                                <option value="bundle" {{ old('unit_type') == 'bundle' ? 'selected' : '' }}>Bundle</option>
                                <option value="pack" {{ old('unit_type') == 'pack' ? 'selected' : '' }}>Pack</option>
                                <option value="dozen" {{ old('unit_type') == 'dozen' ? 'selected' : '' }}>Dozen</option>
                                <option value="liter" {{ old('unit_type') == 'liter' ? 'selected' : '' }}>Liter (L)</option>
                            </select>
                            @error('unit_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="product_image" class="form-label">Product Image</label>
                        <input type="file" class="form-control @error('product_image') is-invalid @enderror" 
                               id="product_image" name="product_image" accept="image/*">
                        @error('product_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Max size: 2MB</small>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_available" name="is_available" 
                               {{ old('is_available', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_available">
                            Available for sale
                        </label>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3">Stock Management</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Initial Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" min="0" 
                                   class="form-control @error('stock_quantity') is-invalid @enderror" 
                                   id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="minimum_stock" class="form-label">Minimum Stock Level <span class="text-danger">*</span></label>
                            <input type="number" min="0" 
                                   class="form-control @error('minimum_stock') is-invalid @enderror" 
                                   id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', 5) }}" required>
                            @error('minimum_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Alert when stock falls below this level</small>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="track_stock" name="track_stock" 
                               {{ old('track_stock', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="track_stock">
                            Track stock quantity
                        </label>
                        <small class="form-text text-muted d-block">Automatically decrease stock when sold</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

