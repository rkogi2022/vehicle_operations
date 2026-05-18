<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Management</title>
    
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

        #statesSection {
            animation: fadeIn 0.5s ease-in;
            display: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
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

        .cursor-pointer {
            cursor: pointer;
        }
        
        .bg-info {
            background-color: #17a2b8;
        }
        
        .bg-info:hover {
            background-color: #138496;
        }
        
        .country-row:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Location Management</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Location Management</li>
    </ol>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Countries Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-globe me-1"></i> Countries</span>
            <button class="btn btn-primary btn-sm" onclick="openCountryModal('add')">
                <i class="fas fa-plus"></i> Add Country
            </button>
        </div>

        <div class="card-body table-responsive">
            <table id="countriesTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Country Name</th>
                        <th>Country Code</th>
                        <th>States Count</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($countries)): ?>
                    <?php foreach ($countries as $country): ?>
                        <tr class="country-row">
                            <td><strong><?= htmlspecialchars($country->name); ?></strong></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($country->code); ?></span></td>
                            <td>
                                <span class="badge bg-info cursor-pointer" onclick="showStates(<?= $country->id ?>, '<?= htmlspecialchars($country->name, ENT_QUOTES) ?>')">
                                    <?= isset($country->state_count) ? $country->state_count : 0 ?> States
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($country->created_at)); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="openCountryModal('edit', <?= $country->id ?>, '<?= htmlspecialchars($country->name, ENT_QUOTES) ?>', '<?= htmlspecialchars($country->code, ENT_QUOTES) ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteCountry(<?= $country->id ?>, '<?= htmlspecialchars($country->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="showStates(<?= $country->id ?>, '<?= htmlspecialchars($country->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-map-marker-alt"></i> Manage States
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No countries found. Click "Add Country" to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- States Section (Initially Hidden) -->
    <div id="statesSection">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-map-marker-alt me-1"></i> States/Regions for <span id="countryNameDisplay"></span></span>
                <div>
                    <button class="btn btn-secondary btn-sm" onclick="hideStates()">
                        <i class="fas fa-arrow-left"></i> Back to Countries
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openStateModal('add')">
                        <i class="fas fa-plus"></i> Add State
                    </button>
                </div>
            </div>

            <div class="card-body table-responsive">
                <table id="statesTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>State/Region Name</th>
                            <th>Code</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="statesTableBody">
                        <tr>
                            <td colspan="5" class="text-center">Select a country to view states</td>
                        </tr>
                    </tbody>
                  </table>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Country Modal -->
<div class="modal fade" id="countryModal" tabindex="-1" aria-labelledby="countryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="country-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="countryModalLabel">Add Country</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="country_id">
                    <div class="mb-3">
                        <label for="country_name" class="form-label">Country Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="country_name" class="form-control" required 
                               placeholder="e.g., Nigeria, United States, Kenya">
                    </div>
                    <div class="mb-3">
                        <label for="country_code" class="form-label">Country Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="country_code" class="form-control" maxlength="3" required
                               placeholder="e.g., NGA, USA, KEN" style="text-transform: uppercase">
                        <small class="text-muted">3-letter country code (ISO 3166-1 alpha-3)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Country</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- State Modal -->
<div class="modal fade" id="stateModal" tabindex="-1" aria-labelledby="stateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="state-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="stateModalLabel">Add State</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="state_id">
                    <input type="hidden" name="country_id" id="state_country_id">
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" id="state_country_name" class="form-control" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label for="state_name" class="form-label">State/Region Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="state_name" class="form-control" required 
                               placeholder="e.g., Lagos, California, Nairobi">
                    </div>
                    <div class="mb-3">
                        <label for="state_code" class="form-label">Code (Optional)</label>
                        <input type="text" name="code" id="state_code" class="form-control" maxlength="10"
                               placeholder="e.g., LA, CA, NBO">
                        <small class="text-muted">Optional state/region code</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save State</button>
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
                <p>Are you sure you want to delete: <strong id="deleteItemName"></strong>?</p>
                <div id="deleteWarning" class="alert alert-warning" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This country has states and/or staff members assigned. Please reassign or delete them first.
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

<script>
let countriesDataTable;
let statesDataTable;
let currentCountryId = null;
let currentCountryName = '';
let countryModal, stateModal, deleteModal;

document.addEventListener("DOMContentLoaded", function () {
    // Initialize Bootstrap modals
    countryModal = new bootstrap.Modal(document.getElementById('countryModal'));
    stateModal = new bootstrap.Modal(document.getElementById('stateModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Initialize Countries DataTable
    const countriesTable = document.getElementById('countriesTable');
    if (countriesTable && countriesTable.querySelector('tbody tr td[colspan]') === null) {
        countriesDataTable = new simpleDatatables.DataTable(countriesTable, {
            searchable: true,
            sortable: true,
            perPage: 10,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Search countries...",
                perPage: "{select} countries per page",
                noRows: "No countries found",
                info: "Showing {start} to {end} of {rows} countries"
            }
        });
    }
    
    // Auto-uppercase country code
    const countryCodeInput = document.getElementById('country_code');
    if (countryCodeInput) {
        countryCodeInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    }
    
    // Form validation for country
    const countryForm = document.getElementById('country-form');
    if (countryForm) {
        countryForm.addEventListener('submit', function(e) {
            const name = document.getElementById('country_name').value.trim();
            const code = document.getElementById('country_code').value.trim();
            
            if (name === '') {
                e.preventDefault();
                alert('Country name is required');
                return false;
            }
            
            if (code === '') {
                e.preventDefault();
                alert('Country code is required');
                return false;
            }
            
            if (code.length !== 3) {
                e.preventDefault();
                alert('Country code must be exactly 3 characters');
                return false;
            }
        });
    }
    
    // Form validation for state
    const stateForm = document.getElementById('state-form');
    if (stateForm) {
        stateForm.addEventListener('submit', function(e) {
            const name = document.getElementById('state_name').value.trim();
            
            if (name === '') {
                e.preventDefault();
                alert('State/Region name is required');
                return false;
            }
        });
    }
});

// Country Functions
function openCountryModal(action, id = null, name = '', code = '') {
    const form = document.getElementById('country-form');
    const modalTitle = document.getElementById('countryModalLabel');
    
    if (action === 'add') {
        modalTitle.textContent = 'Add Country';
        form.action = '<?= URL ?>location/createCountry';
        document.getElementById('country_id').value = '';
        document.getElementById('country_name').value = '';
        document.getElementById('country_code').value = '';
    } else {
        modalTitle.textContent = 'Edit Country';
        form.action = '<?= URL ?>location/editCountry/' + id;
        document.getElementById('country_id').value = id;
        document.getElementById('country_name').value = name;
        document.getElementById('country_code').value = code;
    }
    
    countryModal.show();
}

function confirmDeleteCountry(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?= URL ?>location/deleteCountry/' + id;
    document.getElementById('deleteWarning').style.display = 'none';
    deleteModal.show();
}

// State Functions
function showStates(countryId, countryName = '') {
    currentCountryId = countryId;
    currentCountryName = countryName;
    
    document.getElementById('countryNameDisplay').textContent = countryName;
    document.getElementById('state_country_id').value = countryId;
    document.getElementById('state_country_name').value = countryName;
    
    // Fetch states via AJAX
    fetch('<?= URL ?>location/getStatesByCountryAjax?country_id=' + countryId)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('statesTableBody');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No states found for this country. Click "Add State" to create one.</td></tr>';
            } else {
                data.forEach(state => {
                    const row = tbody.insertRow();
                    row.id = 'state-row-' + state.id;
                    row.innerHTML = `
                        <td>${state.id}</td>
                        <td><strong>${escapeHtml(state.name)}</strong></td>
                        <td>${state.code ? '<span class="badge bg-secondary">' + escapeHtml(state.code) + '</span>' : '-'}</td>
                        <td>${new Date(state.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="openStateModal('edit', ${state.id}, '${escapeHtml(state.name)}', '${state.code ? escapeHtml(state.code) : ''}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteState(${state.id}, '${escapeHtml(state.name)}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                });
            }
            
            // Initialize or reinitialize states DataTable
            if (statesDataTable) {
                statesDataTable.destroy();
            }
            const statesTable = document.getElementById('statesTable');
            if (statesTable) {
                statesDataTable = new simpleDatatables.DataTable(statesTable, {
                    searchable: true,
                    sortable: true,
                    perPage: 10,
                    perPageSelect: [10, 25, 50, 100],
                    labels: {
                        placeholder: "Search states...",
                        perPage: "{select} states per page",
                        noRows: "No states found",
                        info: "Showing {start} to {end} of {rows} states"
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error fetching states:', error);
            document.getElementById('statesTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading states. Please try again.</td></tr>';
        });
    
    // Show states section
    document.getElementById('statesSection').style.display = 'block';
    document.getElementById('statesSection').scrollIntoView({ behavior: 'smooth' });
}

function hideStates() {
    document.getElementById('statesSection').style.display = 'none';
    currentCountryId = null;
    currentCountryName = '';
}

function openStateModal(action, id = null, name = '', code = '') {
    if (!currentCountryId && action === 'add') {
        alert('Please select a country first');
        return;
    }
    
    const form = document.getElementById('state-form');
    const modalTitle = document.getElementById('stateModalLabel');
    
    if (action === 'add') {
        modalTitle.textContent = 'Add State/Region';
        form.action = '<?= URL ?>location/createState';
        document.getElementById('state_id').value = '';
        document.getElementById('state_name').value = '';
        document.getElementById('state_code').value = '';
    } else {
        modalTitle.textContent = 'Edit State/Region';
        form.action = '<?= URL ?>location/editState/' + id;
        document.getElementById('state_id').value = id;
        document.getElementById('state_name').value = name;
        document.getElementById('state_code').value = code;
    }
    
    stateModal.show();
}

function confirmDeleteState(id, name) {
    if (confirm(`Are you sure you want to delete state: ${name}?`)) {
        window.location.href = '<?= URL ?>location/deleteState/' + id;
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Refresh states view after modal close
document.getElementById('stateModal')?.addEventListener('hidden.bs.modal', function() {
    if (currentCountryId) {
        showStates(currentCountryId, currentCountryName);
    }
});

document.getElementById('countryModal')?.addEventListener('hidden.bs.modal', function() {
    location.reload(); // Reload to show updated countries list
});
</script>

</body>
</html>