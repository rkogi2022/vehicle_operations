<div class="container-fluid px-4">
    <h3 class="mt-4">My Trip Requests</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">My Requests</li>
    </ol>
    
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
            <span><i class="fas fa-list me-1"></i> All Requests</span>
            <div>
                <button class="btn btn-secondary btn-sm ms-2" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table id="requestsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Vehicle Location</th>
                        <th>Trip Destination</th>
                        <th>Trip Date</th>
                        <th>Return Date</th>
                        <th>Funder</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $request): ?>
                        <?php $ctrl = ($request->request_type ?? 'intrastate') === 'interstate' ? 'interstate' : 'intrastate'; ?>
                        <tr>
                            <td><?= $request->id; ?></td>
                            <td>
                                <?php if ($ctrl === 'interstate'): ?>
                                    <span class="badge bg-success">Interstate</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Intrastate</span>
                                <?php endif; ?>
                                <?php if (!empty($request->is_passenger)): ?>
                                    <br><span class="badge bg-secondary mt-1" title="Booked by <?= htmlspecialchars($request->booked_by ?? '') ?>">
                                        <i class="fas fa-user-plus"></i> Booked for you
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($request->vehicle_location_state_name ?? ''); ?></td>
                            <td><?= htmlspecialchars($request->trip_destination); ?></td>
                            <td><?= date('M d, Y', strtotime($request->trip_date)); ?></td>
                            <td><?= date('M d, Y', strtotime($request->return_date)); ?></td>
                            <td><?= htmlspecialchars($request->funder_code_name ?? ''); ?></td>
                            <td>
                                <?php
                                if ($request->status == 'pending') {
                                    $badgeClass = 'warning';
                                    $statusText = 'Pending - ' . ucfirst(str_replace('_', ' ', $request->current_approval_level));
                                } elseif ($request->status == 'rejected') {
                                    $badgeClass = 'danger';
                                    $statusText = 'Rejected';
                                } elseif ($request->status == 'security_approved') {
                                    $badgeClass = 'info';
                                    $statusText = 'Approved - Awaiting Driver';
                                } elseif ($request->status == 'completed') {
                                    $badgeClass = 'success';
                                    $statusText = 'Completed';
                                } elseif ($request->status == 'cancelled') {
                                    $badgeClass = 'secondary';
                                    $statusText = 'Cancelled';
                                } elseif ($request->status == 'draft') {
                                    $badgeClass = 'secondary';
                                    $statusText = 'Draft';
                                } else {
                                    $badgeClass = 'primary';
                                    $statusText = ucfirst(str_replace('_', ' ', $request->status));
                                }
                                ?>
                                <span class="badge bg-<?= $badgeClass; ?>"><?= $statusText; ?></span>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($request->created_at)); ?></td>
                            <td>
                                <a href="<?= URL . $ctrl; ?>/view/<?= $request->id; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if (empty($request->is_passenger)): ?>
                                    <?php if ($request->status == 'draft'): ?>
                                        <a href="<?= URL . $ctrl; ?>/edit/<?= $request->id; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?= URL . $ctrl; ?>/delete/<?= $request->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this draft?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php elseif ($request->status == 'pending' && ($request->current_approval_level ?? '') == 'reviewer'): ?>
                                        <a href="<?= URL . $ctrl; ?>/edit/<?= $request->id; ?>" class="btn btn-sm btn-warning" title="Edit before supervisor approves">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($request->status == 'pending'): ?>
                                        <a href="<?= URL . $ctrl; ?>/webCancel/<?= $request->id; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Cancel this request?')">
                                            <i class="fas fa-ban"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No requests found. Click "New Intrastate Request" to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let requestsDataTable;

document.addEventListener("DOMContentLoaded", function () {
    const requestsTable = document.getElementById('requestsTable');
    if (requestsTable && requestsTable.querySelector('tbody tr td[colspan]') === null) {
        if (typeof simpleDatatables !== 'undefined') {
            requestsDataTable = new simpleDatatables.DataTable(requestsTable, {
                searchable: true,
                sortable: true,
                perPage: 10,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "Search requests...",
                    perPage: "",
                    noRows: "No requests found",
                    info: "Showing {start} to {end} of {rows} requests"
                }
            });
        }
    }
});
</script>