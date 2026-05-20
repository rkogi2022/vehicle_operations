<?php
/**
 * EA States Controller
 * Handles all EA state management operations (reviewers, managers, security, operations per state)
 */
class Eastates extends Controller
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
        
        // Only admin and super_admin can access
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'admin' && $role !== 'super_admin') {
            $_SESSION['error'] = "Access denied. Admin privileges required.";
            header('Location: ' . URL . 'home/index');
            exit();
        }
    }
    
    /**
     * Default action - show EA states list
     */
    public function index()
    {
        $this->states();
    }
    /**
     * Display all EA states
     */
    public function states()
    {
        $eaStates             = $this->model->getAllEaStates();
        $availableStates      = $this->model->getAvailableStates();
        $allStates            = $this->model->getAllStatesWithCountry();
        $staffEmails          = $this->model->getAllStaffEmails();
        $securityManagers     = $this->model->getSecurityManagers();
        $drivers              = $this->model->getAllDriversForDropdown();
        $countryDefaults      = $this->model->getAllCountryDefaultApprovers();
        $allCountries         = $this->model->getAllCountries();

        require APP . 'view/_templates/header.php';
        require APP . 'view/ea_states/index.php';
    }

    /**
     * Create a new EA state configuration
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $state_id = $_POST['state_id'];
            $reviewer_email = trim($_POST['reviewer_email']);
            $co_reviewer_email = !empty($_POST['co_reviewer_email']) ? trim($_POST['co_reviewer_email']) : null;
            $manager_email = trim($_POST['manager_email']);
            $security_manager_email  = !empty($_POST['security_manager_email'])  ? trim($_POST['security_manager_email'])  : null;
            $overtime_manager_email  = !empty($_POST['overtime_manager_email'])  ? trim($_POST['overtime_manager_email'])  : null;
            $driver_ids = !empty($_POST['driver_ids']) ? array_filter($_POST['driver_ids']) : [];

            // Validate input
            if (empty($state_id) || empty($reviewer_email) || empty($manager_email)) {
                $_SESSION['error'] = "State, Reviewer, and Manager are required";
                header('Location: ' . URL . 'eastates/states');
                exit();
            }

            // Check if state already has EA configuration
            if ($this->model->eaStateExists($state_id)) {
                $_SESSION['error'] = "This state already has an EA configuration";
                header('Location: ' . URL . 'eastates/states');
                exit();
            }

            $result = $this->model->addEaState($state_id, $reviewer_email, $co_reviewer_email,
                                               $manager_email, $security_manager_email, $driver_ids, $overtime_manager_email);
            
            if ($result) {
                $_SESSION['success'] = "EA State configuration created successfully";
            } else {
                $_SESSION['error'] = "Failed to create EA State configuration";
            }
            
            header('Location: ' . URL . 'eastates/states');
            exit();
        }
        
        // If not POST, redirect to states page
        header('Location: ' . URL . 'eastates/states');
        exit();
    }

    /**
     * Edit EA state configuration
     */
    public function edit($id)
    {
        $eaState = $this->model->getEaStateById($id);
        
        if (!$eaState) {
            $_SESSION['error'] = "EA State configuration not found";
            header('Location: ' . URL . 'eastates/states');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $state_id = $_POST['state_id'];
            $reviewer_email = trim($_POST['reviewer_email']);
            $co_reviewer_email = !empty($_POST['co_reviewer_email']) ? trim($_POST['co_reviewer_email']) : null;
            $manager_email = trim($_POST['manager_email']);
            $security_manager_email  = !empty($_POST['security_manager_email'])  ? trim($_POST['security_manager_email'])  : null;
            $overtime_manager_email  = !empty($_POST['overtime_manager_email'])  ? trim($_POST['overtime_manager_email'])  : null;
            $driver_ids = !empty($_POST['driver_ids']) ? array_filter($_POST['driver_ids']) : [];

            if (empty($state_id) || empty($reviewer_email) || empty($manager_email)) {
                $_SESSION['error'] = "State, Reviewer, and Manager are required";
                header('Location: ' . URL . 'eastates/states');
                exit();
            }

            // Check if state already has EA configuration (excluding current)
            if ($this->model->eaStateExists($state_id, $id)) {
                $_SESSION['error'] = "This state already has an EA configuration";
                header('Location: ' . URL . 'eastates/states');
                exit();
            }

            $result = $this->model->updateEaState($id, $state_id, $reviewer_email, $co_reviewer_email,
                                                   $manager_email, $security_manager_email, $driver_ids, $overtime_manager_email);
            
            if ($result) {
                $_SESSION['success'] = "EA State configuration updated successfully";
                header('Location: ' . URL . 'eastates/states');
                exit();
            } else {
                $_SESSION['error'] = "Failed to update EA State configuration";
                header('Location: ' . URL . 'eastates/states');
                exit();
            }
        }
        
        // If not POST, redirect to states page
        header('Location: ' . URL . 'eastates/states');
        exit();
    }

    /**
     * Delete EA state configuration
     */
    public function delete($id)
    {
        $result = $this->model->deleteEaState($id);
        
        if ($result) {
            $_SESSION['success'] = "EA State configuration deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete EA State configuration";
        }
        
        header('Location: ' . URL . 'eastates/states');
        exit();
    }
    
    /**
     * API endpoint to get EA state by state ID (for AJAX requests)
     */
    public function getByStateAjax()
    {
        if (isset($_GET['ea_state_id'])) {
            $eaState = $this->model->getEaStateById($_GET['ea_state_id']);
            header('Content-Type: application/json');
            echo json_encode($eaState);
            exit();
        }
        echo json_encode(null);
        exit();
    }

    /**
     * Save country default approvers (POST)
     */
    public function saveCountryDefault()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $country_id             = $_POST['country_id'] ?? null;
            $reviewer_email         = trim($_POST['cd_reviewer_email'] ?? '');
            $co_reviewer_email      = !empty($_POST['cd_co_reviewer_email'])      ? trim($_POST['cd_co_reviewer_email'])      : null;
            $manager_email          = trim($_POST['cd_manager_email'] ?? '');
            $security_manager_email = !empty($_POST['cd_security_manager_email']) ? trim($_POST['cd_security_manager_email']) : null;
            $overtime_manager_email = !empty($_POST['cd_overtime_manager_email']) ? trim($_POST['cd_overtime_manager_email']) : null;

            if (empty($country_id) || empty($reviewer_email) || empty($manager_email)) {
                $_SESSION['error'] = "Country, Reviewer, and Manager are required";
                header('Location: ' . URL . 'eastates/states');
                exit();
            }

            $result = $this->model->saveCountryDefaultApprovers(
                $country_id, $reviewer_email, $co_reviewer_email,
                $manager_email, $security_manager_email, $overtime_manager_email
            );

            if ($result) {
                $_SESSION['success'] = "Country default approvers saved successfully";
            } else {
                $_SESSION['error'] = "Failed to save country default approvers";
            }
        }

        header('Location: ' . URL . 'eastates/states');
        exit();
    }

    /**
     * Delete country default approvers
     */
    public function deleteCountryDefault($country_id)
    {
        $result = $this->model->deleteCountryDefaultApprovers($country_id);

        if ($result) {
            $_SESSION['success'] = "Country default approvers deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete country default approvers";
        }

        header('Location: ' . URL . 'eastates/states');
        exit();
    }
}
?>