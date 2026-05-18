<?php
/**
 * Departments Controller
 * Handles all department management operations
 */
class department extends Controller
{
    
    public function __construct()
    {
        parent::__construct();
        // Check if user is logged in
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header('Location: ' . URL . 'login/index');
            exit();
        }
        
        // Load the model that contains department functions
        require_once APP . 'model/model.php';
        $this->model = new Model($this->db);
    }
    
    /**
     * Display departments management page
     */
    public function index()
    {
        $departments = $this->model->getAll(1, 100);
        $totalCount = $this->model->getTotalCount();
        
        // Load header
        require APP . 'view/_templates/header.php';
        // Load department view
        require APP . 'view/department/index.php';
        // Load footer
        require APP . 'view/_templates/footer.php';
    }
    
    /**
     * Create new department
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'department');  // Changed from departments to department
            exit();
        }
        
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Department name is required';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Department name must not exceed 100 characters';
        } elseif ($this->model->nameExists($name)) {
            $errors[] = 'Department name already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: ' . URL . 'department');  // Changed from departments to department
            exit();
        }
        
        if ($this->model->create(['name' => $name])) {
            $_SESSION['success'] = 'Department created successfully!';
        } else {
            $_SESSION['error'] = 'Failed to create department';
        }
        
        header('Location: ' . URL . 'department');  // Changed from departments to department
        exit();
    }
    
    /**
     * Update department
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'department');  // Changed from departments to department
            exit();
        }
        
        $id = (int)$id;
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Department name is required';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Department name must not exceed 100 characters';
        } elseif ($this->model->nameExists($name, $id)) {
            $errors[] = 'Department name already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: ' . URL . 'department');  // Changed from departments to department
            exit();
        }
        
        if ($this->model->update($id, ['name' => $name])) {
            $_SESSION['success'] = 'Department updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update department';
        }
        
        header('Location: ' . URL . 'department');  // Changed from departments to department
        exit();
    }
    
    /**
     * Delete department
     */
    public function delete($id)
    {
        $id = (int)$id;
        
        // Check if department exists
        $department = $this->model->getById($id);
        if (!$department) {
            $_SESSION['error'] = 'Department not found';
            header('Location: ' . URL . 'department');  // Changed from departments to department
            exit();
        }
        
        // Delete department
        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Department deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete department';
        }
        
        header('Location: ' . URL . 'department');  // Changed from departments to department
        exit();
    }
    
    /**
     * Get department by ID (for API/AJAX)
     */
    public function getDepartment($id)
    {
        header('Content-Type: application/json');
        $department = $this->model->getById((int)$id);
        
        if ($department) {
            echo json_encode(['success' => true, 'data' => $department]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Department not found']);
        }
    }
}