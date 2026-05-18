<?php
/**
 * Users Controller
 * Handles all user management operations
 */
class users extends Controller
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
        
        // Check if user has admin or super_admin role
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
            header('Location: ' . URL . 'home/index');
            exit();
        }
    }
    
    /**
     * Default action - show users list
     */
    public function index()
    {
        $users = $this->model->getAllStaff();
        $countries = $this->model->getAllCountries();
        $departments = $this->model->getAllDepartments();
        $roles = ['staff', 'admin', 'super_admin'];

        require APP . 'view/_templates/header.php';
        require APP . 'view/users/index.php';
        require APP . 'view/_templates/footer.php';
    }
    
    /**
     * Create a new user
     */
    public function createUser()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $country_id = !empty($_POST['country_id']) ? $_POST['country_id'] : null;
            $state_id = !empty($_POST['state_id']) ? $_POST['state_id'] : null;
            $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;
            $password = $_POST['password'];
            
            // Validate input
            if (empty($email) || empty($role) || empty($password)) {
                $_SESSION['error'] = "Email, role, and password are required";
                header('Location: ' . URL . 'users');
                exit();
            }
            
            // Check if email already exists
            if ($this->model->emailExists($email)) {
                $_SESSION['error'] = "Email already exists";
                header('Location: ' . URL . 'users');
                exit();
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Create user
            $data = [
                'email' => $email,
                'password' => $hashed_password,
                'role' => $role,
                'country_id' => $country_id,
                'state_id' => $state_id,
                'department_id' => $department_id
            ];
            
            $result = $this->model->createStaff($data);
            
            if ($result) {
                $_SESSION['success'] = "User created successfully";
            } else {
                $_SESSION['error'] = "Failed to create user";
            }
            
            header('Location: ' . URL . 'users');
            exit();
        }
    }
    
    /**
     * Edit user
     */
    public function editUser($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $country_id = !empty($_POST['country_id']) ? $_POST['country_id'] : null;
            $state_id = !empty($_POST['state_id']) ? $_POST['state_id'] : null;
            $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;
            $password = !empty($_POST['password']) ? $_POST['password'] : null;
            
            // Validate input
            if (empty($email) || empty($role)) {
                $_SESSION['error'] = "Email and role are required";
                header('Location: ' . URL . 'users');
                exit();
            }
            
            // Get current user data
            $currentUser = $this->model->getStaffById($id);
            
            // Check if email already exists for another user
            $existingUser = $this->model->getStaff($email);
            if ($existingUser && $existingUser->id != $id) {
                $_SESSION['error'] = "Email already exists for another user";
                header('Location: ' . URL . 'users');
                exit();
            }
            
            // Prepare update data
            $data = [
                'email' => $email,
                'role' => $role,
                'country_id' => $country_id,
                'state_id' => $state_id,
                'department_id' => $department_id
            ];
            
            $result = $this->model->updateStaff($id, $data);
            
            // Update password if provided
            if ($password && !empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $this->model->reset_password($email, $hashed_password);
            }
            
            if ($result) {
                $_SESSION['success'] = "User updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update user";
            }
            
            header('Location: ' . URL . 'users');
            exit();
        }
    }
    
    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        // Prevent deleting own account
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account";
            header('Location: ' . URL . 'users');
            exit();
        }
        
        // Check if user is super_admin trying to delete another super_admin
        $user = $this->model->getStaffById($id);
        if ($user && $user->role == 'super_admin' && $_SESSION['role'] != 'super_admin') {
            $_SESSION['error'] = "You cannot delete a super admin";
            header('Location: ' . URL . 'users');
            exit();
        }
        
        $result = $this->model->deleteStaff($id);
        
        if ($result) {
            $_SESSION['success'] = "User deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete user";
        }
        
        header('Location: ' . URL . 'users');
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
     * Get staff by ID (for editing)
     */
    public function getStaffById($id)
    {
        $user = $this->model->getStaffById($id);
        header('Content-Type: application/json');
        echo json_encode($user);
        exit();
    }

    /**
     * Get user profile for profile card
     */
    public function getUserProfile()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_email'])) {
            echo json_encode(['error' => 'Not logged in']);
            exit();
        }
        
        $email = $_SESSION['user_email'];
        $user = $this->model->getStaffWithLocation($email);
        
        if ($user) {
            // Get user name from email
            $name = explode('@', $user->email)[0];
            $name = ucfirst(str_replace(['.', '_', '-'], ' ', $name));
            
            echo json_encode([
                'name' => $name,
                'email' => $user->email,
                'role' => $user->role,
                'country' => $user->country_name ?? '-',
                'state' => $user->state_name ?? '-',
                'department' => $user->department_name ?? '-'
            ]);
        } else {
            echo json_encode(['error' => 'User not found']);
        }
        exit();
    }
}
?>