@extends('layouts.base')

@section('title', 'Vendor Stall Payments')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cash-coin me-2"></i>Vendor Stall Payments</h2>
        <div>
            <form method="POST" action="{{ route('admin.stall-payments.mark-overdue') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger me-2">
                    <i class="bi bi-exclamation-triangle me-1"></i> Mark Overdue
                </button>
            </form>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBillModal">
                <i class="bi bi-plus-circle me-1"></i> Create Rent Bill
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Total Unpaid</div>
                    <h3 class="text-danger mb-0">₱{{ number_format($totalUnpaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Total Collected</div>
                    <h3 class="text-success mb-0">₱{{ number_format($totalPaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Overdue Bills</div>
                    <h3 class="{{ $overdueCount > 0 ? 'text-danger' : 'text-muted' }} mb-0">{{ $overdueCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- All Bills Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Vendor</th>
                        <th>Stall</th>
                        <th>Billing Period</th>
                        <th>Amount Due</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Paid At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->vendor->business_name ?? '-' }}</td>
                            <td>{{ $payment->stall->stall_number ?? '-' }}</td>
                            <td>{{ $payment->billing_period ?? '-' }}</td>
                            <td>₱{{ number_format($payment->amount_due, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->due_date)->format('M d, Y') }}</td>
                            <td>
                                @if($payment->status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($payment->status === 'overdue')
                                    <span class="badge bg-danger">Overdue</span>
                                @else
                                    <span class="badge bg-warning text-dark">Unpaid</span>
                                @endif
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')) }}</td>
                            <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('M d, Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No stall payment records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Create Bill Modal --}}
<div class="modal fade" id="createBillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.stall-payments.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create Rent Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vendor_id" class="form-label">Vendor *</label>
                        <select class="form-select" name="vendor_id" id="vendor_id" required>
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->vendor_id }}">
                                    {{ $vendor->business_name }} (Stall {{ $vendor->stall_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="billing_period" class="form-label">Billing Period *</label>
                        <input type="text" class="form-control" name="billing_period" id="billing_period" 
                               placeholder="e.g. March 2026" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount_due" class="form-label">Amount Due (₱) *</label>
                        <input type="number" class="form-control" name="amount_due" id="amount_due" 
                               step="0.01" min="1" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date *</label>
                        <input type="date" class="form-control" name="due_date" id="due_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Create Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
