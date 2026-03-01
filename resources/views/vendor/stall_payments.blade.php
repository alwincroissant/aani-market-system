@extends('layouts.base')

@section('title', 'Stall Payments')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-cash-coin me-2"></i>Stall Payments</h2>

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

    {{-- Stall Info --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-shop me-2"></i>Your Stall</h5>
                    @if($stallAssignment)
                        <p class="mb-1"><strong>Stall Number:</strong> {{ $stallAssignment->stall_number }}</p>
                        <p class="mb-0"><strong>Section:</strong> {{ $stallAssignment->section_name ?? 'N/A' }}</p>
                    @else
                        <p class="text-muted mb-0">No stall assigned yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 {{ $totalOwed > 0 ? 'border-warning' : 'border-success' }}">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-wallet2 me-2"></i>Total Balance Due</h5>
                    <h2 class="{{ $totalOwed > 0 ? 'text-danger' : 'text-success' }}">
                        ₱{{ number_format($totalOwed, 2) }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Unpaid Bills --}}
    @if($unpaidPayments->count() > 0)
        <h4 class="mb-3"><i class="bi bi-exclamation-circle text-warning me-2"></i>Outstanding Bills</h4>
        <div class="row mb-4">
            @foreach($unpaidPayments as $payment)
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm {{ $payment->status === 'overdue' ? 'border-danger' : 'border-warning' }}">
                        <div class="card-header d-flex justify-content-between align-items-center {{ $payment->status === 'overdue' ? 'bg-danger text-white' : 'bg-warning text-dark' }}">
                            <strong>{{ $payment->billing_period ?? 'Stall Rent' }}</strong>
                            <span class="badge {{ $payment->status === 'overdue' ? 'bg-light text-danger' : 'bg-light text-warning' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="text-muted small">Amount Due</div>
                                    <h4 class="mb-0">₱{{ number_format($payment->amount_due, 2) }}</h4>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Due Date</div>
                                    <h5 class="mb-0 {{ $payment->status === 'overdue' ? 'text-danger' : '' }}">
                                        {{ \Carbon\Carbon::parse($payment->due_date)->format('M d, Y') }}
                                    </h5>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">Pay This Bill</h6>
                            <form method="POST" action="{{ route('vendor.stall-payments.pay', $payment->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="payment_method_{{ $payment->id }}" class="form-label">Payment Method *</label>
                                    <select class="form-select" name="payment_method" id="payment_method_{{ $payment->id }}" required>
                                        <option value="">-- Select --</option>
                                        <option value="cash">Cash</option>
                                        <option value="gcash">GCash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="payment_reference_{{ $payment->id }}" class="form-label">Reference Number (optional)</label>
                                    <input type="text" class="form-control" name="payment_reference" 
                                           id="payment_reference_{{ $payment->id }}" 
                                           placeholder="e.g. GCash ref #, receipt #">
                                </div>
                                <button type="submit" class="btn btn-success w-100" 
                                        onclick="return confirm('Confirm payment of ₱{{ number_format($payment->amount_due, 2) }}?')">
                                    <i class="bi bi-check-circle me-1"></i> Pay ₱{{ number_format($payment->amount_due, 2) }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i> You have no outstanding bills. You're all caught up!
        </div>
    @endif

    {{-- Payment History --}}
    <h4 class="mb-3"><i class="bi bi-clock-history me-2"></i>Payment History</h4>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paidPayments as $payment)
                        <tr>
                            <td>{{ $payment->billing_period ?? '-' }}</td>
                            <td>₱{{ number_format($payment->amount_paid, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')) }}</td>
                            <td>{{ $payment->payment_reference ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No payment history yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
