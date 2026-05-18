<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    
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
    </style>
</head>
<body>

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">User Management</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">User Management</li>
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

    <!-- Users Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users me-1"></i> Users List</span>
            <button class="btn btn-primary btn-sm" onclick="openUserModal('add')">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>

        <div class="card-body table-responsive">
            <table id="usersTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Country</th>
                        <th>State</th>
                        <th>Department</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user->id); ?></td>
                            <td><?= htmlspecialchars($user->email); ?></td>
                            <td>
                                <span class="badge bg-<?= $user->role == 'super_admin' ? 'danger' : ($user->role == 'admin' ? 'warning' : 'info'); ?>">
                                    <?= htmlspecialchars($user->role); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($user->country_name ?? '-'); ?></td>
                            <td><?= htmlspecialchars($user->state_name ?? '-'); ?></td>
                            <td><?= htmlspecialchars($user->department_name ?? '-'); ?></td>
                            <td><?= date('M d, Y', strtotime($user->created_at)); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= $user->id; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $user->id; ?>, '<?= htmlspecialchars($user->email, ENT_QUOTES); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No users found. Click "Add User" to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="userForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="user_id">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" required 
                               placeholder="user@example.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Enter password">
                        <small class="text-muted">Leave blank to keep current password when editing</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role; ?>"><?= ucfirst(str_replace('_', ' ', $role)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="country_id" class="form-label">Country</label>
                        <select name="country_id" id="country_id" class="form-select">
                            <option value="">Select Country</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= $country->id; ?>"><?= htmlspecialchars($country->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="state_id" class="form-label">State/Region</label>
                        <select name="state_id" id="state_id" class="form-select">
                            <option value="">Select State/Region</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?= $department->id; ?>"><?= htmlspecialchars($department->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save User</button>
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
                <p>Are you sure you want to delete user: <strong id="deleteItemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This action cannot be undone.
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
let usersDataTable;
let userModal, deleteModal;

document.addEventListener("DOMContentLoaded", function () {
    // Initialize Bootstrap modals
    userModal = new bootstrap.Modal(document.getElementById('userModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Initialize Users DataTable
    const usersTable = document.getElementById('usersTable');
    if (usersTable && usersTable.querySelector('tbody tr td[colspan]') === null) {
        usersDataTable = new simpleDatatables.DataTable(usersTable, {
            searchable: true,
            sortable: true,
            perPage: 10,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Search users...",
                perPage: "",
                noRows: "No users found",
                info: "Showing {start} to {end} of {rows} users"
            }
        });
    }
    
    // Country change event - load states
    document.getElementById('country_id').addEventListener('change', function() {
        const countryId = this.value;
        const stateSelect = document.getElementById('state_id');
        
        if (countryId) {
            fetch('<?= URL; ?>users/getStatesByCountryAjax?country_id=' + countryId)
                .then(response => response.json())
                .then(data => {
                    stateSelect.innerHTML = '<option value="">Select State/Region</option>';
                    data.forEach(state => {
                        stateSelect.innerHTML += `<option value="${state.id}">${escapeHtml(state.name)}</option>`;
                    });
                });
        } else {
            stateSelect.innerHTML = '<option value="">Select State/Region</option>';
        }
    });
    
    // Form validation
    const userForm = document.getElementById('userForm');
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const role = document.getElementById('role').value;
            const password = document.getElementById('password').value;
            const userId = document.getElementById('user_id').value;
            
            if (email === '') {
                e.preventDefault();
                alert('Email is required');
                return false;
            }
            
            if (role === '') {
                e.preventDefault();
                alert('Role is required');
                return false;
            }
            
            // Password required only for new users
            if (!userId && password === '') {
                e.preventDefault();
                alert('Password is required for new users');
                return false;
            }
        });
    }
});

function openUserModal(action, id = null) {
    const form = document.getElementById('userForm');
    const modalTitle = document.getElementById('userModalLabel');
    const passwordField = document.getElementById('password');
    const passwordRequired = document.getElementById('passwordRequired');
    
    if (action === 'add') {
        modalTitle.textContent = 'Add User';
        form.action = '<?= URL ?>users/createUser';
        document.getElementById('user_id').value = '';
        document.getElementById('email').value = '';
        document.getElementById('role').value = '';
        document.getElementById('country_id').value = '';
        document.getElementById('state_id').innerHTML = '<option value="">Select State/Region</option>';
        document.getElementById('department_id').value = '';
        passwordField.value = '';
        passwordField.required = true;
        passwordRequired.style.display = 'inline';
        userModal.show();
    }
}

function editUser(id) {
    // Fetch user data
    fetch('<?= URL; ?>users/getStaffById/' + id)
        .then(response => response.json())
        .then(data => {
            const form = document.getElementById('userForm');
            const modalTitle = document.getElementById('userModalLabel');
            const passwordField = document.getElementById('password');
            const passwordRequired = document.getElementById('passwordRequired');
            
            modalTitle.textContent = 'Edit User';
            form.action = '<?= URL; ?>users/editUser/' + id;
            document.getElementById('user_id').value = data.id;
            document.getElementById('email').value = data.email;
            document.getElementById('role').value = data.role;
            document.getElementById('country_id').value = data.country_id || '';
            document.getElementById('department_id').value = data.department_id || '';
            passwordField.value = '';
            passwordField.required = false;
            passwordRequired.style.display = 'none';
            
            if (data.country_id) {
                // Load states for the selected country
                fetch('<?= URL; ?>users/getStatesByCountryAjax?country_id=' + data.country_id)
                    .then(response => response.json())
                    .then(states => {
                        const stateSelect = document.getElementById('state_id');
                        stateSelect.innerHTML = '<option value="">Select State/Region</option>';
                        states.forEach(state => {
                            const selected = (state.id == data.state_id) ? 'selected' : '';
                            stateSelect.innerHTML += `<option value="${state.id}" ${selected}>${escapeHtml(state.name)}</option>`;
                        });
                    });
            } else {
                document.getElementById('state_id').innerHTML = '<option value="">Select State/Region</option>';
            }
            
            userModal.show();
        });
}

function confirmDelete(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?= URL ?>users/deleteUser/' + id;
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