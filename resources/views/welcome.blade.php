@extends('layouts.base')

@section('title', 'AANI Market - Interactive Map')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">AANI Market Interactive Map</h1>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($mapImage)
                    <div class="position-relative" style="border: 2px solid #ddd; background: #f5f5f5; min-height: 500px;">
                        <img src="{{ asset($mapImage) }}" alt="Market Map" class="img-fluid" id="marketMap" style="max-width: 100%; height: auto;">
                        <div id="stallOverlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                            @foreach($stalls as $stall)
                                @if($stall->vendor_id && $stall->position_x && $stall->position_y)
                                    <div class="stall-marker" 
                                         style="position: absolute; 
                                                left: {{ $stall->position_x }}%; 
                                                top: {{ $stall->position_y }}%;
                                                width: 30px;
                                                height: 30px;
                                                background-color: {{ $stall->color_code ?? '#007bff' }};
                                                border: 2px solid white;
                                                border-radius: 50%;
                                                cursor: pointer;
                                                transform: translate(-50%, -50%);"
                                         data-stall-id="{{ $stall->stall_id }}"
                                         data-vendor-name="{{ $stall->business_name }}"
                                         data-vendor-id="{{ $stall->vendor_id }}"
                                         title="{{ $stall->business_name }} - {{ $stall->stall_number }}">
                                    </div>
        @endif
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <h5>Market Map Coming Soon</h5>
                        <p>The market map is currently being set up. Please check back later.</p>
                    </div>
                        @endif
            </div>
        </div>
    </div>
                </div>

<!-- Stall Info Modal -->
<div class="modal fade" id="stallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stallModalTitle">Vendor Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="stallModalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewProductsBtn" class="btn btn-primary">View Products</a>
                </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stallMarkers = document.querySelectorAll('.stall-marker');
    const stallModal = new bootstrap.Modal(document.getElementById('stallModal'));
    
    stallMarkers.forEach(marker => {
        marker.addEventListener('mouseenter', function() {
            this.style.transform = 'translate(-50%, -50%) scale(1.2)';
            this.style.zIndex = '1000';
        });
        
        marker.addEventListener('mouseleave', function() {
            this.style.transform = 'translate(-50%, -50%) scale(1)';
            this.style.zIndex = '1';
        });
        
        marker.addEventListener('click', function() {
            const vendorId = this.getAttribute('data-vendor-id');
            const vendorName = this.getAttribute('data-vendor-name');
            
            document.getElementById('stallModalTitle').textContent = vendorName;
            document.getElementById('stallModalBody').innerHTML = `
                <p><strong>Vendor:</strong> ${vendorName}</p>
                <p><strong>Stall Number:</strong> ${this.getAttribute('data-stall-id')}</p>
            `;
            document.getElementById('viewProductsBtn').href = `/vendors/${vendorId}/products`;
            stallModal.show();
        });
    });
});
</script>
@endpush
