<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - Evidence Action</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .stats-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
        }
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .card-header-custom {
            background: white;
            border-bottom: 2px solid #eef2f6;
            padding: 16px 20px;
            font-weight: 700;
            font-size: 1rem;
        }
        .table-custom th {
            background: #f8f9fc;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-approved { background-color: #28a745; color: #fff; }
        .badge-declined { background-color: #dc3545; color: #fff; }
        .badge-cancelled { background-color: #6c757d; color: #fff; }
        .badge-completed { background-color: #17a2b8; color: #fff; }
        .badge-security_approved { background-color: #20c997; color: #fff; }
        .action-btn {
            margin: 2px;
            border-radius: 30px;
            padding: 5px 12px;
            font-size: 0.75rem;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .nav-tabs-custom {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 10px 20px;
        }
        .nav-tabs-custom .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            background: transparent;
        }
        .nav-tabs-custom .nav-link:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h3 class="mt-2 fw-bold" style="color: #1a2c3e;">Supervisor Dashboard</h3>
        <div>

        </div>
    </div>
    
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-4 bg-transparent p-0">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home"><i class="fas fa-home"></i> Home</a></li>
        <li class="breadcrumb-item">Supervisor Dashboard</li>
    </ol>
    
    <!-- Session Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php
    // SAFETY: Ensure all variables are arrays
    $pendingRequests = isset($pendingRequests) && is_array($pendingRequests) ? $pendingRequests : [];
    $approvedByMeRequests = isset($approvedRequests) && is_array($approvedRequests) ? $approvedRequests : [];
    $declinedByMeRequests = isset($declinedRequests) && is_array($declinedRequests) ? $declinedRequests : [];
    $completedRequests = isset($completedRequests) && is_array($completedRequests) ? $completedRequests : [];
    $cancelledRequests = isset($cancelledRequests) && is_array($cancelledRequests) ? $cancelledRequests : [];
    
    $pendingCount = count($pendingRequests);
    $approvedCount = count($approvedByMeRequests);
    $declinedCount = count($declinedByMeRequests);
    $completedCount = count($completedRequests);
    $cancelledCount = count($cancelledRequests);
    ?>
    
    
    <!-- Stats Cards - Showing what each means clearly -->
    <div class="row mb-4 g-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-warning bg-opacity-10 border-0 border-bottom border-3 border-warning" onclick="document.getElementById('pending-section').scrollIntoView({behavior: 'smooth'})">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-1">Awaiting Approval</h6>
                            <h2 class="mb-0 fw-bold"><?= $pendingCount ?></h2>
                            <small class="text-muted">Requests from my supervisees</small>
                        </div>
                        <i class="fas fa-hourglass-half fa-3x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-success bg-opacity-10 border-0 border-bottom border-3 border-success" onclick="document.getElementById('approved-section').scrollIntoView({behavior: 'smooth'})">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-1">Approved</h6>
                            <h2 class="mb-0 fw-bold"><?= $approvedCount ?></h2>
                            <small class="text-muted">I approved these requests</small>
                        </div>
                        <i class="fas fa-check-circle fa-3x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-danger bg-opacity-10 border-0 border-bottom border-3 border-danger" onclick="document.getElementById('declined-section').scrollIntoView({behavior: 'smooth'})">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-1">Declined</h6>
                            <h2 class="mb-0 fw-bold"><?= $declinedCount ?></h2>
                            <small class="text-muted">I declined these requests</small>
                        </div>
                        <i class="fas fa-times-circle fa-3x text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-info bg-opacity-10 border-0 border-bottom border-3 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-info mb-1">Total Supervisees</h6>
                            <h2 class="mb-0 fw-bold"><?= $totalSupervisees ?? 0 ?></h2>
                            <small class="text-muted">Staff under my supervision</small>
                        </div>
                        <i class="fas fa-users fa-3x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PENDING APPROVALS SECTION (Requests waiting for MY decision) -->
    <div id="pending-section" class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center bg-warning bg-opacity-10">
            <div>
                <i class="fas fa-clock text-warning me-2"></i> 
                <strong>⏳ Awaiting Approval</strong> 
                <span class="badge bg-warning ms-2"><?= $pendingCount ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
             
                            <th>Requester</th>
                            <th>Department</th>
                            <th>Destination</th>
                            <th>Trip Date</th>
                            <th>Purpose</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pendingCount > 0): ?>
                            <?php foreach ($pendingRequests as $trip): ?>
                                <?php if (is_object($trip) || is_array($trip)): ?>
                                    <?php 
                                        $tripId = is_object($trip) ? $trip->id : $trip['id'];
                                        $requesterEmail = is_object($trip) ? ($trip->requester_email ?? $trip->staff_email ?? 'N/A') : ($trip['requester_email'] ?? $trip['staff_email'] ?? 'N/A');
                                        $departmentName = is_object($trip) ? ($trip->department_name ?? 'N/A') : ($trip['department_name'] ?? 'N/A');
                                        $destination = is_object($trip) ? ($trip->trip_destination ?? 'N/A') : ($trip['trip_destination'] ?? 'N/A');
                                        $tripDate = is_object($trip) ? ($trip->departure_date ?? $trip->trip_date ?? date('Y-m-d')) : ($trip['departure_date'] ?? $trip['trip_date'] ?? date('Y-m-d'));
                                        $purpose = is_object($trip) ? ($trip->purpose ?? '') : ($trip['purpose'] ?? '');
                                        $approvalToken = is_object($trip) ? ($trip->approval_token ?? '') : ($trip['approval_token'] ?? '');
                                    ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($requesterEmail) ?>
                                        </td>
                                        <td><?= htmlspecialchars($departmentName) ?></td>
                                        <td><?= htmlspecialchars($destination) ?></td>
                                        <td><?= date('M d, Y', strtotime($tripDate)) ?></td>
                                        <td><?= substr(htmlspecialchars($purpose), 0, 50) ?>...</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary action-btn" onclick="viewTripDetails(<?= $tripId ?>)" title="View Details">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-sm btn-success action-btn" onclick="approveRequest(<?= $tripId ?>, '<?= addslashes($approvalToken) ?>')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger action-btn" onclick="declineRequest(<?= $tripId ?>, '<?= addslashes($approvalToken) ?>')">
                                                <i class="fas fa-times"></i> Decline
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle text-success me-2"></i> No pending requests.
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- APPROVED BY ME SECTION (Requests I have approved) -->
    <div id="approved-section" class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header-custom bg-success bg-opacity-10">
            <i class="fas fa-check-circle text-success me-2"></i> 
            <strong>✅ Approved</strong> 
            <span class="badge bg-success ms-2"><?= $approvedCount ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Requester</th>
                            <th>Destination</th>
                            <th>Trip Date</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($approvedCount > 0): ?>
                            <?php foreach ($approvedByMeRequests as $trip): ?>
                                <?php if (is_object($trip) || is_array($trip)): ?>
                                    <?php 
                                        $tripId = is_object($trip) ? $trip->id : $trip['id'];
                                        $requesterEmail = is_object($trip) ? ($trip->requester_email ?? $trip->staff_email ?? 'N/A') : ($trip['requester_email'] ?? $trip['staff_email'] ?? 'N/A');
                                        $destination = is_object($trip) ? ($trip->trip_destination ?? 'N/A') : ($trip['trip_destination'] ?? 'N/A');
                                        $tripDate = is_object($trip) ? ($trip->departure_date ?? $trip->trip_date ?? date('Y-m-d')) : ($trip['departure_date'] ?? $trip['trip_date'] ?? date('Y-m-d'));
                                        $status = is_object($trip) ? ($trip->status ?? 'security_approved') : ($trip['status'] ?? 'security_approved');
                                        $statusLabel = ($status == 'security_approved') ? 'Security Clearance Pending' : ucfirst($status);
                                        $statusClass = ($status == 'security_approved') ? 'warning' : 'success';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($requesterEmail) ?></td>
                                        <td><?= htmlspecialchars($destination) ?></td>
                                        <td><?= date('M d, Y', strtotime($tripDate)) ?></td>
                                        <td><span class="badge bg-<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info action-btn" onclick="viewTripDetails(<?= $tripId ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox me-2"></i> You haven't approved any requests yet.
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- DECLINED BY ME SECTION -->
    <div id="declined-section" class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header-custom bg-danger bg-opacity-10">
            <i class="fas fa-ban text-danger me-2"></i> 
            <strong>❌ Declined</strong> 
            <span class="badge bg-danger ms-2"><?= $declinedCount ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr><th>Requester</th><th>Destination</th><th>Date</th><th>Decline Reason</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($declinedCount > 0): ?>
                            <?php foreach ($declinedByMeRequests as $trip): ?>
                                <?php if (is_object($trip) || is_array($trip)): ?>
                                    <?php 
                                        $tripId = is_object($trip) ? $trip->id : $trip['id'];
                                        $requesterEmail = is_object($trip) ? ($trip->requester_email ?? $trip->staff_email ?? 'N/A') : ($trip['requester_email'] ?? $trip['staff_email'] ?? 'N/A');
                                        $destination = is_object($trip) ? ($trip->trip_destination ?? 'N/A') : ($trip['trip_destination'] ?? 'N/A');
                                        $tripDate = is_object($trip) ? ($trip->departure_date ?? $trip->trip_date ?? date('Y-m-d')) : ($trip['departure_date'] ?? $trip['trip_date'] ?? date('Y-m-d'));
                                        $reason = is_object($trip) ? ($trip->rejection_reason ?? 'No reason provided') : ($trip['rejection_reason'] ?? 'No reason provided');
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($requesterEmail) ?></td>
                                        <td><?= htmlspecialchars($destination) ?></td>
                                        <td><?= date('M d, Y', strtotime($tripDate)) ?></td>
                                        <td class="text-danger"><?= htmlspecialchars(substr($reason, 0, 60)) ?></td>
                                        <td><button class="btn btn-sm btn-outline-secondary action-btn" onclick="viewTripDetails(<?= $tripId ?>)"><i class="fas fa-eye"></i> View</button></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No declined requests.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- COMPLETED/CLOSED REQUESTS (History) -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header-custom bg-secondary bg-opacity-10">
            <i class="fas fa-history text-secondary me-2"></i> 
            <strong>📋 Completed / Closed Requests</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead><tr><th>Requester</th><th>Destination</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $history = array_merge($completedRequests, $cancelledRequests);
                        if (!empty($history)): ?>
                            <?php foreach ($history as $trip): ?>
                                <?php if (is_object($trip) || is_array($trip)): ?>
                                    <?php 
                                        $tripId = is_object($trip) ? $trip->id : $trip['id'];
                                        $requesterEmail = is_object($trip) ? ($trip->requester_email ?? $trip->staff_email ?? 'N/A') : ($trip['requester_email'] ?? $trip['staff_email'] ?? 'N/A');
                                        $destination = is_object($trip) ? ($trip->trip_destination ?? 'N/A') : ($trip['trip_destination'] ?? 'N/A');
                                        $tripDate = is_object($trip) ? ($trip->departure_date ?? $trip->trip_date ?? date('Y-m-d')) : ($trip['departure_date'] ?? $trip['trip_date'] ?? date('Y-m-d'));
                                        $status = is_object($trip) ? ($trip->status ?? 'completed') : ($trip['status'] ?? 'completed');
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($requesterEmail) ?></td>
                                        <td><?= htmlspecialchars($destination) ?></td>
                                        <td><?= date('M d, Y', strtotime($tripDate)) ?></td>
                                        <td><span class="badge bg-secondary"><?= ucfirst($status) ?></span></td>
                                        <td><button class="btn btn-sm btn-outline-info action-btn" onclick="viewTripDetails(<?= $tripId ?>)"><i class="fas fa-eye"></i> View</button></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No completed or cancelled requests.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function approveRequest(requestId, token) {
        Swal.fire({
            title: 'Approve Trip Request?',
            text: "This request will be sent to Security Manager for clearance.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Approve'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= URL ?>intrastate/approve/' + requestId + '?token=' + encodeURIComponent(token) + '&level=supervisor';
            }
        });
    }
    
    function declineRequest(requestId, token) {
        Swal.fire({
            title: 'Decline Request',
            text: "You will be redirected to provide a reason.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Proceed'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= URL ?>intrastate/reject/' + requestId + '?token=' + encodeURIComponent(token) + '&level=supervisor';
            }
        });
    }
    
    function viewTripDetails(requestId) {
        fetch('<?= URL ?>intrastate/getRequestJson/' + requestId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const trip = data.request;
                    let detailsHtml = `
                        <div style="text-align:left; max-height: 500px; overflow-y: auto;">
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Requester:</strong> ${escapeHtml(trip.staff_email)}</div>
                                <div class="col-md-6"><strong>Phone:</strong> ${escapeHtml(trip.staff_phone)}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Supervisor:</strong> ${escapeHtml(trip.supervisor_email)}</div>
                                <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-${getStatusColor(trip.status)}">${(trip.status || 'PENDING').toUpperCase()}</span></div>
                            </div>
                            <hr>
                            <h6>Trip Information</h6>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Destination:</strong> ${escapeHtml(trip.trip_destination)}</div>
                                <div class="col-md-6"><strong>Pickup Location:</strong> ${escapeHtml(trip.pickup_location)}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Trip Date:</strong> ${new Date(trip.trip_date).toLocaleDateString()}</div>
                                <div class="col-md-6"><strong>Return Date:</strong> ${trip.return_date ? new Date(trip.return_date).toLocaleDateString() : 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12"><strong>Purpose:</strong> ${escapeHtml(trip.purpose)}</div>
                            </div>
                            ${trip.route_information ? `<div class="row mb-2"><div class="col-md-12"><strong>Route:</strong> ${escapeHtml(trip.route_information)}</div></div>` : ''}
                            ${trip.driver_overtime == 'yes' ? `<div class="alert alert-warning mt-2"><strong>⚠ Overtime required:</strong> ${escapeHtml(trip.reason_for_overtime)}</div>` : ''}
                        </div>
                    `;
                    Swal.fire({
                        title: `Trip Request #${trip.id}`,
                        html: detailsHtml,
                        width: '700px',
                        confirmButtonText: 'Close'
                    });
                } else {
                    Swal.fire('Error', 'Could not load details', 'error');
                }
            })
            .catch(err => {
                window.location.href = '<?= URL ?>intrastate/view/' + requestId;
            });
    }
    
    function getStatusColor(status) {
        if (!status) return 'secondary';
        const s = status.toLowerCase();
        if (s === 'pending') return 'warning';
        if (s === 'security_approved') return 'info';
        if (s === 'approved') return 'success';
        if (s === 'rejected') return 'danger';
        if (s === 'completed') return 'primary';
        return 'secondary';
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
</script>

</body>
</html>