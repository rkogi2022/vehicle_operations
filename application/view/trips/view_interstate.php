<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interstate Trip Request - Evidence Action</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .detail-container { max-width: 1400px; margin: 0 auto; }
        .page-header { margin-bottom: 28px; }
        .page-header h3 { font-size: 1.75rem; font-weight: 600; color: #1a2c3e; margin-bottom: 12px; }
        .breadcrumb-custom { background: transparent; padding: 0; margin-bottom: 20px; }
        .breadcrumb-custom a { text-decoration: none; font-weight: 500; }
        .breadcrumb-custom .active { color: #6c757d; }
        .card-modern { background: white; border-radius: 20px; border: none; box-shadow: 0 8px 20px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px; }
        .card-header-clean { background: white; padding: 18px 24px; border-bottom: 2px solid #eef2f6; display: flex; align-items: center; gap: 12px; }
        .card-header-clean i { font-size: 1.4rem; color: #1a7f4b; }
        .card-header-clean h5 { margin: 0; font-weight: 700; font-size: 1.2rem; color: #1e2f3e; }
        .card-body-modern { padding: 24px; }
        .status-badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; border-radius: 40px; font-size: 0.8rem; font-weight: 600; background: #e9ecef; color: #2c3e50; }
        .badge-approved { background: #e3f7ec; color: #1e7b48; }
        .badge-pending { background: #fff3e0; color: #cc7b00; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .info-item { border-bottom: 1px solid #f0f2f5; padding-bottom: 12px; }
        .info-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #7c8b9c; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        .info-value { font-size: 0.95rem; font-weight: 500; color: #1a2c3e; word-break: break-word; }
        .two-column-layout { display: grid; grid-template-columns: 1fr 360px; gap: 28px; }
        @media (max-width: 992px) { .two-column-layout { grid-template-columns: 1fr; } }
        .contact-line { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; margin-bottom: 10px; color: #2d3e50; }
        .contact-line i { width: 24px; color: #1a7f4b; }
        .timeline-mini { display: flex; justify-content: space-between; margin-top: 15px; flex-wrap: wrap; gap: 12px; }
        .timeline-step-mini { flex: 1; text-align: center; background: #f8f9fc; border-radius: 16px; padding: 10px 5px; }
        .timeline-step-mini.completed { background: #e9f7ef; border: 1px solid #c3e6cb; }
        .step-icon-mini { font-size: 1.4rem; margin-bottom: 5px; }
        .action-buttons-group { display: flex; flex-direction: column; gap: 12px; }
        .btn-outline-custom { border-radius: 40px; padding: 10px 12px; font-weight: 500; }
        .divider-light { height: 1px; background: #ecf3f9; margin: 20px 0; }
        .alert-modern { background: #fef9e6; border-left: 4px solid #ffb347; border-radius: 14px; padding: 14px 18px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="detail-container">
    <div class="page-header">
        <h3><i class="fas fa-route me-2" style="color: #1a7f4b;"></i>Interstate Trip Request</h3>
        <div class="breadcrumb-custom">
            <a href="<?= URL; ?>home">Home</a> <span class="mx-1">/</span>
            <a href="<?= URL; ?>intrastate/myrequests">My Requests</a> <span class="mx-1">/</span>
            <span>Interstate Request Details</span>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="two-column-layout">
        <!-- LEFT COLUMN -->
        <div>
            <!-- MAIN TRIP CARD -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-route"></i>
                    <h5>Trip Information</h5>
                    <?php
                    if ($request->status == 'security_approved' && $request->assigned_driver_id) {
                        $status_text = 'Driver Assigned'; $status_class = 'badge-approved';
                    } elseif ($request->status == 'security_approved') {
                        $status_text = 'Approved - Awaiting Driver'; $status_class = 'badge-pending';
                    } elseif ($request->status == 'pending') { $status_text = 'Pending Approval'; $status_class = 'badge-pending'; }
                    elseif ($request->status == 'completed') { $status_text = 'Completed'; $status_class = 'badge-approved'; }
                    elseif ($request->status == 'rejected') { $status_text = 'Rejected'; $status_class = ''; }
                    elseif ($request->status == 'draft') { $status_text = 'Draft'; $status_class = ''; }
                    else { $status_text = ucfirst(str_replace('_', ' ', $request->status)); $status_class = 'badge-pending'; }
                    ?>
                    <div class="ms-auto">
                        <span class="status-badge <?= $status_class; ?>">
                            <i class="fas fa-check-circle"></i>
                            <?= $status_text ?>
                        </span>
                    </div>
                </div>
                <div class="card-body-modern">
                    <!-- Locations -->
                    <div class="info-grid" style="margin-bottom: 20px;">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-map-pin"></i> Departure State (Vehicle Location)</div>
                            <div class="info-value"><?= htmlspecialchars($request->vehicle_location_state_name ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-location-dot"></i> Arrival State</div>
                            <div class="info-value"><?= htmlspecialchars($request->arrival_state_name ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-city"></i> Destination City</div>
                            <div class="info-value"><?= htmlspecialchars($request->destination_city ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-location-arrow"></i> Final Destination Address</div>
                            <div class="info-value"><?= htmlspecialchars($request->trip_destination); ?></div>
                        </div>
                    </div>

                    <div class="divider-light"></div>

                    <!-- Dates & Times -->
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-calendar-day"></i> Trip Date</div>
                            <div class="info-value"><?= date('F j, Y', strtotime($request->trip_date)); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-calendar-week"></i> Return Date</div>
                            <div class="info-value"><?= date('F j, Y', strtotime($request->return_date)); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-hourglass-half"></i> Total Nights</div>
                            <div class="info-value"><?= $request->total_nights; ?> nights</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-clock"></i> Destination Arrival Time</div>
                            <div class="info-value"><?= $request->trip_destination_time; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-map-marker-alt"></i> Pickup Location</div>
                            <div class="info-value"><?= htmlspecialchars($request->pickup_location); ?></div>
                        </div>
                        <?php if ($request->need_driver_pickup == 'yes'): ?>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-person-walking-arrow-right"></i> Driver Pickup Time</div>
                            <div class="info-value"><?= $request->pickup_time ?? '—'; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="divider-light"></div>

                    <!-- Purpose & Route -->
                    <div class="info-grid">
                        <div class="info-item" style="grid-column: span 2;">
                            <div class="info-label"><i class="fas fa-bullhorn"></i> Purpose of Trip</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($request->purpose)); ?></div>
                        </div>
                        <?php if ($request->route_information): ?>
                        <div class="info-item" style="grid-column: span 2;">
                            <div class="info-label"><i class="fas fa-road"></i> Route Information</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($request->route_information ?? '')); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="divider-light"></div>

                    <!-- Mode of Travel -->
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-car-alt"></i> Mode of Travel</div>
                            <div class="info-value"><?= ucfirst($request->mode_of_travel ?? 'road'); ?></div>
                        </div>
                        <?php if (in_array($request->mode_of_travel, ['air', 'both'])): ?>
                        <?php
                        $confirmedDep = $request->operations_departure_airline ?? $request->requester_departure_airline ?? null;
                        $confirmedRet = $request->operations_return_airline    ?? $request->requester_return_airline    ?? null;
                        $depChanged   = $request->operations_departure_airline && $request->requester_departure_airline && $request->operations_departure_airline !== $request->requester_departure_airline;
                        $retChanged   = $request->operations_return_airline    && $request->requester_return_airline    && $request->operations_return_airline    !== $request->requester_return_airline;
                        ?>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-plane-departure"></i> Departure Airline</div>
                            <div class="info-value">
                                <?= htmlspecialchars($confirmedDep ?? '—'); ?>
                                <?php if ($depChanged): ?>
                                    <br><small class="text-muted">Requested: <?= htmlspecialchars($request->requester_departure_airline) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-plane-arrival"></i> Return Airline</div>
                            <div class="info-value">
                                <?= htmlspecialchars($confirmedRet ?? '—'); ?>
                                <?php if ($retChanged): ?>
                                    <br><small class="text-muted">Requested: <?= htmlspecialchars($request->requester_return_airline) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-shuttle-van"></i> Airport Pickup Required</div>
                            <div class="info-value"><?= ucfirst($request->require_airport_pickup ?? 'no'); ?></div>
                        </div>
                        <?php if ($request->require_airport_pickup == 'yes'): ?>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-location-pin"></i> Drop-off After Pickup</div>
                            <div class="info-value"><?= htmlspecialchars($request->airport_pickup_dropoff_destination ?? '—'); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Hotel -->
                    <?php if ($request->require_hotel == 'yes'): ?>
                    <div class="divider-light"></div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-hotel"></i> Hotel Accommodation</div>
                            <div class="info-value">
                                <?php if ($request->hotel_id && isset($request->hotel_name_from_vendor)): ?>
                                    <?= htmlspecialchars($request->hotel_name_from_vendor); ?>
                                <?php elseif ($request->hotel_other_name): ?>
                                    <?= htmlspecialchars($request->hotel_other_name); ?> (Manual entry)
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($request->hotel_location): ?>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-map-pin"></i> Hotel Location</div>
                            <div class="info-value"><?= htmlspecialchars($request->hotel_location ?? ''); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="divider-light"></div>

                    <!-- Funder & Overtime -->
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-tag"></i> Funder Code</div>
                            <div class="info-value"><?= htmlspecialchars($request->funder_code_name ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-clock"></i> Driver Overtime</div>
                            <div class="info-value"><?= ucfirst($request->driver_overtime); ?></div>
                        </div>
                    </div>

                    <?php if ($request->driver_overtime == 'yes'): ?>
                    <div class="alert alert-light mt-3 rounded-4">
                        <div class="info-label"><i class="fas fa-chart-line"></i> Overtime Details</div>
                        <div class="info-value"><strong>Activity:</strong> <?= nl2br(htmlspecialchars($request->trip_activity ?? '')); ?></div>
                        <div class="info-value mt-1"><strong>Reason:</strong> <?= nl2br(htmlspecialchars($request->reason_for_overtime ?? '')); ?></div>
                        <div class="info-value mt-1"><strong>Overtime Manager:</strong> <?= htmlspecialchars($request->overtime_manager_email ?? ''); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($request->assigned_driver_id): ?>
                    <div class="alert alert-success mt-3 rounded-4">
                        <i class="fas fa-user-check me-2"></i> <strong>Assigned Driver:</strong>
                        <?= htmlspecialchars($request->driver_name ?? $request->driver_email ?? ''); ?>
                        <?php if ($request->driver_phone ?? null): ?>(<?= htmlspecialchars($request->driver_phone) ?>)<?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- APPROVAL TIMELINE -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-chart-simple"></i>
                    <h5>Approval Journey</h5>
                </div>
                <div class="card-body-modern">
                    <div class="timeline-mini">
                        <div class="timeline-step-mini <?= $request->reviewer_approved_at ? 'completed' : '' ?>">
                            <div class="step-icon-mini"><i class="fas fa-user-tie"></i></div>
                            <div><strong>Supervisor</strong></div>
                            <div class="small text-muted"><?= $request->reviewer_approved_at ? date('M d', strtotime($request->reviewer_approved_at)) : 'Pending' ?></div>
                        </div>
                        <div class="timeline-step-mini <?= $request->security_manager_approved_at ? 'completed' : '' ?>">
                            <div class="step-icon-mini"><i class="fas fa-shield-alt"></i></div>
                            <div><strong>Security</strong></div>
                            <div class="small text-muted"><?= $request->security_manager_approved_at ? date('M d', strtotime($request->security_manager_approved_at)) : 'Pending' ?></div>
                        </div>
                        <div class="timeline-step-mini <?= $request->assigned_driver_id ? 'completed' : '' ?>">
                            <div class="step-icon-mini"><i class="fas fa-truck"></i></div>
                            <div><strong>Driver</strong></div>
                            <div class="small text-muted"><?= $request->assigned_driver_id ? 'Assigned' : 'Awaiting' ?></div>
                        </div>
                        <div class="timeline-step-mini <?= $request->status == 'completed' ? 'completed' : '' ?>">
                            <div class="step-icon-mini"><i class="fas fa-flag-checkered"></i></div>
                            <div><strong>Completed</strong></div>
                            <div class="small text-muted"><?= $request->status == 'completed' ? 'Yes' : 'Pending' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div>
            <!-- Staff Info -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-user-circle"></i>
                    <h5>Staff Information</h5>
                </div>
                <div class="card-body-modern">
                    <div class="contact-line"><i class="fas fa-envelope"></i> <?= htmlspecialchars($request->staff_email); ?></div>
                    <div class="contact-line"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($request->staff_phone); ?></div>
                    <div class="contact-line">
                        <i class="fas fa-file-signature"></i>
                        <strong>TAF Filled & Approved:</strong>
                        <?php if ($request->taf_approved == 'yes'): ?>
                            <span class="badge bg-success ms-1">Yes</span>
                        <?php else: ?>
                            <span class="badge bg-danger ms-1">No</span>
                        <?php endif; ?>
                    </div>
                    <div class="contact-line"><i class="fas fa-user-tie"></i> <strong>Supervisor:</strong> <?= htmlspecialchars($request->supervisor_email); ?></div>
                </div>
            </div>

            <!-- Approvers -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-users-viewfinder"></i>
                    <h5>Approvers</h5>
                </div>
                <div class="card-body-modern">
                    <div class="contact-line"><i class="fas fa-user-tie"></i> <strong>Supervisor:</strong> <?= htmlspecialchars($request->supervisor_email ?? ''); ?></div>
                    <?php if ($request->security_manager_email): ?>
                    <div class="contact-line"><i class="fas fa-shield-virus"></i> <strong>Security Manager:</strong> <?= htmlspecialchars($request->security_manager_email); ?></div>
                    <?php endif; ?>
                    <div class="divider-light" style="margin: 12px 0;"></div>
                    <div class="info-label" style="margin-bottom: 8px;"><i class="fas fa-headset"></i> Operations Team</div>
                    <div class="contact-line"><i class="fas fa-envelope"></i> <strong>TO:</strong> <?= htmlspecialchars($request->reviewer_email ?? ''); ?></div>
                    <?php if ($request->co_reviewer_email): ?>
                    <div class="contact-line"><i class="fas fa-envelope-open"></i> <strong>CC:</strong> <?= htmlspecialchars($request->co_reviewer_email ?? ''); ?></div>
                    <?php endif; ?>
                    <?php if ($request->manager_email): ?>
                    <div class="contact-line"><i class="fas fa-envelope-square"></i> <strong>BCC:</strong> <?= htmlspecialchars($request->manager_email ?? ''); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rejection Details -->
            <?php if ($request->status == 'rejected' && $request->rejection_reason): ?>
            <div class="card-modern" style="border-left: 4px solid #dc3545;">
                <div class="card-header-clean">
                    <i class="fas fa-ban" style="color:#dc3545;"></i>
                    <h5 style="color:#b91c1c;">Rejection Details</h5>
                </div>
                <div class="card-body-modern">
                    <div class="info-label">Reason:</div>
                    <div class="info-value mb-2"><?= nl2br(htmlspecialchars($request->rejection_reason ?? '')); ?></div>
                    <div class="info-label">Rejected by:</div>
                    <div class="info-value"><?= htmlspecialchars($request->rejected_by ?? ''); ?><?= $request->rejected_at ? ' on ' . date('M j, H:i', strtotime($request->rejected_at)) : ''; ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-clipboard-list"></i>
                    <h5>Actions</h5>
                </div>
                <div class="card-body-modern">
                    <?php
                    $user_email = $_SESSION['user_email'];
                    $role = $_SESSION['role'] ?? '';
                    $is_requester = ($request->staff_email == $user_email);
                    ?>
                    <div class="alert-modern mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php
                        if ($request->status == 'draft') echo "Draft — submit when ready.";
                        elseif ($request->status == 'pending') echo "Awaiting approval. You'll be notified once processed.";
                        elseif ($request->status == 'rejected') echo "Request was rejected. See rejection details.";
                        elseif ($request->status == 'security_approved' && $request->assigned_driver_id) echo "✅ Driver assigned. Your trip is confirmed — check driver details below.";
                        elseif ($request->status == 'security_approved') echo "✅ Approved! Operations team will assign a driver.";
                        elseif ($request->status == 'completed') echo "Trip completed successfully.";
                        elseif ($request->status == 'cancelled') echo "This request has been cancelled.";
                        else echo "Your request is being processed.";
                        ?>
                    </div>
                    <div class="action-buttons-group">
                        <?php if ($is_requester && $request->status == 'draft'): ?>
                            <a href="<?= URL; ?>interstate/edit/<?= $request->id; ?>" class="btn btn-outline-primary btn-outline-custom">
                                <i class="fas fa-edit"></i> Edit Draft
                            </a>
                            <button onclick="if(confirm('Delete draft permanently?')) window.location.href='<?= URL; ?>interstate/delete/<?= $request->id; ?>'" class="btn btn-outline-danger btn-outline-custom">
                                <i class="fas fa-trash-alt"></i> Delete Draft
                            </button>
                        <?php endif; ?>

                        <?php if ($is_requester && !in_array($request->status, ['completed', 'rejected', 'cancelled', 'draft'])): ?>
                            <button onclick="if(confirm('Cancel this request?')) window.location.href='<?= URL; ?>interstate/cancel/<?= $request->id; ?>'" class="btn btn-outline-warning btn-outline-custom">
                                <i class="fas fa-times-circle"></i> Cancel Request
                            </button>
                        <?php endif; ?>

                        <?php if ($request->status == 'security_approved' && !$request->assigned_driver_id): ?>
                            <div class="alert alert-secondary rounded-4 mb-0" style="background:#F4F6F9;">
                                <i class="fas fa-hourglass-half"></i> <strong>Driver assignment pending</strong><br>
                                <small>Operations team will assign a driver. No action needed.</small>
                            </div>
                        <?php endif; ?>

                        <?php
                        $isOperations = ($role == 'admin' || $role == 'super_admin');
                        if (!$isOperations && isset($request->reviewer_email)) {
                            $isOperations = ($user_email == $request->reviewer_email || $user_email == $request->co_reviewer_email || $user_email == $request->manager_email);
                        }
                        ?>
                        <?php if ($isOperations && $request->status == 'security_approved' && $request->assigned_driver_id): ?>
                            <button onclick="if(confirm('Mark trip #<?= $request->id ?> as completed?')) window.location.href='<?= URL ?>interstate/complete/<?= $request->id ?>'"
                                class="btn btn-success btn-outline-custom">
                                <i class="fas fa-flag-checkered me-1"></i> Mark as Completed
                            </button>
                        <?php endif; ?>

                        <a href="<?= URL; ?>intrastate/myrequests" class="btn btn-secondary btn-outline-custom mt-1">
                            <i class="fas fa-arrow-left"></i> Back to My Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
