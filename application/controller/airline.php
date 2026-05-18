<?php
/**
 * Airlines Controller
 * Handles all airline management operations
 */
class airline extends Controller
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
        
        // Load the model that contains airline functions
        require_once APP . 'model/model.php';
        $this->model = new Model($this->db);
    }
    
    /**
     * Display airlines management page
     */
    public function index()
    {
        $airlines = $this->model->getAllAirlines();
        $totalCount = $this->model->getTotalAirlines();
        
        // Load header
        require APP . 'view/_templates/header.php';
        // Load airlines view
        require APP . 'view/airlines/index.php';
        // Load footer
        require APP . 'view/_templates/footer.php';
    }
    
    /**
     * Create new airline
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'airline');
            exit();
        }
        
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Airline name is required';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Airline name must not exceed 100 characters';
        } elseif ($this->model->airlineNameExists($name)) {
            $errors[] = 'Airline name already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: ' . URL . 'airline');
            exit();
        }
        
        if ($this->model->createAirline($name)) {
            $_SESSION['success'] = 'Airline created successfully!';
        } else {
            $_SESSION['error'] = 'Failed to create airline';
        }
        
        header('Location: ' . URL . 'airline');
        exit();
    }
    
    /**
     * Update airline
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'airline');
            exit();
        }
        
        $id = (int)$id;
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Airline name is required';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Airline name must not exceed 100 characters';
        } elseif ($this->model->airlineNameExists($name, $id)) {
            $errors[] = 'Airline name already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: ' . URL . 'airline');
            exit();
        }
        
        if ($this->model->updateAirline($id, $name)) {
            $_SESSION['success'] = 'Airline updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update airline';
        }
        
        header('Location: ' . URL . 'airline');
        exit();
    }
    
    /**
     * Delete airline
     */
    public function delete($id)
    {
        $id = (int)$id;
        
        // Check if airline exists
        $airline = $this->model->getAirlineById($id);
        if (!$airline) {
            $_SESSION['error'] = 'Airline not found';
            header('Location: ' . URL . 'airline');
            exit();
        }
        
        // Delete airline
        if ($this->model->deleteAirline($id)) {
            $_SESSION['success'] = 'Airline deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete airline';
        }
        
        header('Location: ' . URL . 'airline');
        exit();
    }
    
    /**
     * Get airline by ID (for API/AJAX)
     */
    public function getAirline($id)
    {
        header('Content-Type: application/json');
        $airline = $this->model->getAirlineById((int)$id);
        
        if ($airline) {
            echo json_encode(['success' => true, 'data' => $airline]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Airline not found']);
        }
    }
}
?>