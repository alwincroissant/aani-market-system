@extends('layouts.base')

@section('title', 'Sales Report - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Sales Report</h2>
        <div>
            <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-cash display-1 text-success mb-3"></i>
                    <h5 class="card-title">₱{{ number_format($totalSales, 2) }}</h5>
                    <p class="text-muted">Total Sales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-cart-check display-1 text-info mb-3"></i>
                    <h5 class="card-title">{{ $totalOrders }}</h5>
                    <p class="text-muted">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-calendar3 display-1 text-primary mb-3"></i>
                    <h5 class="card-title">{{ $sales->count() }}</h5>
                    <p class="text-muted">Days with Sales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-graph-up display-1 text-warning mb-3"></i>
                    <h5 class="card-title">₱{{ number_format($totalSales / max($totalOrders, 1), 2) }}</h5>
                    <p class="text-muted">Avg per Order</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="{{ route('vendor.reports.sales.export-pdf', ['start_date' => request('start_date', now()->subDays(30)->toDateString()), 'end_date' => request('end_date', now()->toDateString())]) }}" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
                <a href="{{ route('vendor.reports.sales.export-csv', ['start_date' => request('start_date', now()->subDays(30)->toDateString()), 'end_date' => request('end_date', now()->toDateString())]) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-csv"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.reports.sales') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', now()->subDays(30)->toDateString()) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', now()->toDateString()) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Sales Trend</h5>
        </div>
        <div class="card-body">
            <canvas id="salesChart" height="100"></canvas>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daily Sales</h5>
        </div>
        <div class="card-body">
            @if($sales->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sales Amount</th>
                                <th>Orders</th>
                                <th>Avg per Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($sale->date)->format('M d, Y') }}</td>
                                    <td>₱{{ number_format($sale->total, 2) }}</td>
                                    <td>{{ $sale->order_count }}</td>
                                    <td>₱{{ number_format($sale->total / max($sale->order_count, 1), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-graph-down display-1 text-muted mb-3"></i>
                    <h4 class="text-muted">No sales data found</h4>
                    <p class="text-muted">No sales data available for the selected period.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($sales->pluck('date')->reverse()->values()),
                datasets: [{
                    label: 'Daily Sales',
                    data: @json($sales->pluck('total')->reverse()->values()),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
