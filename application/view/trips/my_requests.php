<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Request Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Simple DataTables CSS -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet">
    
    <style>
        .badge {
            font-size: 0.85rem;
            padding: 5px 10px;
        }

        .btn-sm {
            margin: 2px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .dataTable-table {
            font-size: 14px;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .alert {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .btn-sm {
                font-size: 11px;
                padding: 4px 6px;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
            
            .card-header button {
                margin-top: 10px;
                width: 100%;
            }
            
            .table-responsive {
                font-size: 12px;
            }
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        
        .status-approved {
            background-color: #28a745;
            color: #fff;
        }
        
        .status-declined {
            background-color: #dc3545;
            color: #fff;
        }
        
        .status-cancelled {
            background-color: #6c757d;
            color: #fff;
        }
        
        .trip-row:hover {
            background-color: #f8f9fa;
        }
        
        .details-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Trip Request Management</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item ">Trip Requests</li>
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

    <!-- Trip Requests Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-car me-1"></i> My Trip Requests</span>
            <a href="<?= URL ?>trip/create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Request
            </a>
        </div>

        <div class="card-body table-responsive">
            <table id="tripsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Destination</th>
                        <th>Trip Type</th>
                        <th>Departure Date</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($trips)): ?>
                    <?php foreach ($trips as $trip): ?>
                        <tr class="trip-row">
                            <td><strong><?= htmlspecialchars($trip->trip_destination); ?></strong></td>
                            <td>
                                <span class="badge bg-<?= $trip->trip_type == 'local' ? 'info' : 'primary'; ?>">
                                    <?= ucfirst($trip->trip_type); ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($trip->departure_date)); ?> at <?= $trip->departure_time; ?></td>
                            <td><?= substr(htmlspecialchars($trip->purpose), 0, 50); ?>...</td>
                            <td>
                                <span class="badge status-<?= $trip->status; ?>">
                                    <?= ucfirst($trip->status); ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($trip->created_at)); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="viewRequest(<?= $trip->id; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if($trip->status == 'pending'): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="cancelRequest(<?= $trip->id; ?>)">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No trip requests found. <a href="<?= URL ?>trip/create">Click here</a> to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- View Request Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Trip Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Cancel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this trip request?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Yes, Cancel Request</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Go Back</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

<script>
let tripsDataTable;
let viewModal, deleteModal;

// Helper function to get name from email
function getNameFromEmail(email) {
    if (!email) return 'N/A';
    return email.split('@')[0];
}

document.addEventListener("DOMContentLoaded", function () {
    viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    const tripsTable = document.getElementById('tripsTable');
    if (tripsTable && tripsTable.querySelector('tbody tr td[colspan]') === null) {
        tripsDataTable = new simpleDatatables.DataTable(tripsTable, {
            searchable: true,
            sortable: true,
            perPage: 10,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Search trips...",
                perPage: "{select} trips per page",
                noRows: "No trips found",
                info: "Showing {start} to {end} of {rows} trips"
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
                const statusClass = `status-${trip.status}`;
                const statusBadge = `<span class="badge ${statusClass}">${trip.status.toUpperCase()}</span>`;
                
                // Get names from emails
                const requesterName = getNameFromEmail(trip.requester_email);
                const supervisorName = getNameFromEmail(trip.supervisor_email);
                const reviewerName = getNameFromEmail(trip.reviewer_email);
                const coReviewerName = getNameFromEmail(trip.co_reviewer_email);
                const managerName = getNameFromEmail(trip.manager_email);
                
                document.getElementById('viewModalBody').innerHTML = `
                    <div class="details-card">
                        <h6>Staff Information</h6>
                        <p><strong>Requester:</strong> ${escapeHtml(requesterName)} (${escapeHtml(trip.requester_email)})</p>
                        <p><strong>Department:</strong> ${escapeHtml(trip.department_name || 'N/A')}</p>
                        <p><strong>Supervisor:</strong> ${escapeHtml(supervisorName || 'N/A')}</p>
                    </div>
                    
                    <div class="details-card">
                        <h6>Trip Details</h6>
                        <p><strong>Destination:</strong> ${escapeHtml(trip.trip_destination)}</p>
                        <p><strong>Trip Type:</strong> ${trip.trip_type}</p>
                        <p><strong>Purpose:</strong> ${escapeHtml(trip.purpose)}</p>
                        <p><strong>Departure:</strong> ${new Date(trip.departure_date).toLocaleDateString()} at ${trip.departure_time}</p>
                        <p><strong>Return Date:</strong> ${trip.return_date ? new Date(trip.return_date).toLocaleDateString() : 'Not specified'}</p>
                        ${trip.departure_location_name ? `<p><strong>Departure State:</strong> ${escapeHtml(trip.departure_location_name)}</p>` : ''}
                        ${trip.destination_location_name ? `<p><strong>Destination State:</strong> ${escapeHtml(trip.destination_location_name)}</p>` : ''}
                        <p><strong>Need Driver:</strong> ${trip.need_driver ? 'Yes' : 'No'}</p>
                        <p><strong>Driver Overtime:</strong> ${trip.driver_overtime ? 'Yes' : 'No'}</p>
                    </div>
                    
                    <div class="details-card">
                        <h6>Approval Flow</h6>
                        <p><strong>Reviewer:</strong> ${escapeHtml(reviewerName || 'N/A')}</p>
                        <p><strong>Co-Reviewer:</strong> ${escapeHtml(coReviewerName || 'N/A')}</p>
                        <p><strong>Manager:</strong> ${escapeHtml(managerName || 'N/A')}</p>
                        <p><strong>Status:</strong> ${statusBadge}</p>
                        ${trip.comments ? `<p><strong>Comments:</strong> ${escapeHtml(trip.comments)}</p>` : ''}
                    </div>
                `;
                viewModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('viewModalBody').innerHTML = '<div class="alert alert-danger">Error loading trip details</div>';
        });
}

function cancelRequest(id) {
    document.getElementById('confirmDeleteBtn').href = '<?= URL ?>trip/cancelRequest/' + id;
    deleteModal.show();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>