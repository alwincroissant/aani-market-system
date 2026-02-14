@extends('layouts.base')

@section('title', 'My Products')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Products</h2>
            <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Unit</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->product_name }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $product->color_code ?? '#6c757d' }};">
                                    {{ $product->category_name }}
                                </span>
                            </td>
                            <td>₱{{ number_format($product->price_per_unit, 2) }}</td>
                            <td>{{ $product->unit_type }}</td>
                            <td>
                                @if($product->is_available)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No products found. <a href="{{ route('products.create') }}">Create your first product</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection

