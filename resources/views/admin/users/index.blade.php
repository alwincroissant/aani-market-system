@extends('layouts.base')

@section('title', 'User Management')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add New User
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search by email..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Active Users</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Users</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Activation</option>
                                <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Users</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="role">
                                <option value="">All Roles</option>
                                <option value="administrator" {{ request('role') == 'administrator' ? 'selected' : '' }}>Admin</option>
                                <option value="pickup_manager" {{ request('role') == 'pickup_manager' ? 'selected' : '' }}>Pickup Manager</option>
                                <option value="vendor" {{ request('role') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            {{ $user->email }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->role == 'administrator' ? 'danger' : ($user->role == 'pickup_manager' ? 'warning' : ($user->role == 'vendor' ? 'success' : 'primary')) }}">
                                                {{ $user->role == 'pickup_manager' ? 'Pickup Manager' : ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary" title="Edit User">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($user->role === 'vendor' && !$user->is_active)
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="openStallModal({{ $user->id }}, '{{ $user->email }}')"
                                                            title="Assign Stall & Activate">
                                                        <i class="bi bi-geo-alt"></i>
                                                    </button>
                                                @else
                                                    @if($user->is_active)
                                                        <form action="{{ route('admin.users.deactivate', $user->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="btn btn-outline-warning" title="Deactivate User" onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                                <i class="bi bi-pause-circle"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.users.activate', $user->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="btn btn-outline-success" title="Activate User" onclick="return confirm('Are you sure you want to activate this user?')">
                                                                <i class="bi bi-play-circle"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete User" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                        </div>
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">No users found</h5>
                        <p class="text-muted">
                            @if(request('search') || request('role') || request('status'))
                                Try adjusting your search criteria or 
                                <a href="{{ route('admin.users.index') }}">clear all filters</a>.
                            @else
                                Get started by adding your first user.
                            @endif
                        </p>
                        @if(!request('search') && !request('role') && !request('status'))
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add First User
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Stall Assignment Modal -->
<div class="modal fade" id="stallAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Stall & Activate Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Vendor:</strong> <span id="vendorEmail"></span><br>
                    Hover over stalls to see their status. Click "Assign Vendor" on available stalls to assign them.<br>
                    Or click empty areas to create new stalls with two-point selection.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="stallNumber" class="form-label">Stall Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="stallNumber" name="stall_number" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sectionId" class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select" id="sectionId" name="section_id" required>
                                <option value="">Select Section</option>
                                <option value="1">Organic Vegetables (VEG)</option>
                                <option value="2">Plants & Flowers (PLT)</option>
                                <option value="3">Seafood (MF)</option>
                                <option value="4">Native Products (FD)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Stall Area Coordinates</label>
                    <p class="text-muted small">Click two points on map to define vendor's stall area, OR select an existing available stall</p>
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
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearStallCoordinates">Clear Coordinates</button>
                    </div>
                </div>
                
                <!-- Map Container -->
                <div class="mb-3">
                    <label class="form-label">Market Map - Hover over stalls to assign, or click empty areas to create new stalls</label>
                    <div id="stallMap" style="height: 400px; border: 1px solid #ddd; border-radius: 4px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="userId" name="user_id">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="assignStallAndActivate()">
                    <i class="bi bi-check-circle"></i> Assign Stall & Activate
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let stallMap, stallImageOverlay, stallPoints = [], tempStallRectangle = null;
let currentUserId = null;

// Open stall modal function
function openStallModal(userId, email) {
    currentUserId = userId;
    document.getElementById('userId').value = userId;
    document.getElementById('vendorEmail').textContent = email;
    
    // Reset form
    document.getElementById('stallNumber').value = '';
    document.getElementById('sectionId').value = '';
    document.getElementById('x1').value = '';
    document.getElementById('y1').value = '';
    document.getElementById('x2').value = '';
    document.getElementById('y2').value = '';
    stallPoints = [];
    
    // Clear temporary rectangle
    if (tempStallRectangle) {
        stallMap.removeLayer(tempStallRectangle);
        tempStallRectangle = null;
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('stallAssignmentModal'));
    modal.show();
    
    // Initialize map after modal is shown
    setTimeout(() => {
        initializeStallMap();
    }, 500);
}

function initializeStallMap() {
    if (stallMap) {
        stallMap.remove();
    }
    
    const mapImageUrl = '{{ asset($mapImage ?? '') }}';
    if (!mapImageUrl) {
        document.getElementById('stallMap').innerHTML = '<div class="alert alert-warning">No market map image available. Please upload a map first.</div>';
        return;
    }
    
    // Initialize map
    stallMap = L.map('stallMap', {
        crs: L.CRS.Simple,
        minZoom: -2,
        maxZoom: 2
    });
    
    // Load image and set bounds
    const img = new Image();
    img.onload = function() {
        const imgWidth = this.naturalWidth || this.width;
        const imgHeight = this.naturalHeight || this.height;
        
        const bounds = [[0, 0], [imgHeight, imgWidth]];
        stallMap.setView([imgHeight / 2, imgWidth / 2], 0);
        
        // Add image overlay
        stallImageOverlay = L.imageOverlay(mapImageUrl, bounds).addTo(stallMap);
        stallMap.setMaxBounds(bounds);
        
        // Enable interactions
        stallMap.dragging.enable();
        stallMap.touchZoom.enable();
        stallMap.doubleClickZoom.enable();
        stallMap.scrollWheelZoom.enable();
        stallMap.boxZoom.enable();
        stallMap.keyboard.enable();
        
        // Load existing stalls
        loadExistingStalls();
        
        // Add click handler for stall selection
        stallMap.on('click', selectStallPoint);
    };
    img.onerror = function() {
        document.getElementById('stallMap').innerHTML = '<div class="alert alert-danger">Failed to load map image.</div>';
    };
    img.src = mapImageUrl;
}

function loadExistingStalls() {
    // Fetch existing stalls via AJAX
    fetch('{{ route('admin.map.stalls-data') }}')
        .then(response => response.json())
        .then(stalls => {
            console.log('Loading stalls:', stalls); // Debug log
            stalls.forEach(stall => {
                if (stall.x1 && stall.y1 && stall.x2 && stall.y2) {
                    addExistingStallRectangle(stall);
                }
            });
        })
        .catch(error => console.error('Error loading stalls:', error));
}

function addExistingStallRectangle(stall) {
    console.log('Adding stall rectangle:', stall); // Debug log
    
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
    const fillColor = sectionColors[stall.section_code] || statusColors[stall.status] || 'rgba(128, 128, 128, 0.5)';
    const borderColor = fillColor.replace('0.7', '1').replace('0.5', '1');
    
    // Ensure proper ordering
    const bounds = [[Math.min(stall.y1, stall.y2), Math.min(stall.x1, stall.x2)], [Math.max(stall.y1, stall.y2), Math.max(stall.x1, stall.x2)]];
    
    const rectangle = L.rectangle(bounds, {
        color: borderColor,
        fillColor: fillColor,
        weight: 2,
        opacity: 0.8,
        fillOpacity: 0.5
    }).addTo(stallMap);
    
    // Store stall data for hover detection
    rectangle.stallData = {
        id: stall.id,
        stallNumber: stall.stall_number,
        status: stall.status,
        sectionId: stall.section_id,
        sectionCode: stall.section_code,
        businessName: stall.business_name,
        bounds: bounds
    };
    
    // Add stall number label in center
    const center = L.latLng(
        (bounds[0][0] + bounds[1][0]) / 2,
        (bounds[0][1] + bounds[1][1]) / 2
    );
    
    const label = L.divIcon({
        className: 'stall-label',
        html: `<div style="background: white; border: 3px solid ${borderColor}; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${stall.stall_number}</div>`,
        iconSize: [35, 35],
        iconAnchor: [17.5, 17.5]
    });
    
    const labelMarker = L.marker(center, { icon: label }).addTo(stallMap);
    labelMarker.stallData = rectangle.stallData;
    
    // Add hover handlers
    rectangle.on('mouseover', function(e) {
        // Prevent popup from closing when hovering over button
        e.originalEvent.stopPropagation();
        
        // Auto-fill form for available stalls
        if (rectangle.stallData.status === 'available') {
            document.getElementById('stallNumber').value = rectangle.stallData.stallNumber;
            document.getElementById('sectionId').value = rectangle.stallData.sectionId;
            // Clear coordinates since we're using existing stall
            document.getElementById('x1').value = '';
            document.getElementById('y1').value = '';
            document.getElementById('x2').value = '';
            document.getElementById('y2').value = '';
        }
        
        showStallPopup(rectangle.stallData, center);
    });
    
    rectangle.on('mouseout', function(e) {
        // Only close popup if not hovering over popup content
        setTimeout(() => {
            const popup = document.querySelector('.leaflet-popup');
            if (popup && !popup.matches(':hover')) {
                stallMap.closePopup();
            }
        }, 100);
    });
}

// Show stall popup with assign button for available stalls
function showStallPopup(stallData, center) {
    const popupContent = `
        <div style="min-width: 250px;">
            <h6>Stall ${stallData.stallNumber}</h6>
            <p><strong>Section:</strong> ${stallData.sectionCode || 'N/A'}</p>
            <p><strong>Status:</strong> <span class="badge bg-${stallData.status === 'available' ? 'success' : 'danger'}">${stallData.status}</span></p>
            ${stallData.businessName ? '<p><strong>Vendor:</strong> ' + stallData.businessName + '</p>' : ''}
            ${stallData.status === 'available' ? `
                <div class="alert alert-info mt-2">
                    <small><i class="bi bi-info-circle"></i> Form auto-filled with stall details. Click "Assign Stall & Activate" to confirm.</small>
                </div>
                <button class="btn btn-success btn-sm w-100 mt-2" onclick="assignVendorToStall(${stallData.id}, '${stallData.stallNumber}', ${stallData.sectionId})">
                    <i class="bi bi-check-circle"></i> Assign Vendor
                </button>
            ` : '<p class="text-muted small"><em>This stall is occupied</em></p>'}
        </div>
    `;
    
    L.popup()
        .setContent(popupContent)
        .setLatLng(center)
        .openOn(stallMap);
}

// Assign vendor to specific stall
function assignVendorToStall(stallId, stallNumber, sectionId) {
    const userId = currentUserId;
    
    if (!userId) {
        alert('No vendor selected. Please try again.');
        return;
    }
    
    // Send AJAX request to assign vendor to existing stall
    fetch('{{ route('admin.users.assign-stall') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: userId,
            stall_number: stallNumber,
            section_id: sectionId,
            x1: null, // No coordinates for existing stall
            y1: null,
            x2: null,
            y2: null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('stallAssignmentModal')).hide();
            
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').prepend(alertDiv);
            
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert(data.message || 'Failed to assign stall. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function selectStallPoint(e) {
    const { lat, lng } = e.latlng;
    
    console.log('Map clicked at:', lat, lng); // Debug log
    
    // Check if clicking on existing stall
    const clickedPoint = L.latLng(lat, lng);
    let clickedStall = null;
    
    // Check if click is on an existing stall (available or occupied)
    stallMap.eachLayer(function(layer) {
        if (layer instanceof L.Rectangle && layer.stallData) {
            const bounds = layer.getBounds();
            if (bounds.contains(clickedPoint)) {
                console.log('Clicked on stall:', layer.stallData); // Debug log
                return true; // break loop
            }
        }
    });
    
    // If not clicking on existing stall, proceed with 2-point selection
    if (!clickedStall) {
        if (stallPoints.length === 0) {
            stallPoints.push([lat, lng]);
            // Add temporary marker
            L.marker([lat, lng]).addTo(stallMap);
        } else if (stallPoints.length === 1) {
            stallPoints.push([lat, lng]);
            // Create rectangle from two points
            const bounds = [stallPoints[0], stallPoints[1]];
            if (tempStallRectangle) {
                stallMap.removeLayer(tempStallRectangle);
            }
            tempStallRectangle = L.rectangle(bounds, {
                color: '#FF6B35',
                weight: 2,
                fillOpacity: 0.3
            }).addTo(stallMap);
            
            // Fill coordinate fields
            document.getElementById('x1').value = Math.min(stallPoints[0][0], stallPoints[1][0]).toFixed(2);
            document.getElementById('y1').value = Math.min(stallPoints[0][1], stallPoints[1][1]).toFixed(2);
            document.getElementById('x2').value = Math.max(stallPoints[0][0], stallPoints[1][0]).toFixed(2);
            document.getElementById('y2').value = Math.max(stallPoints[0][1], stallPoints[1][1]).toFixed(2);
        } else {
            // Reset for new selection
            if (tempStallRectangle) {
                stallMap.removeLayer(tempStallRectangle);
                tempStallRectangle = null;
            }
            stallPoints = [];
            // Clear temporary markers
            stallMap.eachLayer(function(layer) {
                if (layer instanceof L.Marker && !layer.stallData) {
                    stallMap.removeLayer(layer);
                }
            });
        }
    }
}

// Clear coordinates
document.getElementById('clearStallCoordinates').addEventListener('click', function() {
    document.getElementById('x1').value = '';
    document.getElementById('y1').value = '';
    document.getElementById('x2').value = '';
    document.getElementById('y2').value = '';
    stallPoints = [];
    
    if (tempStallRectangle) {
        stallMap.removeLayer(tempStallRectangle);
        tempStallRectangle = null;
    }
    
    // Remove temporary markers
    stallMap.eachLayer(function(layer) {
        if (layer instanceof L.Marker && !layer.stallData) {
            stallMap.removeLayer(layer);
        }
    });
});

// Assign stall and activate
function assignStallAndActivate() {
    const userId = document.getElementById('userId').value;
    const stallNumber = document.getElementById('stallNumber').value;
    const sectionId = document.getElementById('sectionId').value;
    const x1 = document.getElementById('x1').value;
    const y1 = document.getElementById('y1').value;
    const x2 = document.getElementById('x2').value;
    const y2 = document.getElementById('y2').value;
    
    // Validation
    if (!stallNumber || !sectionId || !x1 || !y1 || !x2 || !y2) {
        alert('Please fill in all fields and select stall coordinates on map.');
        return;
    }
    
    // Send AJAX request
    fetch('{{ route('admin.users.assign-stall') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: userId,
            stall_number: stallNumber,
            section_id: sectionId,
            x1: parseFloat(x1),
            y1: parseFloat(y1),
            x2: parseFloat(x2),
            y2: parseFloat(y2)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('stallAssignmentModal')).hide();
            
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').prepend(alertDiv);
            
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert(data.message || 'Failed to assign stall. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

</script>
@endpush
