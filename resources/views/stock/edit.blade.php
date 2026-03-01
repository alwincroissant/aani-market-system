@extends('layouts.base')

@section('title', 'Adjust Stock')

@section('content')
<div class="container mt-4">
    <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary mb-3">Back to Stocks</a>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Adjust Stock for: {{ $product->product_name }}</h5>
            <p class="text-muted">Current stock: <strong>{{ $product->stock_quantity }}</strong></p>

            <form action="{{ route('stock.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select">
                        <option value="add">Add</option>
                        <option value="subtract">Subtract</option>
                        <option value="set">Set to</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <input type="text" name="notes" class="form-control">
                </div>

                <button class="btn btn-primary">Update Stock</button>
            </form>
        </div>
    </div>
</div>
@endsection
