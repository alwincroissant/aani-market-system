@extends('layouts.base')

@section('title', 'Vendor Attendance Report')

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
        background: var(--accent-lt);
        color: var(--accent);
    }
    .badge-absent { background: #fde8e7; color: var(--danger); }
    .badge-late { background: var(--warn-lt); color: var(--warn); }

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
    }

    .empty-state {
        text-align: center;
        padding: 36px 20px;
        color: var(--muted);
        font-size: 13.5px;
    }

    @media (max-width: 768px) {
        .filter-section { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="admin-page">
<div class="container" style="padding: 20px;">
    <div style="margin-bottom: 24px;">
        <h2 style="margin: 0 0 4px 0; font-size: 24px; font-weight: 600; color: var(--text);">Vendor Attendance Report</h2>
        <p style="margin: 0; font-size: 13px; color: var(--muted);">Monitor vendor market attendance and punctuality</p>
    </div>

    <!-- Filter Form -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.attendance') }}">
                <div class="filter-section">
                    <input type="date" class="form-control" name="market_date" value="{{ $marketDate }}" required>
                    <button type="submit" class="btn-outline-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; margin-bottom: 24px;">
        <div class="card">
            <div class="card-body">
                <div style="font-size: 11.5px; font-weight: 500; letter-spacing: .04em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px;">Present</div>
                <div style="font-size: 28px; font-weight: 600; font-family: 'DM Mono', monospace; color: var(--text);">{{ $presentVendors->count() }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div style="font-size: 11.5px; font-weight: 500; letter-spacing: .04em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px;">Absent</div>
                <div style="font-size: 28px; font-weight: 600; font-family: 'DM Mono', monospace; color: var(--text);">{{ $absentVendors->count() }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div style="font-size: 11.5px; font-weight: 500; letter-spacing: .04em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px;">Total</div>
                <div style="font-size: 28px; font-weight: 600; font-family: 'DM Mono', monospace; color: var(--text);">{{ $attendance->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Present Vendors Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <h5 style="padding: 20px 20px 0 20px; margin-bottom: 0;">✓ Present Vendors ({{ $attendance->count() }})</h5>
            @if($attendance->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Vendor</th>
                            <th>Owner</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($presentVendors as $record)
                            <tr>
                                <td>{{ $record->business_name }}</td>
                                <td>{{ $record->owner_name }}</td>
                                <td><span class="badge">Live</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <i class="bi bi-check-circle" style="font-size: 2rem; color: var(--accent);"></i>
                    <p style="margin: 12px 0 0 0;">All vendors checked in!</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Absent Vendors Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <h5 style="padding: 20px 20px 0 20px; margin-bottom: 0;">✗ Absent Vendors ({{ $absentVendors->count() }})</h5>
            @if($absentVendors->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Vendor</th>
                            <th>Owner</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absentVendors as $vendor)
                            <tr>
                                <td>{{ $vendor->business_name }}</td>
                                <td>{{ $vendor->owner_name }}</td>
                                <td>
                                    <span class="badge badge-absent">Absent</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <i class="bi bi-check-circle" style="font-size: 2rem; color: var(--accent);"></i>
                    <p style="margin: 12px 0 0 0;">All vendors present!</p>
                </div>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
