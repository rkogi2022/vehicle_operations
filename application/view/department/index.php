<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management</title>
    
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

        .cursor-pointer {
            cursor: pointer;
        }
        
        .department-row:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Department Management</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Department Management</li>
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

    <!-- Departments Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-building me-1"></i> Departments List</span>
            <button class="btn btn-primary btn-sm" onclick="openDepartmentModal('add')">
                <i class="fas fa-plus"></i> Add Department
            </button>
        </div>

        <div class="card-body table-responsive">
            <table id="departmentsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $department): ?>
                        <tr class="department-row">
                            <td>
                                <strong><?= htmlspecialchars($department->name); ?></strong>
                            </td>
                            <td><?= date('M d, Y', strtotime($department->created_at)); ?></td>
                            <td><?= date('M d, Y', strtotime($department->updated_at)); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="openDepartmentModal('edit', <?= $department->id ?>, '<?= htmlspecialchars($department->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $department->id ?>, '<?= htmlspecialchars($department->name, ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No departments found. Click "Add Department" to create one.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- Department Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="department-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="departmentModalLabel">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="department_id">
                    <div class="mb-3">
                        <label for="department_name" class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="department_name" class="form-control" required 
                               placeholder="e.g., Human Resources, Information Technology, Finance">
                        <small class="text-muted">Enter a unique department name</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Department</button>
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
                <p>Are you sure you want to delete department: <strong id="deleteItemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This action cannot be undone. All data related to this department will be removed.
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
let departmentsDataTable;
let departmentModal, deleteModal;
let currentDepartmentId = null;

document.addEventListener("DOMContentLoaded", function () {
    // Initialize Bootstrap modals
    departmentModal = new bootstrap.Modal(document.getElementById('departmentModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Initialize Departments DataTable
    const departmentsTable = document.getElementById('departmentsTable');
    if (departmentsTable && departmentsTable.querySelector('tbody tr td[colspan]') === null) {
        departmentsDataTable = new simpleDatatables.DataTable(departmentsTable, {
            searchable: true,
            sortable: true,
            perPage: 10,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Search departments...",
                perPage: "",
                noRows: "No departments found",
                info: "Showing {start} to {end} of {rows} departments"
            }
        });
    }
    
    // Form validation for department
    const departmentForm = document.getElementById('department-form');
    if (departmentForm) {
        departmentForm.addEventListener('submit', function(e) {
            const name = document.getElementById('department_name').value.trim();
            
            if (name === '') {
                e.preventDefault();
                alert('Department name is required');
                return false;
            }
            
            if (name.length > 100) {
                e.preventDefault();
                alert('Department name must not exceed 100 characters');
                return false;
            }
        });
    }
});

// Department Functions
function openDepartmentModal(action, id = null, name = '') {
    const form = document.getElementById('department-form');
    const modalTitle = document.getElementById('departmentModalLabel');
    
    if (action === 'add') {
        modalTitle.textContent = 'Add Department';
        form.action = '<?= URL ?>department/create';
        document.getElementById('department_id').value = '';
        document.getElementById('department_name').value = '';
        currentDepartmentId = null;
    } else {
        modalTitle.textContent = 'Edit Department';
        form.action = '<?= URL ?>department/update/' + id;
        document.getElementById('department_id').value = id;
        document.getElementById('department_name').value = name;
        currentDepartmentId = id;
    }
    
    departmentModal.show();
}

function confirmDelete(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?= URL ?>department/delete/' + id;
    deleteModal.show();
}

// Optional: Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + N to open add modal
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        openDepartmentModal('add');
    }
});

// Refresh page after modal close (optional)
document.getElementById('departmentModal')?.addEventListener('hidden.bs.modal', function() {
    // Uncomment if you want to refresh the page after modal closes
    // location.reload();
});
</script>

</body>
</html>