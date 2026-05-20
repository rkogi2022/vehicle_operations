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
                        <th>Overtime Manager</th>
                        <th>Assigned Drivers</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($eaStates)): ?>
                    <?php foreach ($eaStates as $eaState): ?>
                        <?php
                            $driverNames  = $eaState->driver_names  ? explode('||', $eaState->driver_names)  : [];
                            $driverPhones = $eaState->driver_phones ? explode('||', $eaState->driver_phones) : [];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($eaState->country_name) ?></td>
                            <td><?= htmlspecialchars($eaState->state_name) ?> (<?= htmlspecialchars($eaState->state_code) ?>)</td>
                            <td>
                                <?= htmlspecialchars($eaState->reviewer_email) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($eaState->reviewer_name) ?></small>
                            </td>
                            <td>
                                <?php if ($eaState->co_reviewer_email): ?>
                                    <?= htmlspecialchars($eaState->co_reviewer_email) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($eaState->co_reviewer_name) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($eaState->manager_email) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($eaState->manager_name) ?></small>
                            </td>
                            <td>
                                <?php if ($eaState->security_manager_email): ?>
                                    <?= htmlspecialchars($eaState->security_manager_email) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($eaState->security_manager_name) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($eaState->overtime_manager_email ?? null): ?>
                                    <?= htmlspecialchars($eaState->overtime_manager_email) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($eaState->overtime_manager_name ?? '') ?></small>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($driverNames)): ?>
                                    <?php foreach ($driverNames as $i => $dname): ?>
                                        <div>
                                            <i class="fas fa-user-tie text-secondary me-1" style="font-size:0.8rem;"></i>
                                            <?= htmlspecialchars($dname) ?>
                                            <?php if (!empty($driverPhones[$i])): ?>
                                                <small class="text-muted">(<?= htmlspecialchars($driverPhones[$i]) ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($eaState->created_at)) ?></td>
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
                        <td colspan="10" class="text-center">No EA State configurations found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Country Default Approvers Card -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-globe me-1"></i> Country Default Approvers <small class="text-muted">(used when departure state has no EA State config)</small></span>
        <button class="btn btn-primary btn-sm" onclick="openCountryDefaultModal()">
            <i class="fas fa-plus"></i> Add / Edit Country Default
        </button>
    </div>
    <div class="card-body table-responsive">
        <table id="countryDefaultsTable" class="table table-striped">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Reviewer</th>
                    <th>Co-Reviewer</th>
                    <th>Manager</th>
                    <th>Security Manager</th>
                    <th>Overtime Manager</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($countryDefaults)): ?>
                <?php foreach ($countryDefaults as $cd): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cd->country_name) ?></strong></td>
                        <td>
                            <?= htmlspecialchars($cd->reviewer_email) ?>
                            <br><small class="text-muted"><?= htmlspecialchars($cd->reviewer_name) ?></small>
                        </td>
                        <td>
                            <?php if ($cd->co_reviewer_email): ?>
                                <?= htmlspecialchars($cd->co_reviewer_email) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($cd->co_reviewer_name) ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($cd->manager_email) ?>
                            <br><small class="text-muted"><?= htmlspecialchars($cd->manager_name) ?></small>
                        </td>
                        <td>
                            <?php if ($cd->security_manager_email): ?>
                                <?= htmlspecialchars($cd->security_manager_email) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($cd->security_manager_name) ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($cd->overtime_manager_email): ?>
                                <?= htmlspecialchars($cd->overtime_manager_email) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($cd->overtime_manager_name) ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="openCountryDefaultModal(<?= $cd->country_id ?>, '<?= htmlspecialchars($cd->reviewer_email, ENT_QUOTES) ?>', '<?= htmlspecialchars($cd->co_reviewer_email ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($cd->manager_email, ENT_QUOTES) ?>', '<?= htmlspecialchars($cd->security_manager_email ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($cd->overtime_manager_email ?? '', ENT_QUOTES) ?>')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteCountryDefault(<?= $cd->country_id ?>, '<?= htmlspecialchars($cd->country_name, ENT_QUOTES) ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No country default approvers configured.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- EA State Modal -->
