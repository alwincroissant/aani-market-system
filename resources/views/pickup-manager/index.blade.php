@extends('layouts.base')

@section('title', 'Pickup Manager')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h1><i class="bi bi-qr-code-scan"></i> Pickup Manager</h1>
                <p class="text-muted">Scan pickup codes to verify and complete order pickups</p>
            </div>
        </div>
    </div>

    <!-- Pickup Code Scanner -->
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-qr-code-scan"></i> Scan Pickup Code</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="pickupCodeInput" class="form-label">Enter Pickup Code</label>
                        <div class="input-group input-group-lg">
                            <input type="text" 
                                   class="form-control" 
                                   id="pickupCodeInput" 
                                   placeholder="Enter 6-digit pickup code" 
                                   maxlength="8"
                                   autocomplete="off"
                                   autofocus>
                            <button class="btn btn-primary" onclick="verifyPickupCode()">
                                <i class="bi bi-search"></i> Verify
                            </button>
                        </div>
                        <small class="text-muted">Enter the 6-character pickup code from customer's order</small>
                    </div>
                    
                    <div id="verificationResult" class="d-none"></div>
                    
                    <div id="orderDetails" class="d-none">
                        <div class="alert alert-success">
                            <h6><i class="bi bi-check-circle"></i> Order Verified</h6>
                            <div id="orderInfo"></div>
                            <button class="btn btn-success w-100 mt-3" onclick="markPickupUsed()" id="markUsedBtn">
                                <i class="bi bi-check2-square"></i> Mark as Picked Up
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Pickup Activity</h5>
                </div>
                <div class="card-body">
                    @if($recentPickups->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Code</th>
                                        <th>Customer</th>
                                        <th>Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPickups as $pickup)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($pickup->used_at)->format('g:i A') }}</td>
                                            <td><span class="badge bg-success">{{ $pickup->code }}</span></td>
                                            <td>{{ $pickup->first_name }} {{ $pickup->last_name }}</td>
                                            <td><small>{{ $pickup->order_reference }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No pickup activity today</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-focus on pickup code input
document.getElementById('pickupCodeInput').focus();

// Handle Enter key in pickup code input
document.getElementById('pickupCodeInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        verifyPickupCode();
    }
});

function verifyPickupCode() {
    const code = document.getElementById('pickupCodeInput').value.trim().toUpperCase();
    const resultDiv = document.getElementById('verificationResult');
    const orderDetailsDiv = document.getElementById('orderDetails');
    const orderInfoDiv = document.getElementById('orderInfo');
    
    if (!code) {
        showResult('Please enter a pickup code.', 'danger');
        return;
    }
    
    // Show loading
    showResult('Verifying pickup code...', 'info');
    
    fetch('/pickup-manager/verify-pickup-code', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ pickup_code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const order = data.order;
            showResult('Pickup code verified successfully!', 'success');
            
            orderInfoDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Order:</strong> ${order.order_reference}<br>
                        <strong>Customer:</strong> ${order.first_name} ${order.last_name}<br>
                        <strong>Type:</strong> ${order.fulfillment_type.replace('_', ' ')}
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong> <span class="badge bg-primary">${order.order_status}</span><br>
                        <strong>Code:</strong> <span class="badge bg-success">${code}</span>
                    </div>
                </div>
            `;
            
            orderDetailsDiv.classList.remove('d-none');
            document.getElementById('markUsedBtn').focus();
        } else {
            showResult(data.message, 'danger');
            orderDetailsDiv.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResult('Error verifying pickup code.', 'danger');
    });
}

function markPickupUsed() {
    const code = document.getElementById('pickupCodeInput').value.trim().toUpperCase();
    
    fetch('/pickup-manager/mark-pickup-used', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ pickup_code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResult(data.message, 'success');
            document.getElementById('orderDetails').classList.add('d-none');
            document.getElementById('pickupCodeInput').value = '';
            document.getElementById('pickupCodeInput').focus();
            
            // Reload page after 2 seconds to show updated activity
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showResult(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResult('Error marking pickup as used.', 'danger');
    });
}

function showResult(message, type) {
    const resultDiv = document.getElementById('verificationResult');
    resultDiv.className = `alert alert-${type}`;
    resultDiv.textContent = message;
    resultDiv.classList.remove('d-none');
    
    // Auto-hide success messages after 3 seconds
    if (type === 'success') {
        setTimeout(() => {
            resultDiv.classList.add('d-none');
        }, 3000);
    }
}
</script>
@endsection
