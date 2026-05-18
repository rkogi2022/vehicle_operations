<div class="container-fluid px-4">
    <h3 class="mt-4">EA States Configuration</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">EA States Configuration</li>
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

    <!-- EA States Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-map-marked-alt me-1"></i> EA States Configuration List</span>
            <button class="btn btn-primary btn-sm" onclick="openEaStateModal('add')">
                <i class="fas fa-plus"></i> Add EA State 
            </button>
        </div>

        <div class="card-body table-responsive">
            <table id="eaStatesTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>State</th>
                        <th>Reviewer</th>
                        <th>Co-Reviewer</th>
                        <th>Manager</th>
                        <th>Security Manager</th>
                        <th>Assigned Driver</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($eaStates)): ?>
                    <?php foreach ($eaStates as $eaState): ?>
                        <tr>
                            <td><?= htmlspecialchars($eaState->country_name); ?></td>
                            <td><?= htmlspecialchars($eaState->state_name); ?> (<?= htmlspecialchars($eaState->state_code); ?>)</td>
                            <td>
                                <?= htmlspecialchars($eaState->reviewer_email); ?>
                                <br><small class="text-muted"><?= htmlspecialchars($eaState->reviewer_name); ?></small>
                            </td>
                            <td>
                                <?php if ($eaState->co_reviewer_email): ?>
                                    <?= htmlspecialchars($eaState->co_reviewer_email); ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($eaState->co_reviewer_name); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($eaState->manager_email); ?>
                                <br><small class="text-muted"><?= htmlspecialchars($eaState->manager_name); ?></small>
                            </td>
                            <td>
                                <?php if ($eaState->security_manager_email): ?>
                                    <?= htmlspecialchars($eaState->security_manager_email); ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($eaState->security_manager_name); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($eaState->driver_email): ?>
                                    <?= htmlspecialchars($eaState->driver_email); ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($eaState->driver_phone); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($eaState->created_at)); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="openEaStateModal('edit', <?= $eaState->id ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $eaState->id ?>, '<?= htmlspecialchars($eaState->state_name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No EA State configurations found. Click "Add EA State Configuration" to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- EA State Modal -->
