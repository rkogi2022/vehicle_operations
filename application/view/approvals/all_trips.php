<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold" style="color:#1a2c3e;">
        <i class="fas fa-list-alt me-2" style="color:#1a7f4b;"></i> All Trips
    </h3>

    <ol class="breadcrumb mb-4 bg-transparent p-0">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">All Trips</li>
    </ol>

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
    $allTrips = isset($allTrips) ? $allTrips : [];

    // Count by status
    $counts = ['total' => count($allTrips), 'pending' => 0, 'security_approved' => 0, 'completed' => 0, 'rejected' => 0, 'cancelled' => 0, 'draft' => 0];
    foreach ($allTrips as $t) {
        if (isset($counts[$t->status])) $counts[$t->status]++;
    }
    ?>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card border-0 rounded-4 text-center py-3" style="background:#f0f4ff;">
                <div class="fw-bold fs-4"><?= $counts['total'] ?></div>
                <div class="small text-muted">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 rounded-4 text-center py-3" style="background:#fff8e1;">
                <div class="fw-bold fs-4 text-warning"><?= $counts['pending'] ?></div>
                <div class="small text-muted">Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 rounded-4 text-center py-3" style="background:#e8f5e9;">
                <div class="fw-bold fs-4 text-success"><?= $counts['security_approved'] ?></div>
                <div class="small text-muted">Approved</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 rounded-4 text-center py-3" style="background:#e3f2fd;">
                <div class="fw-bold fs-4 text-primary"><?= $counts['completed'] ?></div>
                <div class="small text-muted">Completed</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 rounded-4 text-center py-3" style="background:#fce4ec;">
                <div class="fw-bold fs-4 text-danger"><?= $counts['rejected'] ?></div>
                <div class="small text-muted">Rejected</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 rounded-4 text-center py-3" style="background:#f5f5f5;">
                <div class="fw-bold fs-4 text-secondary"><?= $counts['draft'] + $counts['cancelled'] ?></div>
                <div class="small text-muted">Draft / Cancelled</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body py-2 px-3 d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-semibold small text-muted me-1">Filter:</span>
            <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn active" data-filter="all">All</button>
            <button class="btn btn-sm btn-outline-success rounded-pill filter-btn" data-filter="intrastate">Intrastate</button>
            <button class="btn btn-sm btn-outline-primary rounded-pill filter-btn" data-filter="interstate">Interstate</button>
            <div class="vr mx-1"></div>
            <button class="btn btn-sm btn-outline-warning rounded-pill filter-btn" data-filter="pending">Pending</button>
            <button class="btn btn-sm btn-outline-success rounded-pill filter-btn" data-filter="security_approved">Approved</button>
            <button class="btn btn-sm btn-outline-primary rounded-pill filter-btn" data-filter="completed">Completed</button>
            <button class="btn btn-sm btn-outline-danger rounded-pill filter-btn" data-filter="rejected">Rejected</button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn" data-filter="draft">Draft</button>
            <div class="ms-auto">
                <input type="text" id="tripSearch" class="form-control form-control-sm rounded-pill" placeholder="Search..." style="width:180px;">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="allTripsTable">
                    <thead style="background:#f8f9fc;">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Type</th>
                            <th>Requester</th>
                            <th>From</th>
                            <th>To / Destination</th>
                            <th>Trip Date</th>
                            <th>Return Date</th>
                            <th>Funder</th>
                            <th>Status</th>
                            <th>Driver</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($allTrips)): ?>
                        <tr><td colspan="12" class="text-center py-5 text-muted">No trips found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allTrips as $trip): ?>
                        <?php
                            $type   = $trip->request_type;
                            $ctrl   = $type;
                            $status = $trip->status;

                            if ($status === 'pending') {
                                $badgeClass  = 'warning';
                                $statusLabel = 'Pending - ' . ucfirst(str_replace('_', ' ', $trip->current_approval_level ?? ''));
                            } elseif ($status === 'security_approved' && $trip->assigned_driver_id) {
                                $badgeClass  = 'success';
                                $statusLabel = 'Driver Assigned';
                            } elseif ($status === 'security_approved') {
                                $badgeClass  = 'info';
                                $statusLabel = 'Approved - Awaiting Driver';
                            } elseif ($status === 'completed') {
                                $badgeClass  = 'primary';
                                $statusLabel = 'Completed';
                            } elseif ($status === 'rejected') {
                                $badgeClass  = 'danger';
                                $statusLabel = 'Rejected';
                            } elseif ($status === 'cancelled') {
                                $badgeClass  = 'secondary';
                                $statusLabel = 'Cancelled';
                            } elseif ($status === 'draft') {
                                $badgeClass  = 'secondary';
                                $statusLabel = 'Draft';
                            } else {
                                $badgeClass  = 'secondary';
                                $statusLabel = ucfirst(str_replace('_', ' ', $status));
                            }

                            $destination = $type === 'interstate'
                                ? htmlspecialchars(($trip->arrival_state_name ?? '') . ' — ' . ($trip->destination_city ?? ''))
                                : htmlspecialchars($trip->trip_destination ?? '');

                            $fromLocation = htmlspecialchars($trip->vehicle_location_state_name ?? '—');
                        ?>
                        <tr data-type="<?= $type ?>" data-status="<?= $status ?>">
                            <td class="ps-3 fw-bold"><?= $trip->id ?></td>
                            <td>
                                <?php if ($type === 'interstate'): ?>
                                    <span class="badge bg-success">Interstate</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Intrastate</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold small"><?= htmlspecialchars($trip->staff_name ?? explode('@', $trip->staff_email)[0]) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($trip->staff_email) ?></div>
                            </td>
                            <td class="small"><?= $fromLocation ?></td>
                            <td class="small"><?= $destination ?></td>
                            <td class="small"><?= date('M d, Y', strtotime($trip->trip_date)) ?></td>
                            <td class="small"><?= date('M d, Y', strtotime($trip->return_date)) ?></td>
                            <td class="small"><?= htmlspecialchars($trip->funder_code_name ?? '—') ?></td>
                            <td><span class="badge bg-<?= $badgeClass ?>" style="font-size:0.7rem;"><?= $statusLabel ?></span></td>
                            <td class="small">
                                <?php if ($trip->driver_email ?? null): ?>
                                    <div><?= htmlspecialchars($trip->driver_name ?? explode('@', $trip->driver_email)[0]) ?></div>
                                    <div class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars($trip->driver_phone ?? '') ?></div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= date('M d, Y', strtotime($trip->created_at)) ?></td>
                            <td>
                                <a href="<?= URL . $ctrl ?>/view/<?= $trip->id ?>" class="btn btn-sm btn-outline-secondary rounded-3">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        applyFilters();
    });
});

document.getElementById('tripSearch').addEventListener('input', applyFilters);

function applyFilters() {
    const activeFilter = document.querySelector('.filter-btn.active')?.dataset.filter ?? 'all';
    const search = document.getElementById('tripSearch').value.toLowerCase();

    document.querySelectorAll('#allTripsTable tbody tr[data-type]').forEach(row => {
        const type   = row.dataset.type;
        const status = row.dataset.status;
        const text   = row.textContent.toLowerCase();

        const matchFilter = activeFilter === 'all'
            || activeFilter === type
            || activeFilter === status
            || (activeFilter === 'security_approved' && status === 'security_approved');

        const matchSearch = !search || text.includes(search);

        row.style.display = (matchFilter && matchSearch) ? '' : 'none';
    });
}
</script>
