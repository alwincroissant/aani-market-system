@extends('layouts.base')

@section('title', 'Map Management')

@section('content')
<div class="row">
    <div class="col-12">
        <h2>Market Map Editor</h2>
        <hr>
    </div>
</div>

@if(!$hasMapImage)
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5>Upload Market Floor Plan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.map.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="map_image" class="form-label">Select Floor Plan Image</label>
                            <input type="file" class="form-control @error('map_image') is-invalid @enderror" 
                                   id="map_image" name="map_image" accept="image/*" required>
                            @error('map_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Supported formats: JPEG, PNG, JPG, GIF (Max: 5MB)</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Image</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Market Map Editor</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                        <button class="btn btn-success" id="addStallBtn">
                            <i class="bi bi-plus-circle"></i> Add Stall
                        </button>
                        <button class="btn btn-info" id="editModeBtn">
                            <i class="bi bi-pencil"></i> Edit Mode
                        </button>
                        <button class="btn btn-danger" id="deleteModeBtn">
                            <i class="bi bi-trash"></i> Delete Mode
                        </button>
                        <form action="{{ route('admin.map.upload') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                            @csrf
                            <input type="file" name="map_image" accept="image/*" class="d-none" id="replaceImage" onchange="this.form.submit()">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('replaceImage').click()">
                                Replace Image
                            </button>
                        </form>
                        <span class="ms-1" id="modeIndicator"></span>
                    </div>
                    <div id="map" style="height: 600px; border: 2px solid #ddd; border-radius: 4px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stall Modal -->
    <div class="modal fade" id="stallModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stallModalTitle">Add Stall</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="stallForm">
                    <div class="modal-footer justify-content-between">
                        <span class="text-muted small">Fill in details and click Save.</span>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Stall</button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="stall_id" name="stall_id">
                        <div class="mb-3">
                            <label for="stall_number" class="form-label">Stall Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="stall_number" name="stall_number" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select" id="section_id" name="section_id" required>
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section_name }} ({{ $section->section_code }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="vendor_id" class="form-label">Vendor</label>
                            <select class="form-select" id="vendor_id" name="vendor_id">
                                <option value="">Select Vendor (Optional)</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stall Area Coordinates</label>
                            <p class="text-muted small">Click two points on the map to define the stall area (top-left and bottom-right corners)</p>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label small">First Corner (X1, Y1)</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="x1" name="x1" readonly placeholder="X1">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="y1" name="y1" readonly placeholder="Y1">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Second Corner (X2, Y2)</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="x2" name="x2" readonly placeholder="X2">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="y2" name="y2" readonly placeholder="Y2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearCoordinates">Clear Coordinates</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-container {
        background: #f5f5f5;
    }
    .stall-marker {
        cursor: pointer;
    }
    .mode-active {
        background-color: #28a745 !important;
        color: white !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($hasMapImage)
    const mapImageUrl = '{{ asset($mapImage) }}';
    console.log('Map image URL:', mapImageUrl);
    let map, imageOverlay, markers = [], currentMode = null, editingStallId = null;
    
    // Initialize map
    map = L.map('map', {
        crs: L.CRS.Simple,
        minZoom: -2,
        maxZoom: 2
    });
    
    // Load image and set bounds
    const img = new Image();
    img.onload = function() {
        const imgWidth = this.naturalWidth || this.width;
        const imgHeight = this.naturalHeight || this.height;
        
        // Use Simple CRS: coordinates match image pixels
        // Bounds: [y, x] format for Simple CRS
        const bounds = [[0, 0], [imgHeight, imgWidth]];
        
        // Set map view to center of image
        map.setView([imgHeight / 2, imgWidth / 2], 0);
        
        // Add image overlay
        imageOverlay = L.imageOverlay(mapImageUrl, bounds).addTo(map);
        
        // Set max bounds to prevent panning outside image
        map.setMaxBounds(bounds);
        
        // Enable dragging and zooming
        map.dragging.enable();
        map.touchZoom.enable();
        map.doubleClickZoom.enable();
        map.scrollWheelZoom.enable();
        map.boxZoom.enable();
        map.keyboard.enable();
        if (map.tap) map.tap.enable();
        
        // Load existing stalls
        loadStalls();
    };
    img.onerror = function() {
        console.error('Failed to load image:', mapImageUrl);
        console.error('Image exists check:', !!document.querySelector('img[src="' + mapImageUrl + '"]'));
        document.getElementById('map').innerHTML = '<div class="alert alert-danger m-3">Failed to load map image. Please check if the file exists at: ' + mapImageUrl + '<br>Check browser console for more details.</div>';
    };
    img.src = mapImageUrl;
    
    // Load existing stalls
    function loadStalls() {
        @foreach($stalls as $stall)
            @if($stall->x1 && $stall->y1 && $stall->x2 && $stall->y2)
                addStallRectangle({{ $stall->id }}, {{ $stall->x1 }}, {{ $stall->y1 }}, {{ $stall->x2 }}, {{ $stall->y2 }}, '{{ $stall->stall_number }}', '{{ $stall->status }}', {{ $stall->section_id ?? 'null' }}, '{{ $stall->business_name ?? '' }}', {{ $stall->vendor_id ?? 'null' }}, '{{ $stall->section_code ?? '' }}');
            @endif
        @endforeach
    }
    
    // Variables for two-point selection
    let stallPoints = [];
    let tempRectangle = null;
    
    // Add stall rectangle
    function addStallRectangle(id, x1, y1, x2, y2, stallNumber, status, sectionId, vendorName, vendorId, sectionCode) {
        const statusColors = {
            'available': 'rgba(0, 128, 0, 0.5)',  // Green with transparency
            'occupied': 'rgba(255, 0, 0, 0.5)',    // Red with transparency
            'maintenance': 'rgba(255, 165, 0, 0.5)' // Orange with transparency
        };
        
        // Section-specific colors
        const sectionColors = {
            'VEG': 'rgba(34, 139, 34, 0.7)',     // Forest Green for Vegetables
            'PLT': 'rgba(148, 0, 211, 0.7)',     // Dark Violet for Plants/Flowers
            'MF': 'rgba(139, 69, 19, 0.7)',      // Saddle Brown for Meat & Fish
            'FD': 'rgba(255, 140, 0, 0.7)'       // Dark Orange for Food
        };
        
        // Use section color if available, otherwise use status color
        const fillColor = sectionColors[sectionCode] || statusColors[status] || 'rgba(128, 128, 128, 0.5)';
        const borderColor = fillColor.replace('0.7', '1').replace('0.5', '1');
        
        // Ensure proper ordering
        const bounds = [[Math.min(y1, y2), Math.min(x1, x2)], [Math.max(y1, y2), Math.max(x1, x2)]];
        
        const rectangle = L.rectangle(bounds, {
            color: borderColor,
            fillColor: fillColor,
            weight: 2,
            opacity: 0.8,
            fillOpacity: 0.5
        }).addTo(map);
        
        rectangle.stallData = { id, stallNumber, status, sectionId, vendorName, vendorId, bounds, sectionCode };
        markers.push(rectangle);
        
        // Add stall number label in center
        const center = L.latLng(
            (bounds[0][0] + bounds[1][0]) / 2,
            (bounds[0][1] + bounds[1][1]) / 2
        );
        
        const label = L.divIcon({
            className: 'stall-label',
            html: `<div style="background: white; border: 3px solid ${borderColor}; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${stallNumber}</div>`,
            iconSize: [35, 35],
            iconAnchor: [17.5, 17.5]
        });
        
        const labelMarker = L.marker(center, { icon: label }).addTo(map);
        labelMarker.stallData = rectangle.stallData;
        markers.push(labelMarker);
        
        // Popup for both rectangle and label
        const popupContent = `
            <strong>Stall ${stallNumber}</strong><br>
            Section: ${sectionCode || 'N/A'}<br>
            Status: ${status}<br>
            ${vendorName ? 'Vendor: ' + vendorName : 'No vendor assigned'}
        `;
        
        rectangle.bindPopup(popupContent);
        labelMarker.bindPopup(popupContent);
        
        // Click handlers
        [rectangle, labelMarker].forEach(element => {
            element.on('click', function() {
                if (currentMode === 'edit') {
                    editStall(rectangle.stallData);
                } else if (currentMode === 'delete') {
                    if (confirm('Are you sure you want to delete this stall?')) {
                        deleteStall(rectangle.stallData.id, [rectangle, labelMarker]);
                    }
                }
            });
        });
    }
    
    // Mode buttons
    document.getElementById('addStallBtn').addEventListener('click', function() {
        resetMode();
        currentMode = 'add';
        this.classList.add('mode-active');
        document.getElementById('modeIndicator').textContent = 'Click on the map to add a stall';
        map.on('click', addStallOnClick);
    });
    
    document.getElementById('editModeBtn').addEventListener('click', function() {
        resetMode();
        currentMode = 'edit';
        this.classList.add('mode-active');
        document.getElementById('modeIndicator').textContent = 'Click on a stall to edit it';
    });
    
    document.getElementById('deleteModeBtn').addEventListener('click', function() {
        resetMode();
        currentMode = 'delete';
        this.classList.add('mode-active');
        document.getElementById('modeIndicator').textContent = 'Click on a stall to delete it';
    });
    
    function resetMode() {
        currentMode = null;
        stallPoints = [];
        
        // Remove temporary rectangle and markers
        if (tempRectangle) {
            map.removeLayer(tempRectangle);
            tempRectangle = null;
        }
        
        // Remove temporary point markers
        map.eachLayer(function(layer) {
            if (layer instanceof L.CircleMarker && !layer.stallData) {
                map.removeLayer(layer);
            }
        });
        
        document.querySelectorAll('#addStallBtn, #editModeBtn, #deleteModeBtn').forEach(btn => {
            btn.classList.remove('mode-active');
        });
        document.getElementById('modeIndicator').textContent = '';
        map.off('click', addStallOnClick);
    }
    
    // Clear coordinates button
    document.getElementById('clearCoordinates').addEventListener('click', function() {
        document.getElementById('x1').value = '';
        document.getElementById('y1').value = '';
        document.getElementById('x2').value = '';
        document.getElementById('y2').value = '';
        stallPoints = [];
        
        // Remove temporary rectangle and markers
        if (tempRectangle) {
            map.removeLayer(tempRectangle);
            tempRectangle = null;
        }
        
        map.eachLayer(function(layer) {
            if (layer instanceof L.CircleMarker && !layer.stallData) {
                map.removeLayer(layer);
            }
        });
    });
    
    function addStallOnClick(e) {
        const { lat, lng } = e.latlng;
        
        stallPoints.push({ x: lng, y: lat });
        
        // Add a temporary marker for the clicked point
        const tempMarker = L.circleMarker([lat, lng], {
            radius: 5,
            fillColor: '#ff7800',
            color: '#000',
            weight: 1,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map);
        
        // Update coordinate fields
        if (stallPoints.length === 1) {
            document.getElementById('x1').value = lng.toFixed(2);
            document.getElementById('y1').value = lat.toFixed(2);
            document.getElementById('modeIndicator').textContent = 'Click second point to complete stall area';
        } else if (stallPoints.length === 2) {
            document.getElementById('x2').value = lng.toFixed(2);
            document.getElementById('y2').value = lat.toFixed(2);
            
            // Draw temporary rectangle
            const bounds = [[stallPoints[0].y, stallPoints[0].x], [stallPoints[1].y, stallPoints[1].x]];
            tempRectangle = L.rectangle(bounds, {
                color: '#ff7800',
                fillColor: '#ff7800',
                weight: 2,
                opacity: 0.8,
                fillOpacity: 0.3,
                dashArray: '5, 5'
            }).addTo(map);
            
            // Show modal after 2 points are selected
            setTimeout(() => {
                document.getElementById('stall_id').value = '';
                document.getElementById('stallForm').reset();
                // Restore coordinates
                document.getElementById('x1').value = stallPoints[0].x.toFixed(2);
                document.getElementById('y1').value = stallPoints[0].y.toFixed(2);
                document.getElementById('x2').value = stallPoints[1].x.toFixed(2);
                document.getElementById('y2').value = stallPoints[1].y.toFixed(2);
                document.getElementById('stallModalTitle').textContent = 'Add Stall';
                const modal = new bootstrap.Modal(document.getElementById('stallModal'));
                modal.show();
                resetMode();
            }, 500);
        }
    }
    
    function editStall(stallData) {
        document.getElementById('stall_id').value = stallData.id;
        document.getElementById('stall_number').value = stallData.stallNumber;
        document.getElementById('section_id').value = stallData.sectionId;
        document.getElementById('vendor_id').value = stallData.vendorId || '';
        document.getElementById('status').value = stallData.status;
        
        // Set coordinates from bounds
        if (stallData.bounds) {
            document.getElementById('x1').value = stallData.bounds[0][1].toFixed(2);
            document.getElementById('y1').value = stallData.bounds[0][0].toFixed(2);
            document.getElementById('x2').value = stallData.bounds[1][1].toFixed(2);
            document.getElementById('y2').value = stallData.bounds[1][0].toFixed(2);
        }
        
        document.getElementById('stallModalTitle').textContent = 'Edit Stall';
        const modal = new bootstrap.Modal(document.getElementById('stallModal'));
        modal.show();
    }
    
    // Form submission
    document.getElementById('stallForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(feedback => {
            feedback.textContent = '';
        });
        
        const formData = new FormData(this);
        const stallId = formData.get('stall_id');
        const url = stallId ? `/admin/map/stalls/${stallId}` : '/admin/map/stalls';
        const method = stallId ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                stall_number: formData.get('stall_number'),
                section_id: formData.get('section_id'),
                vendor_id: formData.get('vendor_id') || null,
                status: formData.get('status'),
                x1: parseFloat(formData.get('x1')),
                y1: parseFloat(formData.get('y1')),
                x2: parseFloat(formData.get('x2')),
                y2: parseFloat(formData.get('y2'))
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('stallModal')).hide();
                
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                successAlert.style.zIndex = '9999';
                successAlert.innerHTML = `
                    <strong>Success!</strong> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(successAlert);
                
                // Auto-remove alert after 3 seconds
                setTimeout(() => {
                    if (successAlert.parentNode) {
                        successAlert.parentNode.removeChild(successAlert);
                    }
                }, 3000);
                
                // Reload page to show updated stalls
                setTimeout(() => {
                    location.reload();
                }, 1000);
                
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const input = document.getElementById(key);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = data.errors[key][0];
                            }
                        }
                    });
                    
                    // Show validation error summary
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    errorAlert.style.zIndex = '9999';
                    errorAlert.innerHTML = `
                        <strong>Validation Error!</strong> Please fix the errors below.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(errorAlert);
                    
                    // Auto-remove alert after 5 seconds
                    setTimeout(() => {
                        if (errorAlert.parentNode) {
                            errorAlert.parentNode.removeChild(errorAlert);
                        }
                    }, 5000);
                    
                } else {
                    // Show general error message
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    errorAlert.style.zIndex = '9999';
                    errorAlert.innerHTML = `
                        <strong>Error!</strong> ${data.message || 'An error occurred'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(errorAlert);
                    
                    // Auto-remove alert after 5 seconds
                    setTimeout(() => {
                        if (errorAlert.parentNode) {
                            errorAlert.parentNode.removeChild(errorAlert);
                        }
                    }, 5000);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Show error message
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            errorAlert.style.zIndex = '9999';
            errorAlert.innerHTML = `
                <strong>Error!</strong> An error occurred while saving the stall
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(errorAlert);
            
            // Auto-remove alert after 5 seconds
            setTimeout(() => {
                if (errorAlert.parentNode) {
                    errorAlert.parentNode.removeChild(errorAlert);
                }
            }, 5000);
        });
    });
    
    function deleteStall(id, elements) {
        fetch(`/admin/map/stalls/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove all elements for this stall (rectangle and label)
                elements.forEach(element => {
                    if (element && map.hasLayer(element)) {
                        map.removeLayer(element);
                    }
                });
                
                // Remove from markers array
                markers = markers.filter(m => !elements.includes(m));
                
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                successAlert.style.zIndex = '9999';
                successAlert.innerHTML = `
                    <strong>Success!</strong> Stall deleted successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(successAlert);
                
                // Auto-remove alert after 3 seconds
                setTimeout(() => {
                    if (successAlert.parentNode) {
                        successAlert.parentNode.removeChild(successAlert);
                    }
                }, 3000);
                
            } else {
                // Show error message
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                errorAlert.style.zIndex = '9999';
                errorAlert.innerHTML = `
                    <strong>Error!</strong> ${data.message || 'Failed to delete stall'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(errorAlert);
                
                // Auto-remove alert after 3 seconds
                setTimeout(() => {
                    if (errorAlert.parentNode) {
                        errorAlert.parentNode.removeChild(errorAlert);
                    }
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Show error message
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            errorAlert.style.zIndex = '9999';
            errorAlert.innerHTML = `
                <strong>Error!</strong> An error occurred while deleting the stall
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(errorAlert);
            
            // Auto-remove alert after 3 seconds
            setTimeout(() => {
                if (errorAlert.parentNode) {
                    errorAlert.parentNode.removeChild(errorAlert);
                }
            }, 3000);
        });
    }
    @else
    console.log('No map image uploaded yet');
    @endif
});
</script>
@endpush