<div class="modal fade" id="eaStateModal" tabindex="-1" aria-labelledby="eaStateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="ea-state-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="eaStateModalLabel">Add EA State Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="ea_state_id">
                    
                    <div class="mb-3">
                        <label for="state_id" class="form-label">State <span class="text-danger">*</span></label>
                        <select name="state_id" id="state_id" class="form-control" required>
                            <option value="">Select State</option>
                            <?php if (!empty($availableStates)): ?>
                                <?php foreach ($availableStates as $state): ?>
                                    <option value="<?= $state->id; ?>">
                                        <?= htmlspecialchars($state->country_name); ?> - <?= htmlspecialchars($state->name); ?> (<?= htmlspecialchars($state->code); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Select the state to configure reviewers, managers, and driver.</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="reviewer_email" class="form-label">Reviewer Email <span class="text-danger">*</span></label>
                            <select name="reviewer_email" id="reviewer_email" class="form-control" required>
                                <option value="">Select Reviewer</option>
                                <?php if (!empty($staffEmails)): ?>
                                    <?php foreach ($staffEmails as $staff): ?>
                                        <option value="<?= htmlspecialchars($staff->email); ?>">
                                            <?= htmlspecialchars($staff->email); ?> (<?= htmlspecialchars($staff->role); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="co_reviewer_email" class="form-label">Co-Reviewer Email</label>
                            <select name="co_reviewer_email" id="co_reviewer_email" class="form-control">
                                <option value="">Select Co-Reviewer (Optional)</option>
                                <?php if (!empty($staffEmails)): ?>
                                    <?php foreach ($staffEmails as $staff): ?>
                                        <option value="<?= htmlspecialchars($staff->email); ?>">
                                            <?= htmlspecialchars($staff->email); ?> (<?= htmlspecialchars($staff->role); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="manager_email" class="form-label">Manager Email <span class="text-danger">*</span></label>
                            <select name="manager_email" id="manager_email" class="form-control" required>
                                <option value="">Select Manager</option>
                                <?php if (!empty($staffEmails)): ?>
                                    <?php foreach ($staffEmails as $staff): ?>
                                        <option value="<?= htmlspecialchars($staff->email); ?>">
                                            <?= htmlspecialchars($staff->email); ?> (<?= htmlspecialchars($staff->role); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="security_manager_email" class="form-label">Security Manager Email</label>
                            <select name="security_manager_email" id="security_manager_email" class="form-control">
                                <option value="">Select Security Manager (Optional)</option>
                                <?php if (!empty($securityManagers)): ?>
                                    <?php foreach ($securityManagers as $staff): ?>
                                        <option value="<?= htmlspecialchars($staff->email); ?>">
                                            <?= htmlspecialchars($staff->email); ?> (<?= htmlspecialchars($staff->role); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Person who will approve for security clearance.</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="driver_id" class="form-label">Assigned Driver</label>
                            <select name="driver_id" id="driver_id" class="form-control">
                                <option value="">Select Driver (Optional)</option>
                                <?php if (!empty($drivers)): ?>
                                    <?php foreach ($drivers as $driver): ?>
                                        <option value="<?= $driver->id; ?>">
                                            <?= htmlspecialchars($driver->email); ?> (<?= htmlspecialchars($driver->name); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Optional: Assign a default driver for this state.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Configuration</button>
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
                <p>Are you sure you want to delete EA State configuration for: <strong id="deleteItemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This action cannot be undone. All configuration data for this state will be removed.
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
let eaStatesDataTable;
let eaStateModal, deleteModal;
let eaStatesData = <?php echo json_encode($eaStates); ?>;

document.addEventListener("DOMContentLoaded", function () {
    // Initialize Bootstrap modals
    eaStateModal = new bootstrap.Modal(document.getElementById('eaStateModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Initialize EA States DataTable
    const eaStatesTable = document.getElementById('eaStatesTable');
    if (eaStatesTable && eaStatesTable.querySelector('tbody tr td[colspan]') === null) {
        if (typeof simpleDatatables !== 'undefined') {
            eaStatesDataTable = new simpleDatatables.DataTable(eaStatesTable, {
                searchable: true,
                sortable: true,
                perPage: 10,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "Search EA states...",
                    perPage: "",
                    noRows: "No EA state configurations found",
                    info: "Showing {start} to {end} of {rows} configurations"
                }
            });
        }
    }
    
    // Form validation for EA state
    const eaStateForm = document.getElementById('ea-state-form');
    if (eaStateForm) {
        eaStateForm.addEventListener('submit', function(e) {
            const state_id = document.getElementById('state_id').value;
            const reviewer_email = document.getElementById('reviewer_email').value;
            const manager_email = document.getElementById('manager_email').value;
            const id = document.getElementById('ea_state_id').value;
            
            if (state_id === '') {
                e.preventDefault();
                alert('Please select a state');
                return false;
            }
            
            if (reviewer_email === '') {
                e.preventDefault();
                alert('Please select a reviewer');
                return false;
            }
            
            if (manager_email === '') {
                e.preventDefault();
                alert('Please select a manager');
                return false;
            }
            
            // Set correct action URL based on whether we have an ID
            if (id) {
                eaStateForm.action = '<?= URL; ?>eastates/edit/' + id;
            } else {
                eaStateForm.action = '<?= URL; ?>eastates/create';
            }
        });
    }
});

// EA State Functions
function openEaStateModal(action, id = null) {
    const modalTitle = document.getElementById('eaStateModalLabel');
    const eaStateId = document.getElementById('ea_state_id');
    const stateSelect = document.getElementById('state_id');
    const reviewerSelect = document.getElementById('reviewer_email');
    const coReviewerSelect = document.getElementById('co_reviewer_email');
    const managerSelect = document.getElementById('manager_email');
    const securityManagerSelect = document.getElementById('security_manager_email');
    const driverSelect = document.getElementById('driver_id');
    
    if (action === 'add') {
        modalTitle.textContent = 'Add EA State Configuration';
        eaStateId.value = '';
        
        // Reset and enable all options in state select
        for (let i = 0; i < stateSelect.options.length; i++) {
            stateSelect.options[i].style.display = '';
            stateSelect.options[i].disabled = false;
        }
        
        stateSelect.value = '';
        reviewerSelect.value = '';
        coReviewerSelect.value = '';
        managerSelect.value = '';
        securityManagerSelect.value = '';
        driverSelect.value = '';
    } else {
        modalTitle.textContent = 'Edit EA State Configuration';
        eaStateId.value = id;
        
        // Fetch EA state data via AJAX
        fetch('<?= URL; ?>eastates/getByStateAjax?state_id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // Enable and show all state options first
                    for (let i = 0; i < stateSelect.options.length; i++) {
                        stateSelect.options[i].style.display = '';
                        stateSelect.options[i].disabled = false;
                    }
                    stateSelect.value = data.state_id;
                    reviewerSelect.value = data.reviewer_email;
                    coReviewerSelect.value = data.co_reviewer_email || '';
                    managerSelect.value = data.manager_email;
                    securityManagerSelect.value = data.security_manager_email || '';
                    driverSelect.value = data.driver_id || '';
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    eaStateModal.show();
}

function confirmDelete(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?= URL; ?>eastates/delete/' + id;
    deleteModal.show();
}

// Optional: Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + N to open add modal
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        openEaStateModal('add');
    }
});
</script>