<div class="modal fade" id="eaStateModal" tabindex="-1" aria-labelledby="eaStateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="ea-state-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="eaStateModalLabel">Add EA State Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="ea_state_id">

                    <div class="mb-3">
                        <label for="state_id" class="form-label">State <span class="text-danger">*</span></label>
                        <select name="state_id" id="state_id" class="form-control" required>
                            <option value="">Select State</option>
                            <?php foreach ($allStates as $state): ?>
                                <option value="<?= $state->id ?>"
                                    data-configured="<?= $state->already_configured ? '1' : '0' ?>">
                                    <?= htmlspecialchars($state->country_name) ?> - <?= htmlspecialchars($state->name) ?> (<?= htmlspecialchars($state->code) ?>)
                                    <?= $state->already_configured ? ' ✓' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="reviewer_email" class="form-label">Reviewer Email <span class="text-danger">*</span></label>
                            <select name="reviewer_email" id="reviewer_email" class="form-control" required>
                                <option value="">Select Reviewer</option>
                                <?php foreach ($staffEmails as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="co_reviewer_email" class="form-label">Co-Reviewer Email</label>
                            <select name="co_reviewer_email" id="co_reviewer_email" class="form-control">
                                <option value="">Select Co-Reviewer (Optional)</option>
                                <?php foreach ($staffEmails as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="manager_email" class="form-label">Manager Email <span class="text-danger">*</span></label>
                            <select name="manager_email" id="manager_email" class="form-control" required>
                                <option value="">Select Manager</option>
                                <?php foreach ($staffEmails as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="security_manager_email" class="form-label">Security Manager Email</label>
                            <select name="security_manager_email" id="security_manager_email" class="form-control">
                                <option value="">Select Security Manager (Optional)</option>
                                <?php foreach ($securityManagers as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="overtime_manager_email" class="form-label">Overtime Manager Email</label>
                            <select name="overtime_manager_email" id="overtime_manager_email" class="form-control">
                                <option value="">Select Overtime Manager (Optional)</option>
                                <?php foreach ($staffEmails as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Approves trip requests where driver overtime is required.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="driver_ids" class="form-label">Assigned Drivers</label>
                        <select name="driver_ids[]" id="driver_ids" class="form-control" multiple size="5">
                            <?php foreach ($drivers as $driver): ?>
                                <option value="<?= $driver->id ?>">
                                    <?= htmlspecialchars($driver->name) ?><?= $driver->phone ? ' — ' . htmlspecialchars($driver->phone) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold <kbd>Ctrl</kbd> (or <kbd>Cmd</kbd> on Mac) to select multiple drivers. Leave blank if none.</small>
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

<!-- Country Default Approvers Modal -->
<div class="modal fade" id="countryDefaultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="country-default-form" method="POST" action="<?= URL ?>eastates/saveCountryDefault">
                <div class="modal-header">
                    <h5 class="modal-title">Country Default Approvers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">These approvers are used for interstate trips departing from states that have no specific EA State configuration.</p>
                    <input type="hidden" id="cd_country_id_hidden">

                    <div class="mb-3">
                        <label for="cd_country_id" class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="country_id" id="cd_country_id" class="form-control" required>
                            <option value="">Select Country</option>
                            <?php foreach ($allCountries as $country): ?>
                                <option value="<?= $country->id ?>">
                                    <?= htmlspecialchars($country->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cd_reviewer_email" class="form-label">Reviewer Email <span class="text-danger">*</span></label>
                            <select name="cd_reviewer_email" id="cd_reviewer_email" class="form-control" required>
                                <option value="">Select Reviewer</option>
                                <?php foreach ($staffEmails as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cd_co_reviewer_email" class="form-label">Co-Reviewer Email</label>
                            <select name="cd_co_reviewer_email" id="cd_co_reviewer_email" class="form-control">
                                <option value="">Select Co-Reviewer (Optional)</option>
                                <?php foreach ($staffEmails as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cd_manager_email" class="form-label">Manager Email <span class="text-danger">*</span></label>
                            <select name="cd_manager_email" id="cd_manager_email" class="form-control" required>
                                <option value="">Select Manager</option>
                                <?php foreach ($staffEmails as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cd_security_manager_email" class="form-label">Security Manager Email</label>
                            <select name="cd_security_manager_email" id="cd_security_manager_email" class="form-control">
                                <option value="">Select Security Manager (Optional)</option>
                                <?php foreach ($securityManagers as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cd_overtime_manager_email" class="form-label">Overtime Manager Email</label>
                            <select name="cd_overtime_manager_email" id="cd_overtime_manager_email" class="form-control">
                                <option value="">Select Overtime Manager (Optional)</option>
                                <?php foreach ($staffEmails as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff->email) ?>">
                                        <?= htmlspecialchars($staff->email) ?> (<?= htmlspecialchars($staff->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Country Default Confirmation Modal -->
<div class="modal fade" id="deleteCountryDefaultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Remove default approvers for: <strong id="deleteCountryDefaultName"></strong>?</p>
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</div>
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmDeleteCountryDefaultBtn" class="btn btn-danger">Delete</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete EA State configuration for: <strong id="deleteItemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> This action cannot be undone.
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
let eaStateModal, deleteModal;

document.addEventListener("DOMContentLoaded", function () {
    eaStateModal = new bootstrap.Modal(document.getElementById('eaStateModal'));
    deleteModal  = new bootstrap.Modal(document.getElementById('deleteModal'));

    const eaStateForm = document.getElementById('ea-state-form');
    if (eaStateForm) {
        eaStateForm.addEventListener('submit', function(e) {
            const state_id       = document.getElementById('state_id').value;
            const reviewer_email = document.getElementById('reviewer_email').value;
            const manager_email  = document.getElementById('manager_email').value;
            const id             = document.getElementById('ea_state_id').value;

            if (!state_id)       { e.preventDefault(); alert('Please select a state'); return false; }
            if (!reviewer_email) { e.preventDefault(); alert('Please select a reviewer'); return false; }
            if (!manager_email)  { e.preventDefault(); alert('Please select a manager'); return false; }

            eaStateForm.action = id
                ? '<?= URL ?>eastates/edit/' + id
                : '<?= URL ?>eastates/create';
        });
    }
});

function openEaStateModal(action, id = null) {
    const modalTitle          = document.getElementById('eaStateModalLabel');
    const eaStateId           = document.getElementById('ea_state_id');
    const stateSelect         = document.getElementById('state_id');
    const reviewerSelect      = document.getElementById('reviewer_email');
    const coReviewerSelect    = document.getElementById('co_reviewer_email');
    const managerSelect       = document.getElementById('manager_email');
    const securitySelect      = document.getElementById('security_manager_email');
    const overtimeSelect      = document.getElementById('overtime_manager_email');
    const driverSelect        = document.getElementById('driver_ids');

    // Deselect all drivers first
    Array.from(driverSelect.options).forEach(o => o.selected = false);

    if (action === 'add') {
        modalTitle.textContent = 'Add EA State Configuration';
        eaStateId.value        = '';
        stateSelect.value      = '';
        reviewerSelect.value   = '';
        coReviewerSelect.value = '';
        managerSelect.value    = '';
        securitySelect.value   = '';
        overtimeSelect.value   = '';
        // Disable states that already have a config
        Array.from(stateSelect.options).forEach(opt => {
            opt.disabled = opt.dataset.configured === '1';
        });
    } else {
        modalTitle.textContent = 'Edit EA State Configuration';
        eaStateId.value        = id;
        // Enable all states so the current one can be shown
        Array.from(stateSelect.options).forEach(opt => { opt.disabled = false; });

        fetch('<?= URL ?>eastates/getByStateAjax?ea_state_id=' + id)
            .then(r => r.json())
            .then(data => {
                if (!data) return;
                stateSelect.value      = data.state_id               || '';
                reviewerSelect.value   = data.reviewer_email         || '';
                coReviewerSelect.value = data.co_reviewer_email      || '';
                managerSelect.value    = data.manager_email          || '';
                securitySelect.value   = data.security_manager_email || '';
                overtimeSelect.value   = data.overtime_manager_email || '';

                // Pre-select assigned drivers
                const selectedIds = data.driver_ids_csv
                    ? data.driver_ids_csv.split(',').map(Number)
                    : [];
                Array.from(driverSelect.options).forEach(opt => {
                    opt.selected = selectedIds.includes(parseInt(opt.value));
                });
            })
            .catch(err => console.error('Error loading EA state:', err));
    }

    eaStateModal.show();
}

function confirmDelete(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?= URL ?>eastates/delete/' + id;
    deleteModal.show();
}

let countryDefaultModal, deleteCountryDefaultModal;

document.addEventListener('DOMContentLoaded', function () {
    countryDefaultModal       = new bootstrap.Modal(document.getElementById('countryDefaultModal'));
    deleteCountryDefaultModal = new bootstrap.Modal(document.getElementById('deleteCountryDefaultModal'));
});

function openCountryDefaultModal(countryId = null, reviewer = '', coReviewer = '', manager = '', security = '', overtime = '') {
    document.getElementById('cd_country_id').value              = countryId || '';
    document.getElementById('cd_reviewer_email').value          = reviewer;
    document.getElementById('cd_co_reviewer_email').value       = coReviewer;
    document.getElementById('cd_manager_email').value           = manager;
    document.getElementById('cd_security_manager_email').value  = security;
    document.getElementById('cd_overtime_manager_email').value  = overtime;

    // Lock country dropdown when editing existing record; use hidden input to ensure value posts
    const cdCountrySelect = document.getElementById('cd_country_id');
    const cdCountryHidden = document.getElementById('cd_country_id_hidden');
    if (countryId) {
        cdCountrySelect.disabled = true;
        cdCountryHidden.value    = countryId;
        cdCountryHidden.name     = 'country_id';
        cdCountrySelect.removeAttribute('name');
    } else {
        cdCountrySelect.disabled = false;
        cdCountryHidden.value    = '';
        cdCountryHidden.name     = '';
        cdCountrySelect.name     = 'country_id';
    }

    countryDefaultModal.show();
}

function confirmDeleteCountryDefault(countryId, countryName) {
    document.getElementById('deleteCountryDefaultName').textContent = countryName;
    document.getElementById('confirmDeleteCountryDefaultBtn').href = '<?= URL ?>eastates/deleteCountryDefault/' + countryId;
    deleteCountryDefaultModal.show();
}
</script>
