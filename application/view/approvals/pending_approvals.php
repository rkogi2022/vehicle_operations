<?php
$info = $_SESSION['info'] ?? null;
if ($info) unset($_SESSION['info']);
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Pending Approvals</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Pending Approvals</li>
    </ol>

    <?php if ($info): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> <?= htmlspecialchars($info) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bell me-1 text-warning"></i> Trips Awaiting Your Approval</span>
            <small class="text-muted">Both email links and this page can be used to approve or reject</small>
        </div>
        <div class="card-body">
            <?php if (empty($pendingApprovals)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-double fa-3x mb-3 text-success"></i>
                    <p class="mb-0">No trips are currently waiting for your approval.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Requester</th>
                                <th>Destination</th>
                                <th>Trip Date</th>
                                <th>Purpose</th>
                                <th>Your Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingApprovals as $trip): ?>
                                <?php
                                $ctrl = $trip->trip_type === 'interstate' ? 'interstate' : 'intrastate';
                                $staffName = ucwords(str_replace(['.','_','-'], ' ', explode('@', $trip->staff_email)[0]));
                                $roleLabel = $trip->approval_role === 'supervisor' ? 'Supervisor' : 'Security Manager';
                                $roleBadge = $trip->approval_role === 'supervisor' ? 'bg-primary' : 'bg-danger';
                                ?>
                                <tr>
                                    <td><?= $trip->id ?></td>
                                    <td>
                                        <?php if ($trip->trip_type === 'interstate'): ?>
                                            <span class="badge bg-success">Interstate</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Intrastate</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($staffName) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($trip->staff_email) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($trip->trip_destination) ?></td>
                                    <td><?= date('M d, Y', strtotime($trip->trip_date)) ?></td>
                                    <td>
                                        <span title="<?= htmlspecialchars($trip->purpose) ?>" style="cursor:help;">
                                            <?= htmlspecialchars(mb_strimwidth($trip->purpose ?? '', 0, 60, '…')) ?>
                                        </span>
                                    </td>
                                    <td><span class="badge <?= $roleBadge ?>"><?= $roleLabel ?></span></td>
                                    <td>
                                        <a href="<?= URL . $ctrl ?>/view/<?= $trip->id ?>" class="btn btn-sm btn-info mb-1">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success mb-1"
                                                onclick="confirmApprove(<?= $trip->id ?>, '<?= $ctrl ?>')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger mb-1"
                                                onclick="openRejectModal(<?= $trip->id ?>, '<?= $ctrl ?>', '<?= htmlspecialchars($staffName, ENT_QUOTES) ?>', '<?= htmlspecialchars($trip->trip_destination, ENT_QUOTES) ?>')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approve confirmation form (hidden) -->
<form id="approveForm" method="POST" action="" style="display:none;">
</form>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Trip Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST" action="">
                <div class="modal-body">
                    <p id="rejectModalDesc" class="text-muted mb-3"></p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-bold">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4"
                                  placeholder="Please provide a clear reason for rejecting this request..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Submit Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmApprove(id, ctrl) {
    if (!confirm('Approve this trip request? The next approver will be notified by email.')) return;
    const form = document.getElementById('approveForm');
    form.action = '<?= URL ?>' + ctrl + '/systemApprove/' + id;
    form.submit();
}

function openRejectModal(id, ctrl, staffName, destination) {
    document.getElementById('rejectForm').action = '<?= URL ?>' + ctrl + '/systemReject/' + id;
    document.getElementById('rejectModalDesc').textContent =
        'Rejecting trip to "' + destination + '" requested by ' + staffName + '.';
    document.getElementById('rejection_reason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
