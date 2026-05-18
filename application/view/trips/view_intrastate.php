<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intrastate Trip Request - Evidence Action</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

        .detail-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header & Breadcrumb */
        .page-header {
            margin-bottom: 28px;
        }

        .page-header h3 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1a2c3e;
            margin-bottom: 12px;
        }

        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }

        .breadcrumb-custom a {
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-custom .active {
            color: #6c757d;
        }

        /* Cards */
        .card-modern {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 24px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-modern:hover {
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .card-header-clean {
            background: white;
            padding: 18px 24px;
            border-bottom: 2px solid #eef2f6;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header-clean i {
            font-size: 1.4rem;
            color: #2c7a4d;
        }

        .card-header-clean h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.2rem;
            color: #1e2f3e;
        }

        .card-body-modern {
            padding: 24px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
            background: #e9ecef;
            color: #2c3e50;
        }

        .badge-approved {
            background: #e3f7ec;
            color: #1e7b48;
        }

        .badge-pending {
            background: #fff3e0;
            color: #cc7b00;
        }

        /* Info Grid - Clean Layout */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .info-item {
            border-bottom: 1px solid #f0f2f5;
            padding-bottom: 12px;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #7c8b9c;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 500;
            color: #1a2c3e;
            word-break: break-word;
        }

        .info-value strong {
            font-weight: 700;
            color: #0b2b26;
        }

        /* Two column main section */
        .two-column-layout {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 28px;
        }

        @media (max-width: 992px) {
            .two-column-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Sidebar cards */
        .contact-card {
            background: #fafcfc;
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 18px;
            border: 1px solid #edf2f7;
        }

        .contact-title {
            font-weight: 700;
            color: #1e4663;
            margin-bottom: 12px;
            font-size: 0.9rem;
            border-left: 3px solid #2c7a4d;
            padding-left: 10px;
        }

        .contact-line {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            margin-bottom: 10px;
            color: #2d3e50;
        }

        .contact-line i {
            width: 24px;
            color: #2c7a4d;
        }

        /* Approval timeline mini */
        .timeline-mini {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .timeline-step-mini {
            flex: 1;
            text-align: center;
            background: #f8f9fc;
            border-radius: 16px;
            padding: 10px 5px;
            transition: all 0.2s;
        }

        .timeline-step-mini.completed {
            background: #e9f7ef;
            border: 1px solid #c3e6cb;
        }

        .step-icon-mini {
            font-size: 1.4rem;
            margin-bottom: 5px;
        }

        /* Action Buttons */
        .action-buttons-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-outline-custom {
            border-radius: 40px;
            padding: 10px 12px;
            font-weight: 500;
            transition: all 0.2s;
        }

        /* Divider */
        .divider-light {
            height: 1px;
            background: #ecf3f9;
            margin: 20px 0;
        }

        /* Alert modern */
        .alert-modern {
            background: #fef9e6;
            border-left: 4px solid #ffb347;
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="detail-container">
    <!-- Header & Breadcrumb -->
    <div class="page-header">
        <h3><i class="fas fa-map-marked-alt me-2" style="color: #2c7a4d;"></i>Intrastate Trip Request</h3>
        <div class="breadcrumb-custom">
            <a href="<?= URL; ?>home"> Home</a> <span class="mx-1">/</span>
            <a href="<?= URL; ?>intrastate/myrequests">My Requests</a> <span class="mx-1">/</span>
            <span>Request Details</span>
        </div>
    </div>

    <!-- Session Alerts -->
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

    <!-- MAIN TWO COLUMN LAYOUT -->
    <div class="two-column-layout">
        <!-- LEFT COLUMN: Trip Details + Timeline -->
        <div>
            <!-- MAIN TRIP CARD -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-truck-moving"></i>
                    <h5>Trip Information</h5>
                    <?php
                    $status_text = '';
                    $status_class = 'badge-pending';
                    if ($request->status == 'security_approved') {
                        $status_text = 'Approved - Awaiting Driver';
                        $status_class = 'badge-approved';
                    } elseif ($request->status == 'pending') {
                        $status_text = 'Pending Approval';
                        $status_class = 'badge-pending';
                    } elseif ($request->status == 'completed') {
                        $status_text = 'Completed';
                        $status_class = 'badge-approved';
                    } elseif ($request->status == 'rejected') {
                        $status_text = 'Rejected';
                    } elseif ($request->status == 'draft') {
                        $status_text = 'Draft';
                    } else {
                        $status_text = ucfirst($request->status);
                    }
                    ?>
                    <div class="ms-auto">
                        <span class="status-badge <?= $status_class ?? ''; ?>">
                            <i class="fas <?= $request->status == 'security_approved' ? 'fa-check-circle' : 'fa-clock' ?>"></i> 
                            <?= $status_text ?>
                        </span>
                    </div>
                </div>
                <div class="card-body-modern">
                    <!-- FIRST ROW: destination & location -->
                    <div class="info-grid" style="margin-bottom: 20px;">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-location-dot"></i> Trip Destination</div>
                            <div class="info-value"><?= htmlspecialchars($request->trip_destination); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-map-pin"></i> Vehicle Location (State)</div>
                            <div class="info-value"><?= htmlspecialchars($request->vehicle_location_state_name); ?> (<?= htmlspecialchars($request->vehicle_location_code); ?>)</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-calendar-day"></i> Trip Date</div>
                            <div class="info-value"><?= date('F j, Y', strtotime($request->trip_date)); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-calendar-week"></i> Return Date</div>
                            <div class="info-value"><?= date('F j, Y', strtotime($request->return_date)); ?></div>
                        </div>
                    </div>

                    <div class="divider-light"></div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-hourglass-half"></i> Total Nights</div>
                            <div class="info-value"><?= $request->total_nights; ?> nights</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-map-marker-alt"></i> Pickup Location</div>
                            <div class="info-value"><?= htmlspecialchars($request->pickup_location); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-clock"></i> Arrival Time</div>
                            <div class="info-value"><?= $request->trip_destination_time; ?></div>
                        </div>
                        <?php if ($request->need_driver_pickup == 'yes'): ?>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-person-walking-arrow-right"></i> Pickup Time</div>
                            <div class="info-value"><?= $request->pickup_time; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="divider-light"></div>

                    <div class="info-grid">
                        <div class="info-item" style="grid-column: span 2;">
                            <div class="info-label"><i class="fas fa-bullhorn"></i> Purpose of Trip</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($request->purpose)); ?></div>
                        </div>
                        <?php if ($request->route_information): ?>
                        <div class="info-item" style="grid-column: span 2;">
                            <div class="info-label"><i class="fas fa-road"></i> Route Information</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($request->route_information)); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="divider-light"></div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-tag"></i> Funder Code</div>
                            <div class="info-value"><?= htmlspecialchars($request->funder_code_name); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-clock"></i> Driver Overtime Required</div>
                            <div class="info-value"><?= ucfirst($request->driver_overtime); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-car-side"></i> Need Driver Pickup</div>
                            <div class="info-value"><?= ucfirst($request->need_driver_pickup); ?></div>
                        </div>
                    </div>

                    <?php if ($request->driver_overtime == 'yes' && $request->reason_for_overtime): ?>
                    <div class="alert alert-light mt-3 rounded-4">
                        <div class="info-label"><i class="fas fa-chart-line"></i> Overtime Details</div>
                        <div class="info-value"><strong>Activity:</strong> <?= nl2br(htmlspecialchars($request->trip_activity ?? '')); ?></div>
                        <div class="info-value mt-1"><strong>Reason:</strong> <?= nl2br(htmlspecialchars($request->reason_for_overtime ?? '')); ?></div>
                        <div class="info-value mt-1"><strong>Overtime Manager:</strong> <?= htmlspecialchars($request->overtime_manager_email ?? ''); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($request->assigned_driver_id): ?>
                    <div class="alert alert-success mt-3 rounded-4">
                        <i class="fas fa-user-check me-2"></i> <strong>Assigned Driver:</strong> <?= htmlspecialchars($request->driver_email ?? ''); ?> (<?= htmlspecialchars($request->driver_phone ?? ''); ?>)
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- APPROVAL TIMELINE (simplified) -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-chart-simple"></i>
                    <h5>Approval Journey</h5>
                </div>
                <div class="card-body-modern">
                    <div class="timeline-mini">
                        <div class="timeline-step-mini <?= $request->reviewer_approved_at ? 'completed' : '' ?>">
                            <div class="step-icon-mini"><i class="fas fa-user-check"></i></div>
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
                            <div><strong>Driver Assign</strong></div>
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

        <!-- RIGHT COLUMN: Staff, Approvers, Next Steps & Actions (Neat & organized) -->
        <div>
            <!-- Staff information block -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-user-circle"></i>
                    <h5>Staff Information</h5>
                </div>
                <div class="card-body-modern">
                    <div class="contact-line"><i class="fas fa-user"></i> <strong><?= htmlspecialchars($request->staff_name ?? ''); ?></strong></div>
                    <div class="contact-line"><i class="fas fa-envelope"></i> <?= htmlspecialchars($request->staff_email); ?></div>
                    <div class="contact-line"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($request->staff_phone); ?></div>
                    <div class="contact-line"><i class="fas fa-user-tie"></i> <strong>Supervisor:</strong> <?= htmlspecialchars($request->supervisor_email); ?></div>
                </div>
            </div>

            <!-- Approvers / Operations -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-users-viewfinder"></i>
                    <h5>Approvers & Operations</h5>
                </div>
                <div class="card-body-modern">
                    <div class="contact-line"><i class="fas fa-user-check"></i> <strong>Reviewer:</strong> <?= htmlspecialchars($request->reviewer_email); ?></div>
                    <?php if ($request->co_reviewer_email): ?>
                    <div class="contact-line"><i class="fas fa-user-friends"></i> <strong>Co-Reviewer:</strong> <?= htmlspecialchars($request->co_reviewer_email); ?></div>
                    <?php endif; ?>
                    <div class="contact-line"><i class="fas fa-briefcase"></i> <strong>Manager:</strong> <?= htmlspecialchars($request->manager_email); ?></div>
                    <?php if ($request->security_manager_email): ?>
                    <div class="contact-line"><i class="fas fa-shield-virus"></i> <strong>Security Manager:</strong> <?= htmlspecialchars($request->security_manager_email); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rejection details if any -->
            <?php if ($request->status == 'rejected' && $request->rejection_reason): ?>
            <div class="card-modern" style="border-left: 4px solid #dc3545;">
                <div class="card-header-clean">
                    <i class="fas fa-ban" style="color: #dc3545;"></i>
                    <h5 style="color:#b91c1c;">Rejection Details</h5>
                </div>
                <div class="card-body-modern">
                    <div class="info-label">Reason:</div>
                    <div class="info-value mb-2"><?= nl2br(htmlspecialchars($request->rejection_reason ?? '')); ?></div>
                    <div class="info-label">Rejected by:</div>
                    <div class="info-value"><?= htmlspecialchars($request->rejected_by ?? ''); ?> on <?= $request->rejected_at ? date('M j, H:i', strtotime($request->rejected_at)) : ''; ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- NEXT STEPS & ACTIONS (Neat, no driver assignment form) -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <i class="fas fa-clipboard-list"></i>
                    <h5>Next Steps & Actions</h5>
                </div>
                <div class="card-body-modern">
                    <?php 
                    $user_email = $_SESSION['user_email'];
                    $role = $_SESSION['role'] ?? '';
                    $operations_team = [$request->reviewer_email, $request->co_reviewer_email, $request->manager_email];
                    $is_requester = ($request->staff_email == $user_email);
                    ?>
                    
                    <!-- Status info message -->
                    <div class="alert-modern mb-3">
                        <i class="fas fa-info-circle me-2"></i> 
                        <?php 
                        if($request->status == 'draft') echo "Your request is in draft mode. Submit for approval when ready.";
                        elseif($request->status == 'pending') echo "Awaiting supervisor review. You'll be notified once approved.";
                        elseif($request->status == 'rejected') echo "This request was rejected. Please review the rejection reason above.";
                        elseif($request->status == 'security_approved') echo "✅ Security approved! The operations team will assign a driver. You will receive an update once driver is assigned.";
                        elseif($request->status == 'completed') echo "Trip completed successfully.";
                        elseif($request->status == 'cancelled') echo "This trip request has been cancelled.";
                        else echo "Your request is being processed.";
                        ?>
                    </div>

                    <div class="action-buttons-group">
                        <!-- Draft: edit / delete -->
                        <?php if ($is_requester && $request->status == 'draft'): ?>
                            <a href="<?= URL; ?>intrastate/edit/<?= $request->id; ?>" class="btn btn-outline-primary btn-outline-custom">
                                <i class="fas fa-edit"></i> Edit Draft
                            </a>
                            <button onclick="if(confirm('Delete draft permanently?')) window.location.href='<?= URL; ?>intrastate/delete/<?= $request->id; ?>'" class="btn btn-outline-danger btn-outline-custom">
                                <i class="fas fa-trash-alt"></i> Delete Draft
                            </button>
                        <?php endif; ?>

                        <!-- Cancel (for requester, if not final) -->
                        <?php if ($is_requester && !in_array($request->status, ['completed', 'rejected', 'cancelled', 'draft'])): ?>
                            <button onclick="if(confirm('Cancel this trip request?')) window.location.href='<?= URL; ?>intrastate/cancel/<?= $request->id; ?>'" class="btn btn-outline-warning btn-outline-custom">
                                <i class="fas fa-times-circle"></i> Cancel Request
                            </button>
                        <?php endif; ?>

                        <!-- For Operations: Mark completed (only if driver assigned) -->
                        <?php if ((in_array($user_email, $operations_team) || $role == 'admin') && $request->assigned_driver_id && !in_array($request->status, ['completed', 'cancelled'])): ?>
                            <button onclick="if(confirm('Mark trip as completed?')) window.location.href='<?= URL; ?>intrastate/complete/<?= $request->id; ?>'" class="btn btn-success btn-outline-custom">
                                <i class="fas fa-check-double"></i> Mark as Completed
                            </button>
                        <?php endif; ?>

                        <!-- Info message when driver pending (No assign form) -->
                        <?php if ($request->status == 'security_approved' && !$request->assigned_driver_id): ?>
                            <div class="alert alert-secondary mt-1 mb-0 rounded-4" style="background:#F4F6F9;">
                                <i class="fas fa-hourglass-half"></i> <strong>Driver assignment in progress</strong><br>
                                <small>Operations team will assign a driver from the pool. No action needed from you.</small>
                            </div>
                        <?php endif; ?>

                        <!-- Back link -->
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