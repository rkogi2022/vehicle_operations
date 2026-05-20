<?php
/**
 * Driver Controller
 * Handles all driver management operations
 */
class drivers extends Controller
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
    }
    
    /**
     * Default action - show drivers list
     */
    public function index()
    {
        $this->drivers();
    }
    
    /**
     * Display all drivers
     */
    public function drivers()
    {
        $drivers = $this->model->getAllDrivers();
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/drivers/index.php';
    }
    
    /**
     * Create a new driver
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            // Validate input
            if (empty($name) || empty($phone)) {
                $_SESSION['error'] = "Name and phone number are required";
                header('Location: ' . URL . 'drivers/drivers');
                exit();
            }

            // Validate email format only if provided
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format";
                header('Location: ' . URL . 'drivers/drivers');
                exit();
            }

            // Check if email already exists (only when email given)
            if (!empty($email) && $this->model->driverEmailExists($email)) {
                $_SESSION['error'] = "Email already exists";
                header('Location: ' . URL . 'drivers/drivers');
                exit();
            }

            $result = $this->model->addDriver($name, $email ?: null, $phone);
            
            if ($result) {
                $_SESSION['success'] = "Driver created successfully";
            } else {
                $_SESSION['error'] = "Failed to create driver";
            }
            
            header('Location: ' . URL . 'drivers/drivers');
            exit();
        }
        
        // If not POST, redirect to drivers page
        header('Location: ' . URL . 'drivers/drivers');
        exit();
    }
    
    /**
     * Edit driver
     */
    public function edit($id)
    {
        $driver = $this->model->getDriverById($id);
        
        if (!$driver) {
            $_SESSION['error'] = "Driver not found";
            header('Location: ' . URL . 'drivers/drivers');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if (empty($name) || empty($phone)) {
                $_SESSION['error'] = "Name and phone number are required";
                header('Location: ' . URL . 'drivers/drivers');
                exit();
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format";
                header('Location: ' . URL . 'drivers/drivers');
                exit();
            }

            if (!empty($email) && $this->model->driverEmailExists($email, $id)) {
                $_SESSION['error'] = "Email already exists for another driver";
                header('Location: ' . URL . 'drivers/drivers');
                exit();
            }

            $result = $this->model->updateDriver($id, $name, $email ?: null, $phone);
            
            if ($result) {
                $_SESSION['success'] = "Driver updated successfully";
                header('Location: ' . URL . 'drivers/drivers');
                exit();
            } else {
                $_SESSION['error'] = "Failed to update driver";
                header('Location: ' . URL . 'drivers/drivers');
                exit();
            }
        }
        
        // If not POST, redirect to drivers page
        header('Location: ' . URL . 'drivers/drivers');
        exit();
    }
    
    /**
     * Delete driver
     */
    public function delete($id)
    {
        $result = $this->model->deleteDriver($id);
        
        if ($result) {
            $_SESSION['success'] = "Driver deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete driver";
        }
        
        header('Location: ' . URL . 'drivers/drivers');
        exit();
    }
    
    /**
     * API endpoint to search drivers (for AJAX requests)
     */
    public function searchAjax()
    {
        if (isset($_GET['keyword'])) {
            $keyword = trim($_GET['keyword']);
            $drivers = $this->model->searchDrivers($keyword);
            header('Content-Type: application/json');
            echo json_encode($drivers);
            exit();
        }
        echo json_encode([]);
        exit();
    }
}
?>