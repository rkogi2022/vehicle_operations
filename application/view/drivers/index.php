<div class="container-fluid px-4">
    <h3 class="mt-4">Driver Management</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Driver Management</li>
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

    <!-- Drivers Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users me-1"></i> Drivers List</span>
            <button class="btn btn-primary btn-sm" onclick="openDriverModal('add')">
                <i class="fas fa-plus"></i> Add Driver
            </button>
        </div>

        <div class="card-body table-responsive">
            <table id="driversTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($drivers)): ?>
                    <?php foreach ($drivers as $driver): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($driver->name); ?></strong>
                            </td>
                            <td><?= htmlspecialchars($driver->email ?? '—'); ?></td>
                            <td><?= htmlspecialchars($driver->phone); ?></td>
                            <td><?= date('M d, Y', strtotime($driver->created_at)); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="openDriverModal('edit', <?= $driver->id ?>, '<?= htmlspecialchars($driver->name, ENT_QUOTES) ?>', '<?= htmlspecialchars($driver->email ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($driver->phone, ENT_QUOTES) ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $driver->id ?>, '<?= htmlspecialchars($driver->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No drivers found. Click "Add Driver" to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
             </>
        </div>
    </div>
</div>

<!-- Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="driver-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="driverModalLabel">Add Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="driver_id">
                    <div class="mb-3">
                        <label for="driver_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="driver_name" class="form-control" required
                               placeholder="e.g., John Doe">
                    </div>
                    <div class="mb-3">
                        <label for="driver_email" class="form-label">Email</label>
                        <input type="email" name="email" id="driver_email" class="form-control"
                               placeholder="e.g., john.doe@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="driver_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" id="driver_phone" class="form-control" required
                               placeholder="e.g., +254712345678">
                        <small class="text-muted">Enter phone number with country code.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Driver</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete driver: <strong id="deleteItemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This action cannot be undone. All data related to this driver will be removed.
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
let driversDataTable;
let driverModal, deleteModal;

document.addEventListener("DOMContentLoaded", function () {
    // Initialize Bootstrap modals
    driverModal = new bootstrap.Modal(document.getElementById('driverModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Initialize Drivers DataTable
    const driversTable = document.getElementById('driversTable');
    if (driversTable && driversTable.querySelector('tbody tr td[colspan]') === null) {
        if (typeof simpleDatatables !== 'undefined') {
            driversDataTable = new simpleDatatables.DataTable(driversTable, {
                searchable: true,
                sortable: true,
                perPage: 10,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "Search drivers...",
                    perPage: "",
                    noRows: "No drivers found",
                    info: "Showing {start} to {end} of {rows} drivers"
                }
            });
        }
    }
    
    // Form validation for driver
    const driverForm = document.getElementById('driver-form');
    if (driverForm) {
        driverForm.addEventListener('submit', function(e) {
            const name  = document.getElementById('driver_name').value.trim();
            const email = document.getElementById('driver_email').value.trim();
            const phone = document.getElementById('driver_phone').value.trim();
            const id    = document.getElementById('driver_id').value;

            if (name === '') {
                e.preventDefault();
                alert('Full name is required');
                return false;
            }

            if (email !== '' && !validateEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }

            if (phone === '') {
                e.preventDefault();
                alert('Phone number is required');
                return false;
            }
            
            // Set correct action URL based on whether we have an ID
            if (id) {
                driverForm.action = '<?= URL; ?>drivers/edit/' + id;
            } else {
                driverForm.action = '<?= URL; ?>drivers/create';
            }
        });
    }
});

// Email validation function
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Driver Functions
function openDriverModal(action, id = null, name = '', email = '', phone = '') {
    const modalTitle = document.getElementById('driverModalLabel');
    const driverId    = document.getElementById('driver_id');
    const driverName  = document.getElementById('driver_name');
    const driverEmail = document.getElementById('driver_email');
    const driverPhone = document.getElementById('driver_phone');

    if (action === 'add') {
        modalTitle.textContent = 'Add Driver';
        driverId.value    = '';
        driverName.value  = '';
        driverEmail.value = '';
        driverPhone.value = '';
    } else {
        modalTitle.textContent = 'Edit Driver';
        driverId.value    = id;
        driverName.value  = name;
        driverEmail.value = email;
        driverPhone.value = phone;
    }

    driverModal.show();
}

function confirmDelete(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?= URL; ?>drivers/delete/' + id;
    deleteModal.show();
}

// Optional: Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + N to open add modal
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        openDriverModal('add');
    }
});
</script>