<div class="container-fluid px-4">
    <h3 class="mt-4">Airline Management</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= URL; ?>home#configurations">Configurations</a></li>
        <li class="breadcrumb-item">Airlines</li>
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

    <!-- Airlines Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-plane me-1"></i> Airlines List</span>
            <button class="btn btn-primary btn-sm" onclick="openAirlineModal('add')">
                <i class="fas fa-plus"></i> Add Airline
            </button>
        </div>

        <div class="card-body table-responsive">
            <table id="airlinesTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Airline Name</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($airlines)): ?>
                    <?php foreach ($airlines as $airline): ?>
                        <tr class="airline-row">
                            <td>
                                <strong><i class="fas fa-plane text-primary"></i> <?= htmlspecialchars($airline->name); ?></strong>
                            </td>
                            <td><?= date('M d, Y', strtotime($airline->created_at)); ?></td>
                            <td><?= date('M d, Y', strtotime($airline->updated_at)); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="openAirlineModal('edit', <?= $airline->id ?>, '<?= htmlspecialchars($airline->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $airline->id ?>, '<?= htmlspecialchars($airline->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No airlines found. Click "Add Airline" to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Airline Modal -->
<div class="modal fade" id="airlineModal" tabindex="-1" aria-labelledby="airlineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="airline-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="airlineModalLabel">Add Airline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="airline_id">
                    <div class="mb-3">
                        <label for="airline_name" class="form-label">Airline Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="airline_name" class="form-control" required 
                               placeholder="e.g., Kenya Airways, Ethiopian Airlines">
                        <small class="text-muted">Enter the full airline name (max 100 characters)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Example Airlines</label>
                        <div>
                            <span class="badge bg-secondary">Kenya Airways</span>
                            <span class="badge bg-secondary">Ethiopian Airlines</span>
                            <span class="badge bg-secondary">Emirates</span>
                            <span class="badge bg-secondary">Qatar Airways</span>
                            <span class="badge bg-secondary">RwandAir</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Airline</button>
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
                <p>Are you sure you want to delete airline: <strong id="deleteItemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This action cannot be undone. All data related to this airline will be removed.
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
let airlinesDataTable;
let airlineModal, deleteModal;

document.addEventListener("DOMContentLoaded", function () {
    // Initialize Bootstrap modals
    airlineModal = new bootstrap.Modal(document.getElementById('airlineModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Initialize Airlines DataTable
    const airlinesTable = document.getElementById('airlinesTable');
    if (airlinesTable && airlinesTable.querySelector('tbody tr td[colspan]') === null) {
        if (typeof simpleDatatables !== 'undefined') {
            airlinesDataTable = new simpleDatatables.DataTable(airlinesTable, {
                searchable: true,
                sortable: true,
                perPage: 10,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "Search airlines...",
                    perPage: "",
                    noRows: "No airlines found",
                    info: "Showing {start} to {end} of {rows} airlines"
                }
            });
        }
    }
    
    // Form validation for airline
    const airlineForm = document.getElementById('airline-form');
    if (airlineForm) {
        airlineForm.addEventListener('submit', function(e) {
            const name = document.getElementById('airline_name').value.trim();
            const id = document.getElementById('airline_id').value;
            
            if (name === '') {
                e.preventDefault();
                alert('Airline name is required');
                return false;
            }
            
            if (name.length > 100) {
                e.preventDefault();
                alert('Airline name must not exceed 100 characters');
                return false;
            }
            
            // Set correct action URL based on whether we have an ID
            if (id) {
                airlineForm.action = '<?= URL; ?>airline/update/' + id;
            } else {
                airlineForm.action = '<?= URL; ?>airline/create';
            }
        });
    }
});

// Airline Functions
function openAirlineModal(action, id = null, name = '') {
    const modalTitle = document.getElementById('airlineModalLabel');
    const airlineId = document.getElementById('airline_id');
    const airlineName = document.getElementById('airline_name');
    
    if (action === 'add') {
        modalTitle.textContent = 'Add Airline';
        airlineId.value = '';
        airlineName.value = '';
    } else {
        modalTitle.textContent = 'Edit Airline';
        airlineId.value = id;
        airlineName.value = name;
    }
    
    airlineModal.show();
}

function confirmDelete(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?= URL; ?>airline/delete/' + id;
    deleteModal.show();
}

// Optional: Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + N to open add modal
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        openAirlineModal('add');
    }
});
</script>