<div class="container-fluid px-4">
    <h3 class="mt-4">Funder Code Management</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item ">Funder Code Management</li>
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

    <!-- Funder Codes Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-code-branch me-1"></i> Funder Codes List</span>
            <button class="btn btn-primary btn-sm" onclick="openFunderModal('add')">
                <i class="fas fa-plus"></i> Add Funder Code
            </button>
        </div>

        <div class="card-body table-responsive">
            <table id="funderCodesTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Funder Code Name</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($funderCodes)): ?>
                    <?php foreach ($funderCodes as $funderCode): ?>
                        <tr class="funder-row">
                            <td>
                                <strong class="funder-code"><?= htmlspecialchars($funderCode->name); ?></strong>
                            </td>
                            <td><?= date('M d, Y', strtotime($funderCode->created_at)); ?></td>
                            <td><?= date('M d, Y', strtotime($funderCode->updated_at)); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="openFunderModal('edit', <?= $funderCode->id ?>, '<?= htmlspecialchars($funderCode->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $funderCode->id ?>, '<?= htmlspecialchars($funderCode->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No funder codes found. Click "Add Funder Code" to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Funder Code Modal -->
<div class="modal fade" id="funderModal" tabindex="-1" aria-labelledby="funderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="funder-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="funderModalLabel">Add Funder Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="funder_id">
                    <div class="mb-3">
                        <label for="funder_name" class="form-label">Funder Code Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="funder_name" class="form-control" required 
                               placeholder="e.g., 293.02--KCE_Safe_Water_Flex_2024_OPTZ">
                        <small class="text-muted">Enter a unique funder code name (max 100 characters). Allowed: letters, numbers, spaces, and special characters (., -, _)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Example Format</label>
                        <div>
                            <span class="badge bg-secondary">NSF</span>
                            <span class="badge bg-secondary">NIH</span>
                            <span class="badge bg-secondary">293.02--KCE_Safe_Water_Flex_2024_OPTZ</span>
                            <span class="badge bg-secondary">DOD-2024-001</span>
                            <span class="badge bg-secondary">USAID_Project_2025</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Funder Code</button>
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
                <p>Are you sure you want to delete funder code: <strong id="deleteItemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This action cannot be undone. All data related to this funder code will be removed.
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
let funderCodesDataTable;
let funderModal, deleteModal;

document.addEventListener("DOMContentLoaded", function () {
    // Initialize Bootstrap modals
    funderModal = new bootstrap.Modal(document.getElementById('funderModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Initialize Funder Codes DataTable
    const funderCodesTable = document.getElementById('funderCodesTable');
    if (funderCodesTable && funderCodesTable.querySelector('tbody tr td[colspan]') === null) {
        if (typeof simpleDatatables !== 'undefined') {
            funderCodesDataTable = new simpleDatatables.DataTable(funderCodesTable, {
                searchable: true,
                sortable: true,
                perPage: 10,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "Search funder codes...",
                    perPage: "",
                    noRows: "No funder codes found",
                    info: "Showing {start} to {end} of {rows} funder codes"
                }
            });
        }
    }
    
    // Form validation for funder code
    const funderForm = document.getElementById('funder-form');
    if (funderForm) {
        funderForm.addEventListener('submit', function(e) {
            const name = document.getElementById('funder_name').value.trim();
            const id = document.getElementById('funder_id').value;
            
            if (name === '') {
                e.preventDefault();
                alert('Funder code name is required');
                return false;
            }
            
            if (name.length > 100) {
                e.preventDefault();
                alert('Funder code name must not exceed 100 characters');
                return false;
            }
            
            // Updated validation pattern - allows letters, numbers, spaces, dots, hyphens, underscores
            // Also allows double hyphens (--) and other common punctuation
            const validPattern = /^[a-zA-Z0-9\s\-_\.]+$/;
            if (!validPattern.test(name)) {
                e.preventDefault();
                alert('Funder code can contain letters, numbers, spaces, dots (.), hyphens (-), and underscores (_) only');
                return false;
            }
            
            // Set correct action URL based on whether we have an ID
            if (id) {
                funderForm.action = '<?= URL; ?>funders/update/' + id;
            } else {
                funderForm.action = '<?= URL; ?>funders/create';
            }
        });
    }
});

// Funder Code Functions
function openFunderModal(action, id = null, name = '') {
    const modalTitle = document.getElementById('funderModalLabel');
    const funderId = document.getElementById('funder_id');
    const funderName = document.getElementById('funder_name');
    
    if (action === 'add') {
        modalTitle.textContent = 'Add Funder Code';
        funderId.value = '';
        funderName.value = '';
    } else {
        modalTitle.textContent = 'Edit Funder Code';
        funderId.value = id;
        funderName.value = name;
    }
    
    funderModal.show();
}

function confirmDelete(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?= URL; ?>funders/delete/' + id;
    deleteModal.show();
}

// Optional: Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + N to open add modal
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        openFunderModal('add');
    }
});
</script>