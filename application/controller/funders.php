<?php
/**
 * Funders Controller
 * Handles all funder code management operations
 */
class funders extends Controller
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
        
        // Load the model that contains funder functions
        require_once APP . 'model/model.php';
        $this->model = new Model($this->db);
    }
    
    /**
     * Display funders management page
     */
    public function index()
    {
        $funderCodes = $this->model->getAllFunders();
        $totalCount = $this->model->getTotalFunders();
        
        // Load header
        require APP . 'view/_templates/header.php';
        // Load funders view
        require APP . 'view/funders/index.php';
        // Load footer
        require APP . 'view/_templates/footer.php';
    }
    
    /**
     * Create new funder code
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'funders');
            exit();
        }
        
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Funder code name is required';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Funder code name must not exceed 100 characters';
        } elseif ($this->model->funderNameExists($name)) {
            $errors[] = 'Funder code name already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: ' . URL . 'funders');
            exit();
        }
        
        if ($this->model->createFunder($name)) {
            $_SESSION['success'] = 'Funder code created successfully!';
        } else {
            $_SESSION['error'] = 'Failed to create funder code';
        }
        
        header('Location: ' . URL . 'funders');
        exit();
    }
    
    /**
     * Update funder code
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'funders');
            exit();
        }
        
        $id = (int)$id;
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Funder code name is required';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Funder code name must not exceed 100 characters';
        } elseif ($this->model->funderNameExists($name, $id)) {
            $errors[] = 'Funder code name already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: ' . URL . 'funders');
            exit();
        }
        
        if ($this->model->updateFunder($id, $name)) {
            $_SESSION['success'] = 'Funder code updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update funder code';
        }
        
        header('Location: ' . URL . 'funders');
        exit();
    }
    
    /**
     * Delete funder code
     */
    public function delete($id)
    {
        $id = (int)$id;
        
        // Check if funder code exists
        $funder = $this->model->getFunderById($id);
        if (!$funder) {
            $_SESSION['error'] = 'Funder code not found';
            header('Location: ' . URL . 'funders');
            exit();
        }
        
        // Delete funder code
        if ($this->model->deleteFunder($id)) {
            $_SESSION['success'] = 'Funder code deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete funder code';
        }
        
        header('Location: ' . URL . 'funders');
        exit();
    }
    
    /**
     * Get funder code by ID (for API/AJAX)
     */
    public function getFunder($id)
    {
        header('Content-Type: application/json');
        $funder = $this->model->getFunderById((int)$id);
        
        if ($funder) {
            echo json_encode(['success' => true, 'data' => $funder]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Funder code not found']);
        }
    }
}
?>