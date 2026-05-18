<div class="container-fluid px-4">
    <h3 class="mt-4">Operations Dashboard</h3>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item ">Operations Dashboard</li>
    </ol>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Total Requests</h4>
                    <h2><?= $stats['total'] ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= URL ?>trip/allRequests">View All</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4>Pending</h4>
                    <h2><?= $stats['pending'] ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= URL ?>trip/allRequests?status=pending">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4>Approved</h4>
                    <h2><?= $stats['approved'] ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= URL ?>trip/allRequests?status=approved">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h4>Declined</h4>
                    <h2><?= $stats['declined'] ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= URL ?>trip/allRequests?status=declined">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">
                    <h4>Cancelled</h4>
                    <h2><?= $stats['cancelled'] ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= URL ?>trip/allRequests?status=cancelled">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Requests Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Recent Trip Requests
        </div>
        <div class="card-body table-responsive">
            <table id="allTripsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Supervisor</th>
                        <th>Destination</th>
                        <th>Departure Date</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allRequests as $trip): ?>
                        <tr>
                            <td><?= htmlspecialchars($trip->id) ?></td>
                            <td><?= htmlspecialchars($trip->requester_email) ?></td>
                            <td><?= htmlspecialchars($trip->department_name ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($trip->supervisor_email ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($trip->trip_destination) ?></td>
                            <td><?= date('M d, Y', strtotime($trip->departure_date)) ?> at <?= $trip->departure_time ?></td>
                            <td>
                                <span class="badge bg-<?= $trip->status == 'approved' ? 'success' : ($trip->status == 'pending' ? 'warning' : ($trip->status == 'declined' ? 'danger' : 'secondary')) ?>">
                                    <?= ucfirst($trip->status) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($trip->created_at)) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="viewRequest(<?= $trip->id ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                             </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const dataTable = document.getElementById('allTripsTable');
    if (dataTable) {
        new simpleDatatables.DataTable(dataTable, {
            searchable: true,
            sortable: true,
            perPage: 25,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Search trips...",
                perPage: "{select} trips per page",
                noRows: "No trips found"
            }
        });
    }
});

function viewRequest(id) {
    fetch('<?= URL ?>trip/getTripDetails/' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const trip = data.data;
                const statusColor = trip.status == 'approved' ? 'success' : (trip.status == 'pending' ? 'warning' : 'danger');
                
                Swal.fire({
                    title: 'Trip Request Details',
                    html: `
                        <div style="text-align: left;">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Staff Information</h6>
                                    <p><strong>Requester:</strong> ${escapeHtml(trip.requester_email)}</p>
                                    <p><strong>Department:</strong> ${escapeHtml(trip.department_name || 'N/A')}</p>
                                    <p><strong>Supervisor:</strong> ${escapeHtml(trip.supervisor_email || 'N/A')}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Approval Flow</h6>
                                    <p><strong>Reviewer:</strong> ${escapeHtml(trip.reviewer_email || 'N/A')}</p>
                                    <p><strong>Co-Reviewer:</strong> ${escapeHtml(trip.co_reviewer_email || 'N/A')}</p>
                                    <p><strong>Manager:</strong> ${escapeHtml(trip.manager_email || 'N/A')}</p>
                                </div>
                            </div>
                            <hr>
                            <h6>Trip Details</h6>
                            <p><strong>Destination:</strong> ${escapeHtml(trip.trip_destination)}</p>
                            <p><strong>Purpose:</strong> ${escapeHtml(trip.purpose)}</p>
                            <p><strong>Departure:</strong> ${new Date(trip.departure_date).toLocaleDateString()} at ${trip.departure_time}</p>
                            <p><strong>Return Date:</strong> ${trip.return_date ? new Date(trip.return_date).toLocaleDateString() : 'Not specified'}</p>
                            <p><strong>Departure State:</strong> ${escapeHtml(trip.departure_location_name || 'N/A')}</p>
                            <p><strong>Destination State:</strong> ${escapeHtml(trip.destination_location_name || 'N/A')}</p>
                            <p><strong>Need Driver:</strong> ${trip.need_driver ? 'Yes' : 'No'}</p>
                            <p><strong>Driver Overtime:</strong> ${trip.driver_overtime ? 'Yes' : 'No'}</p>
                            <hr>
                            <p><strong>Status:</strong> <span class="badge bg-${statusColor}">${trip.status.toUpperCase()}</span></p>
                            ${trip.comments ? `<p><strong>Comments:</strong> ${escapeHtml(trip.comments)}</p>` : ''}
                        </div>
                    `,
                    width: '800px'
                });
            }
        });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>