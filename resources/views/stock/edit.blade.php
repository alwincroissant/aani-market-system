@extends('layouts.base')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Stock - {{ $product->product_name }}</h1>
        <a href="{{ route('stock.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Back to Stock Management
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('stock.update', $product) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Product Information</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product Name</label>
                            <p class="text-gray-900">{{ $product->product_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Vendor</label>
                            <p class="text-gray-900">{{ $product->vendor->business_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <p class="text-gray-900">{{ $product->category->category_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Type</label>
                            <p class="text-gray-900">{{ $product->unit_type }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price</label>
                            <p class="text-gray-900">${{ number_format($product->price_per_unit, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Stock Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Current Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" 
                                   value="{{ $product->stock_quantity }}" min="0" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="minimum_stock" class="block text-sm font-medium text-gray-700">Minimum Stock Level</label>
                            <input type="number" id="minimum_stock" name="minimum_stock" 
                                   value="{{ $product->minimum_stock }}" min="0" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Alert when stock reaches this level</p>
                        </div>

                        <div>
                            <label for="track_stock" class="block text-sm font-medium text-gray-700">Track Stock</label>
                            <div class="mt-1">
                                <input type="checkbox" id="track_stock" name="track_stock" value="1" 
                                       {{ $product->track_stock ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="track_stock" class="ml-2 text-sm text-gray-700">Enable stock tracking for this product</label>
                            </div>
                        </div>

                        <div>
                            <label for="allow_backorder" class="block text-sm font-medium text-gray-700">Allow Backorder</label>
                            <div class="mt-1">
                                <input type="checkbox" id="allow_backorder" name="allow_backorder" value="1" 
                                       {{ $product->allow_backorder ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="allow_backorder" class="ml-2 text-sm text-gray-700">Allow orders when out of stock</label>
                            </div>
                        </div>

                        <div>
                            <label for="stock_notes" class="block text-sm font-medium text-gray-700">Stock Notes</label>
                            <textarea id="stock_notes" name="stock_notes" rows="3" 
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Add any notes about stock management...">{{ $product->stock_notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t pt-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Current Status</h4>
                        @php
                            $statusClass = match($product->stock_status) {
                                'In stock' => 'bg-green-100 text-green-800',
                                'Low stock' => 'bg-yellow-100 text-yellow-800',
                                'Out of stock' => 'bg-red-100 text-red-800',
                                'Backorder' => 'bg-orange-100 text-orange-800',
                                'Not tracked' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                            {{ $product->stock_status }}
                        </span>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('stock.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                            Update Stock
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
