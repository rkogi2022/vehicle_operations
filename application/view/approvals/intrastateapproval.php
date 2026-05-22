<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operations Dashboard - Pending Driver Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>

        .stats-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 16px;
            overflow: hidden;
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
        .table-custom {
            margin-bottom: 0;
        }
        .table-custom th {
            background: #f8f9fc;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 15px;
        }
        .table-custom td {
            padding: 15px;
            vertical-align: middle;
        }
        .driver-select {
            min-width: 180px;
            border-radius: 8px;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            font-size: 0.85rem;
        }
        .driver-select:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-assign {
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h3 class="mt-2 fw-bold" style="color: #1a2c3e;">
            Operations Dashboard
        </h3>
        <div>
        </div>
    </div>
    
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-4 bg-transparent p-0">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home"> Home</a></li>
        <li class="breadcrumb-item ">Driver Assignment</li>
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
    // Ensure variables are arrays
    $pendingDriverRequests = isset($pendingDriverRequests) && is_array($pendingDriverRequests) ? $pendingDriverRequests : [];
    $availableDrivers = isset($availableDrivers) && is_array($availableDrivers) ? $availableDrivers : [];
    
    $awaitingCount = count($pendingDriverRequests);
    $driversCount = count($availableDrivers);
    ?>
    
    <!-- Info Box -->
    <div class="info-box">
        <i class="fas fa-info-circle me-2 text-primary"></i> 
        <strong>Driver Assignment:</strong> 
        You have <strong><?= $awaitingCount ?></strong> trip(s) awaiting driver assignment.
        Use the dropdown to select a driver for each trip and click "Assign".
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="card stats-card bg-warning bg-opacity-10 border-0 border-bottom border-3 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-1">Awaiting Driver</h6>
                            <h2 class="mb-0 fw-bold"><?= $awaitingCount ?></h2>
                            <small class="text-muted">Need driver assignment</small>
                        </div>
                        <i class="fas fa-hourglass-half fa-3x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card bg-success bg-opacity-10 border-0 border-bottom border-3 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-1">Available Drivers</h6>
                            <h2 class="mb-0 fw-bold"><?= $driversCount ?></h2>
                            <small class="text-muted">Ready to assign</small>
                        </div>
                        <i class="fas fa-users fa-3x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card bg-primary bg-opacity-10 border-0 border-bottom border-3 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary mb-1">Total Assigned</h6>
                            <h2 class="mb-0 fw-bold"><?= $stats['in_progress'] ?? 0 ?></h2>
                            <small class="text-muted">In progress</small>
                        </div>
                        <i class="fas fa-truck fa-3x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Table -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header-custom">
            <i class="fas fa-list me-2 text-primary"></i>
            <strong>Trips Awaiting Driver Assignment</strong>
            <span class="badge bg-warning ms-2"><?= $awaitingCount ?></span>
        </div>
        <div class="card-body p-0">
            <?php if ($awaitingCount > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Requester</th>
                                <th>Destination</th>
                                <th>Trip Date</th>
                                <th>Pickup Location</th>
                                <th>Purpose</th>
                                <th width="220">Assign Driver</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingDriverRequests as $trip): ?>
                                <?php 
                                    // Handle both object and array formats
                                    if (is_object($trip)) {
                                        $tripId = $trip->id;
                                        $requesterEmail = $trip->staff_email ?? 'N/A';
                                        $requesterPhone = $trip->staff_phone ?? '';
                                        $destination = $trip->trip_destination ?? 'N/A';
                                        $pickupLocation = $trip->pickup_location ?? 'N/A';
                                        $tripDate = $trip->trip_date ?? date('Y-m-d');
                                        $purpose = $trip->purpose ?? '';
                                        $returnDate = $trip->return_date ?? null;
                                        $tripDestinationTime = $trip->trip_destination_time ?? '';
                                        $needDriverPickup = $trip->need_driver_pickup ?? 'no';
                                        $pickupTime = $trip->pickup_time ?? '';
                                    } else {
                                        $tripId = $trip['id'] ?? 0;
                                        $requesterEmail = $trip['staff_email'] ?? 'N/A';
                                        $requesterPhone = $trip['staff_phone'] ?? '';
                                        $destination = $trip['trip_destination'] ?? 'N/A';
                                        $pickupLocation = $trip['pickup_location'] ?? 'N/A';
                                        $tripDate = $trip['trip_date'] ?? date('Y-m-d');
                                        $purpose = $trip['purpose'] ?? '';
                                        $returnDate = $trip['return_date'] ?? null;
                                        $tripDestinationTime = $trip['trip_destination_time'] ?? '';
                                        $needDriverPickup = $trip['need_driver_pickup'] ?? 'no';
                                        $pickupTime = $trip['pickup_time'] ?? '';
                                    }
                                    
                                    // Get short name from email
                                    $requesterName = explode('@', $requesterEmail)[0];
                                ?>
                                <tr>
                                    <td class="fw-bold">#<?= $tripId ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($requesterName) ?></div>
                                        <small class="text-muted">📞 <?= htmlspecialchars($requesterPhone) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($destination) ?></strong>
                                        <?php if ($tripDestinationTime): ?>
                                            <br><small class="text-muted">Arrival: <?= $tripDestinationTime ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($tripDate)) ?>
                                        <?php if ($returnDate && $returnDate != '0000-00-00' && $returnDate != $tripDate): ?>
                                            <br><small class="text-muted">Return: <?= date('M d', strtotime($returnDate)) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($pickupLocation) ?>
                                        <?php if ($needDriverPickup == 'yes' && $pickupTime): ?>
                                            <br><small class="text-primary">⏰ Pickup: <?= $pickupTime ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span title="<?= htmlspecialchars($purpose) ?>">
                                            <?= substr(htmlspecialchars($purpose), 0, 40) ?>...
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?= URL ?>intrastate/assignDriver/<?= $tripId ?>" class="d-flex gap-2" onsubmit="return confirmAssignment(event, this)">
                                            <select name="driver_id" class="driver-select form-select" required>
                                                <option value="">-- Select Driver --</option>
                                                <?php foreach ($availableDrivers as $driver):
                                                    $driverId    = is_object($driver) ? $driver->id    : ($driver['id'] ?? 0);
                                                    $driverName  = is_object($driver) ? ($driver->name ?? $driver->driver_name ?? '') : ($driver['name'] ?? $driver['driver_name'] ?? '');
                                                    $driverPhone = is_object($driver) ? ($driver->phone ?? '') : ($driver['phone'] ?? '');
                                                ?>
                                                    <option value="<?= $driverId ?>">
                                                        <?= htmlspecialchars($driverName) ?> - <?= htmlspecialchars($driverPhone) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                    </td>
                                    <td>
                                            <button type="submit" class="btn btn-success btn-assign">
                                                <i class="fas fa-user-check me-1"></i> Assign
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle text-success"></i>
                    <h5>No Trips Awaiting Driver Assignment</h5>
                    <p class="text-muted">All security-approved trips have been assigned to drivers.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- In Progress — Driver Assigned -->
    <?php
    $inProgressRequests = isset($inProgressRequests) && is_array($inProgressRequests) ? $inProgressRequests : [];
    $driversByState     = isset($driversByState)     && is_array($driversByState)     ? $driversByState     : [];
    $allDrivers         = isset($allDrivers)         && is_array($allDrivers)         ? $allDrivers         : $availableDrivers;
    if (!empty($inProgressRequests)):
    ?>
    <div class="card shadow-sm border-0 rounded-4 mt-4">
        <div class="card-header-custom">
            <i class="fas fa-truck me-2 text-primary"></i>
            <strong>In Progress — Driver Assigned</strong>
            <span class="badge bg-primary ms-2"><?= count($inProgressRequests) ?></span>
        </div>
        <div class="card-body p-0">
            <?php foreach ($inProgressRequests as $trip):
                $tripPassed  = strtotime($trip->trip_date) < strtotime('today');
                $canEdit     = !$tripPassed;
                $ipId        = $trip->id;
                $tripDrivers = $driversByState[$trip->vehicle_location_state_id ?? 0] ?? $allDrivers;
            ?>
            <div class="border-bottom p-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <span class="fw-bold">#<?= $ipId ?></span>
                        <span class="ms-2"><?= htmlspecialchars(explode('@', $trip->staff_email)[0]) ?></span>
                        <small class="text-muted ms-2"><?= htmlspecialchars($trip->staff_phone ?? '') ?></small>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($trip->trip_destination ?? '') ?>
                            &mdash; <?= date('M d, Y', strtotime($trip->trip_date)) ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="text-success small">
                            <i class="fas fa-user-check me-1"></i>
                            <strong><?= htmlspecialchars($trip->driver_name ?? $trip->driver_email ?? '—') ?></strong>
                            <?php if ($trip->driver_phone ?? null): ?>
                                <span class="text-muted">(<?= htmlspecialchars($trip->driver_phone) ?>)</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($canEdit): ?>
                        <button class="btn btn-sm btn-warning" type="button"
                            data-bs-toggle="collapse" data-bs-target="#intraedit_<?= $ipId ?>"
                            title="Change driver">
                            <i class="fas fa-edit me-1"></i> Edit
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-success"
                            onclick="confirmIntraComplete(<?= $ipId ?>, '<?= htmlspecialchars(addslashes($trip->trip_destination ?? '')) ?>')">
                            <i class="fas fa-flag-checkered me-1"></i> Complete
                        </button>
                    </div>
                </div>

                <?php if ($canEdit): ?>
                <div class="collapse mt-3" id="intraedit_<?= $ipId ?>">
                    <div class="p-3 rounded" style="background:#fffdf5;border:1px solid #ffe082;">
                        <h6 class="fw-bold text-warning mb-2"><i class="fas fa-edit me-1"></i> Update Driver — Trip #<?= $ipId ?></h6>
                        <form method="POST" action="<?= URL ?>intrastate/assignDriver/<?= $ipId ?>"
                              onsubmit="return confirmIntraUpdate(event, this)">
                            <div class="d-flex gap-2 align-items-end flex-wrap">
                                <div style="min-width:260px;">
                                    <label class="form-label small fw-semibold mb-1">New Driver</label>
                                    <select name="driver_id" class="form-select form-select-sm" required>
                                        <option value="">— Select Driver —</option>
                                        <?php foreach ($tripDrivers as $d):
                                            $dId    = is_object($d) ? $d->id    : ($d['id']    ?? 0);
                                            $dName  = is_object($d) ? ($d->name  ?? '') : ($d['name']  ?? '');
                                            $dPhone = is_object($d) ? ($d->phone ?? '') : ($d['phone'] ?? '');
                                            $sel    = ($trip->assigned_driver_id == $dId) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $dId ?>" <?= $sel ?>><?= htmlspecialchars($dName) ?> — <?= htmlspecialchars($dPhone) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">
                                    <i class="fas fa-save me-1"></i> Save & Notify Requester
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    data-bs-toggle="collapse" data-bs-target="#intraedit_<?= $ipId ?>">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmIntraUpdate(event, form) {
        event.preventDefault();
        const sel = form.querySelector('select[name="driver_id"]');
        if (!sel.value) {
            Swal.fire({ title: 'No Driver Selected', icon: 'warning', confirmButtonText: 'OK' });
            return false;
        }
        Swal.fire({
            title: 'Update Driver?',
            html: 'The requester will receive an email about the change.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e67e22',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Update & Notify'
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    }

    function confirmIntraComplete(id, dest) {
        Swal.fire({
            title: 'Mark as Completed?',
            html: `Mark trip <strong>#${id}</strong> to <strong>${dest}</strong> as completed?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, Complete'
        }).then(r => {
            if (r.isConfirmed) {
                const f = document.createElement('form');
                f.method = 'POST'; f.action = '<?= URL ?>intrastate/complete/' + id;
                document.body.appendChild(f); f.submit();
            }
        });
    }

    function confirmAssignment(event, form) {
        event.preventDefault();
        
        const select = form.querySelector('select[name="driver_id"]');
        const selectedDriver = select.options[select.selectedIndex]?.text;
        
        if (!select.value) {
            Swal.fire({
                title: 'No Driver Selected',
                text: 'Please select a driver before assigning.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        Swal.fire({
            title: 'Assign Driver?',
            html: `Assign <strong>${selectedDriver}</strong> to this trip?<br><small>The requester will be notified via email.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Assign Driver'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
</script>

</body>
</html>