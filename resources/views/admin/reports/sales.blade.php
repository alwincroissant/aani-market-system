@extends('layouts.base')

@section('title', 'Sales Report')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --bg:        #F5F4F0;
        --surface:   #FFFFFF;
        --border:    #E4E2DC;
        --text:      #1A1916;
        --muted:     #7A7871;
        --accent:    #1D6F42;
        --accent-lt: #EAF4EE;
        --danger:    #C0392B;
        --warn:      #D97706;
        --warn-lt:   #FEF3C7;
        --radius:    10px;
        --shadow:    0 1px 3px rgba(0,0,0,.07), 0 4px 12px rgba(0,0,0,.04);
    }

    .admin-page { background: var(--bg); padding: 20px 0; }

    .page-header { margin-bottom: 24px; }
    .page-header h2 { font-size: 24px; font-weight: 600; color: var(--text); margin: 0; }

    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-body { padding: 20px; }

    table { width: 100%; border-collapse: collapse; font-family: 'DM Sans', sans-serif; }
    thead tr { background: var(--bg); border-bottom: 1px solid var(--border); }
    th {
        text-align: left;
        padding: 10px 16px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--muted);
    }
    td {
        padding: 11px 16px;
        font-size: 13.5px;
        border-bottom: 1px solid var(--border);
        color: var(--text);
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #faf9f7; }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 11.5px;
        font-weight: 500;
    }

    .badge-pending   { background: var(--warn-lt);  color: var(--warn); }
    .badge-confirmed,
    .badge-ready,
    .badge-preparing,
    .badge-awaiting_rider { background: #e8eef7; color: #1f4f82; }
    .badge-out_for_delivery { background: #e8eef7; color: #1f4f82; }
    .badge-completed { background: var(--accent-lt); color: var(--accent); }
    .badge-delivered { background: #e1f3ed; color: #176b4b; }
    .badge-cancelled { background: #fde8e7; color: var(--danger); }

    .btn-outline-primary {
        padding: 6px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        color: var(--text);
        background: transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
    }
    .btn-outline-primary:hover { background: var(--bg); border-color: #999; }

    .form-control, .form-select {
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 13px;
        color: var(--text);
        background-color: white;
    }
    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-lt);
    }

    .filter-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
    }
    .stat-label {
        font-size: 11.5px;
        font-weight: 500;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 10px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 600;
        font-family: 'DM Mono', monospace;
        line-height: 1;
        color: var(--text);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .mono { font-family: 'DM Mono', monospace; font-size: 13px; }
    .empty-state {
        text-align: center;
        padding: 36px 20px;
        color: var(--muted);
        font-size: 13.5px;
    }

    @media (max-width: 768px) {
        .filter-section { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="admin-page">
<div class="container" style="padding: 20px;">
    <div style="margin-bottom: 24px;">
        <h2 style="margin: 0 0 4px 0; font-size: 24px; font-weight: 600; color: var(--text);">Sales Report</h2>
        <p style="margin: 0; font-size: 13px; color: var(--muted);">Track sales performance and revenue trends</p>
    </div>

    <!-- Filter Form -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales') }}">
                <div class="filter-section">
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" required>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" required>
                    <button type="submit" class="btn-outline-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Gross Sales</div>
            <div class="stat-value mono">₱{{ number_format($totalGrossSales, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Market Fees (5%)</div>
            <div class="stat-value mono">₱{{ number_format($totalMarketFees, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Net Revenue</div>
            <div class="stat-value mono">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value mono">{{ $allSales->count() }}</div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            @if($allSales->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Order #</th>
                            <th>Type</th>
                            <th>Vendor</th>
                            <th>Gross Sale</th>
                            <th>Market Fee</th>
                            <th>Net Payout</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allSales as $order)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y H:i') }}</td>
                                <td class="mono">{{ $order->order_number }}</td>
                                <td>
                                    @if($order->sale_type == 'physical')
                                        <span class="badge" style="background: #e1f3ed; color: #176b4b;">Physical</span>
                                    @else
                                        <span class="badge" style="background: #e8eef7; color: #1f4f82;">Online</span>
                                    @endif
                                </td>
                                <td>{{ $order->business_name }}</td>
                                <td class="mono">₱{{ number_format($order->subtotal, 2) }}</td>
                                <td class="mono">₱{{ number_format($order->market_fee, 2) }}</td>
                                <td class="mono">₱{{ number_format($order->subtotal - $order->market_fee, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($order->status) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <i class="bi bi-graph-down" style="font-size: 3rem; color: var(--muted);"></i>
                    <h5 style="margin-top: 12px; color: var(--text);">No orders found</h5>
                    <p>No orders were placed within the selected date range.</p>
                </div>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
