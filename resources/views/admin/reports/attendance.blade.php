@extends('layouts.base')

@section('title', 'Vendor Attendance Report')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Vendor Attendance Report</h2>
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
                <form method="GET" action="{{ route('admin.reports.attendance') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="market_date" class="form-label">Market Date</label>
                            <input type="date" class="form-control" name="market_date" value="{{ $marketDate }}" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-7">
                            <div class="text-end">
                                <small class="text-muted">Quick dates:</small><br>
                                <a href="?market_date={{ now()->toDateString() }}" class="btn btn-sm btn-outline-secondary">Today</a>
                                <a href="?market_date={{ now()->subDay()->toDateString() }}" class="btn btn-sm btn-outline-secondary">Yesterday</a>
                                <a href="?market_date={{ now()->startOfWeek()->toDateString() }}" class="btn btn-sm btn-outline-secondary">This Week</a>
                                <a href="?market_date={{ now()->subWeek()->startOfWeek()->toDateString() }}" class="btn btn-sm btn-outline-secondary">Last Week</a>
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
            <a href="{{ route('admin.reports.attendance.export-pdf', ['market_date' => $marketDate]) }}" class="btn btn-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.attendance.export-csv', ['market_date' => $marketDate]) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-csv"></i> Export CSV
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $attendance->count() }}</h4>
                        <p class="mb-0">Present</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $absentVendors->count() }}</h4>
                        <p class="mb-0">Absent</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-x-circle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $allVendors->count() }}</h4>
                        <p class="mb-0">Total Vendors</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Records -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-check-circle text-success"></i> Present Vendors
                    <small class="text-muted">({{ $attendance->count() }})</small>
                </h5>
            </div>
            <div class="card-body">
                @if($attendance->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Owner</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendance as $record)
                                    <tr>
                                        <td>{{ $record->business_name }}</td>
                                        <td>{{ $record->owner_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($record->check_in_time)->format('H:i') }}</td>
                                        <td>
                                            @if($record->check_out_time)
                                                {{ \Carbon\Carbon::parse($record->check_out_time)->format('H:i') }}
                                            @else
                                                <span class="badge bg-warning">Still in</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-x-circle text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No vendors checked in on this date</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-x-circle text-danger"></i> Absent Vendors
                    <small class="text-muted">({{ $absentVendors->count() }})</small>
                </h5>
            </div>
            <div class="card-body">
                @if($absentVendors->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
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
                                            <span class="badge bg-danger">Absent</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">All vendors present!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Attendance Rate -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Attendance Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="progress" style="height: 30px;">
                            <?php $attendanceRate = $allVendors->count() > 0 ? ($attendance->count() / $allVendors->count()) * 100 : 0; ?>
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $attendanceRate }}%;">
                                {{ number_format($attendanceRate, 1) }}% Attendance Rate
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <h5>
                            {{ $attendance->count() }} / {{ $allVendors->count() }} Vendors
                        </h5>
                        <p class="text-muted mb-0">for {{ \Carbon\Carbon::parse($marketDate)->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
