<?php
/**
 * Location Controller
 * Handles all country and state management operations
 */
class location extends Controller
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
     * Default action - show countries list
     */
    public function index()
    {
        $this->countries();
    }
    
    /**
     * Display all countries and states in one page
     */
    public function countries()
    {
        $countries = $this->model->getAllCountries();
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/location/index.php';
    }
    
    /**
     * Display all states (redirect to countries page)
     */
    public function states($country_id = null)
    {
        // Redirect to countries page since everything is in one page
        header('Location: ' . URL . 'location/countries');
        exit();
    }
    
    /**
     * Get all states with country information
     */
    private function getAllStatesWithCountries()
    {
        $sql = "SELECT s.*, c.name as country_name, c.code as country_code 
                FROM state s
                LEFT JOIN country c ON s.country_id = c.id
                ORDER BY c.name, s.name";
        $stmt = $this->model->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Create a new country
     */
    public function createCountry()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));
            
            // Validate input
            if (empty($name) || empty($code)) {
                $_SESSION['error'] = "Country name and code are required";
                header('Location: ' . URL . 'location/countries');
                exit();
            }
            
            // Check if country code already exists
            $existing = $this->model->getCountryByCode($code);
            if ($existing) {
                $_SESSION['error'] = "Country code already exists";
                header('Location: ' . URL . 'location/countries');
                exit();
            }
            
            $result = $this->model->createCountry($name, $code);
            
            if ($result) {
                $_SESSION['success'] = "Country created successfully";
            } else {
                $_SESSION['error'] = "Failed to create country";
            }
            
            header('Location: ' . URL . 'location/countries');
            exit();
        }
        
        // If not POST, redirect to countries page
        header('Location: ' . URL . 'location/countries');
        exit();
    }
    
    /**
     * Edit country
     */
    public function editCountry($id)
    {
        $country = $this->model->getCountryById($id);
        
        if (!$country) {
            $_SESSION['error'] = "Country not found";
            header('Location: ' . URL . 'location/countries');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));
            
            if (empty($name) || empty($code)) {
                $_SESSION['error'] = "Country name and code are required";
                header('Location: ' . URL . 'location/countries');
                exit();
            }
            
            $sql = "UPDATE country SET name = :name, code = :code WHERE id = :id";
            $stmt = $this->model->db->prepare($sql);
            $result = $stmt->execute([
                ':name' => $name,
                ':code' => $code,
                ':id' => $id
            ]);
            
            if ($result) {
                $_SESSION['success'] = "Country updated successfully";
                header('Location: ' . URL . 'location/countries');
                exit();
            } else {
                $_SESSION['error'] = "Failed to update country";
                header('Location: ' . URL . 'location/countries');
                exit();
            }
        }
        
        // If not POST, redirect to countries page
        header('Location: ' . URL . 'location/countries');
        exit();
    }
    
    /**
     * Delete country
     */
    public function deleteCountry($id)
    {
        // Check if country has any states
        $states = $this->model->getStatesByCountry($id);
        if (count($states) > 0) {
            $_SESSION['error'] = "Cannot delete country with existing states. Delete states first.";
            header('Location: ' . URL . 'location/countries');
            exit();
        }
        
        // Check if country has any staff members
        $staff = $this->model->getStaffByCountry($id);
        if (count($staff) > 0) {
            $_SESSION['error'] = "Cannot delete country with assigned staff members";
            header('Location: ' . URL . 'location/countries');
            exit();
        }
        
        $sql = "DELETE FROM country WHERE id = :id";
        $stmt = $this->model->db->prepare($sql);
        $result = $stmt->execute([':id' => $id]);
        
        if ($result) {
            $_SESSION['success'] = "Country deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete country";
        }
        
        header('Location: ' . URL . 'location/countries');
        exit();
    }
    
    /**
     * Create a new state
     */
    public function createState()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $country_id = $_POST['country_id'];
            $name = trim($_POST['name']);
            $code = trim($_POST['code']) ?: null;
            
            // Validate input
            if (empty($country_id) || empty($name)) {
                $_SESSION['error'] = "Country and state name are required";
                header('Location: ' . URL . 'location/countries');
                exit();
            }
            
            $result = $this->model->createState($country_id, $name, $code);
            
            if ($result) {
                $_SESSION['success'] = "State created successfully";
            } else {
                $_SESSION['error'] = "Failed to create state. State name may already exist for this country.";
            }
            
            header('Location: ' . URL . 'location/countries');
            exit();
        }
        
        // If not POST, redirect to countries page
        header('Location: ' . URL . 'location/countries');
        exit();
    }
    
    /**
     * Edit state
     */
    public function editState($id)
    {
        $state = $this->model->getStateWithCountry($id);
        
        if (!$state) {
            $_SESSION['error'] = "State not found";
            header('Location: ' . URL . 'location/countries');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $country_id = $_POST['country_id'];
            $name = trim($_POST['name']);
            $code = trim($_POST['code']) ?: null;
            
            if (empty($country_id) || empty($name)) {
                $_SESSION['error'] = "Country and state name are required";
                header('Location: ' . URL . 'location/countries');
                exit();
            }
            
            $sql = "UPDATE state SET name = :name, code = :code WHERE id = :id";
            $stmt = $this->model->db->prepare($sql);
            $result = $stmt->execute([
                ':name' => $name,
                ':code' => $code,
                ':id' => $id
            ]);
            
            if ($result) {
                $_SESSION['success'] = "State updated successfully";
                header('Location: ' . URL . 'location/countries');
                exit();
            } else {
                $_SESSION['error'] = "Failed to update state";
                header('Location: ' . URL . 'location/countries');
                exit();
            }
        }
        
        // If not POST, redirect to countries page
        header('Location: ' . URL . 'location/countries');
        exit();
    }
    
    /**
     * Delete state
     */
    public function deleteState($id)
    {
        // Check if state has any staff members
        $staff = $this->model->getStaffByState($id);
        if (count($staff) > 0) {
            $_SESSION['error'] = "Cannot delete state with assigned staff members";
            header('Location: ' . URL . 'location/countries');
            exit();
        }
        
        $result = $this->model->deleteState($id);
        
        if ($result) {
            $_SESSION['success'] = "State deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete state";
        }
        
        header('Location: ' . URL . 'location/countries');
        exit();
    }
    
    /**
     * API endpoint to get states by country (for AJAX requests)
     */
    public function getStatesByCountryAjax()
    {
        if (isset($_GET['country_id'])) {
            $states = $this->model->getStatesByCountry($_GET['country_id']);
            header('Content-Type: application/json');
            echo json_encode($states);
            exit();
        }
        echo json_encode([]);
        exit();
    }
        
    /**
     * Get total number of states
     */
    private function getTotalStates()
    {
        $sql = "SELECT COUNT(*) as total FROM state";
        $stmt = $this->model->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->total;
    }
    
}
?>