@extends('layouts.base')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Recent Stock Changes</h2>
                <p class="text-muted mb-0">Track all stock adjustments, restocks, and sales</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Previous Stock</th>
                        <th>New Stock</th>
                        <th>Change</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockLogs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <strong>{{ $log->product->product_name ?? 'N/A' }}</strong><br>
                                @if($log->product)
                                    <small class="text-muted">{{ $log->product->unit_type }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($log->change_type) {
                                        'adjustment' => 'bg-warning',
                                        'restock' => 'bg-success',
                                        'sale' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($log->change_type) }}
                                </span>
                            </td>
                            <td>{{ $log->previous_stock }}</td>
                            <td>{{ $log->new_stock }}</td>
                            <td>
                                @if($log->quantity_changed > 0)
                                    <span class="text-success">
                                        <i class="bi bi-arrow-up"></i> +{{ $log->quantity_changed }}
                                    </span>
                                @elseif($log->quantity_changed < 0)
                                    <span class="text-danger">
                                        <i class="bi bi-arrow-down"></i> {{ $log->quantity_changed }}
                                    </span>
                                @else
                                    <span class="text-muted">No change</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <h5 class="mt-3">No stock changes found</h5>
                                <p class="text-muted">Stock changes will appear here when you adjust product quantities.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $stockLogs->links() }}
        </div>
    </div>
</div>
@endsection
