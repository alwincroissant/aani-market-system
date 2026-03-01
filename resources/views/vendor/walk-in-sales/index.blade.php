@extends('layouts.base')

@section('title', 'Physical Sales')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-shop-window me-2"></i>Physical / Walk-In Sales</h2>
            <p class="text-muted mb-0">Record and track your weekend market sales separately from online orders</p>
        </div>
        <a href="{{ route('vendor.walk-in-sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Record Sale
        </a>
    </div>

    {{-- Today's Comparison Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Today's Physical Sales</h6>
                            <h3 class="mb-0 text-success">₱{{ number_format($todayPhysical, 2) }}</h3>
                        </div>
                        <div class="text-success"><i class="bi bi-shop-window fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Today's Online Sales</h6>
                            <h3 class="mb-0 text-primary">₱{{ number_format($todayOnline, 2) }}</h3>
                        </div>
                        <div class="text-primary"><i class="bi bi-globe fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Today's Combined</h6>
                            <h3 class="mb-0 text-info">₱{{ number_format($todayPhysical + $todayOnline, 2) }}</h3>
                        </div>
                        <div class="text-info"><i class="bi bi-calculator fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Period Transactions</h6>
                            <h3 class="mb-0 text-warning">{{ $summary->total_transactions ?? 0 }}</h3>
                        </div>
                        <div class="text-warning"><i class="bi bi-receipt fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.walk-in-sales.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('vendor.walk-in-sales.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Period Summary --}}
    @if($summary && $summary->total_transactions > 0)
    <div class="alert alert-light border mb-4">
        <strong>Period Summary ({{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}):</strong>
        {{ $summary->total_transactions }} transactions &bull;
        {{ number_format($summary->total_items, 0) }} items sold &bull;
        <strong>₱{{ number_format($summary->total_revenue, 2) }}</strong> total revenue
    </div>
    @endif

    {{-- Sales Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                            <th>Notes</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $sale->sale_date->format('M d, Y') }}</span>
                                @if($sale->sale_date->isWeekend())
                                    <span class="badge bg-success-subtle text-success ms-1">Weekend</span>
                                @endif
                            </td>
                            <td>{{ $sale->sale_time ? \Carbon\Carbon::parse($sale->sale_time)->format('h:i A') : '—' }}</td>
                            <td>
                                {{ $sale->product_name }}
                                @if($sale->product_id)
                                    <span class="badge bg-primary-subtle text-primary ms-1" title="Linked to inventory"><i class="bi bi-link-45deg"></i></span>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($sale->quantity, $sale->quantity == intval($sale->quantity) ? 0 : 2) }}</td>
                            <td class="text-end">₱{{ number_format($sale->unit_price, 2) }}</td>
                            <td class="text-end fw-semibold">₱{{ number_format($sale->quantity * $sale->unit_price, 2) }}</td>
                            <td><small class="text-muted">{{ $sale->notes ?: '—' }}</small></td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('vendor.walk-in-sales.destroy', $sale->id) }}"
                                      onsubmit="return confirm('Delete this sale record?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3">{{ $sales->links() }}</div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2 mb-3">No physical sales recorded for this period.</p>
                <a href="{{ route('vendor.walk-in-sales.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Record Your First Sale
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
