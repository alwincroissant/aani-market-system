@extends('layouts.base')

@section('title', 'Sales Report')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sales Report</h2>
            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.sales') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <small class="text-muted">Quick filters:</small><br>
                                <a href="?start_date={{ now()->subDays(7)->toDateString() }}&end_date={{ now()->toDateString() }}" class="btn btn-sm btn-outline-success">Last 7 days</a>
                                <a href="?start_date={{ now()->subDays(30)->toDateString() }}&end_date={{ now()->toDateString() }}" class="btn btn-sm btn-outline-success">Last 30 days</a>
                                <a href="?start_date={{ now()->startOfMonth()->toDateString() }}&end_date={{ now()->toDateString() }}" class="btn btn-sm btn-outline-success">This month</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Export Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.sales.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.sales.export-csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-csv"></i> Export CSV
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1 text-success">₱{{ number_format($totalGrossSales, 2) }}</h4>
                        <p class="mb-0 text-muted">Gross Sales</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cash-stack fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1 text-success">₱{{ number_format($totalMarketFees, 2) }}</h4>
                        <p class="mb-0 text-muted">Market Fees (5%)</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-percent fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1 text-success">₱{{ number_format($totalRevenue, 2) }}</h4>
                        <p class="mb-0 text-muted">Total Revenue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    Order Details 
                    <small class="text-muted">({{ $orders->count() }} orders)</small>
                </h5>
            </div>
            <div class="card-body">
                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Order #</th>
                                    <th>Vendor</th>
                                    <th>Gross Sale</th>
                                    <th>Market Fee</th>
                                    <th>Net Payout</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y H:i') }}</td>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->business_name }}</td>
                                        <td>₱{{ number_format($order->subtotal, 2) }}</td>
                                        <td>₱{{ number_format($order->market_fee, 2) }}</td>
                                        <td>₱{{ number_format($order->subtotal - $order->market_fee, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-success fw-bold">
                                    <td colspan="3">TOTALS</td>
                                    <td>₱{{ number_format($totalGrossSales, 2) }}</td>
                                    <td>₱{{ number_format($totalMarketFees, 2) }}</td>
                                    <td>₱{{ number_format($totalGrossSales - $totalMarketFees, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-graph-down text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">No orders found</h5>
                        <p class="text-muted">No orders were placed within the selected date range.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
