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
     * Download CSV template for bulk user import
     */
    public function downloadTemplate()
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="user_import_template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['email', 'password', 'role', 'country', 'state', 'department']);
        fputcsv($out, ['jane.doe@evidenceaction.org', 'Welcome@123', 'staff', 'Nigeria', 'Lagos', 'Finance']);
        fclose($out);
        exit();
    }

    /**
     * Bulk import users from uploaded CSV file
     */
    public function bulkImport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['import_file'])) {
            header('Location: ' . URL . 'users');
            exit();
        }

        $file = $_FILES['import_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "File upload failed (error code " . $file['error'] . ").";
            header('Location: ' . URL . 'users');
            exit();
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $_SESSION['error'] = "Only CSV files are accepted. Save your Excel file as CSV first.";
            header('Location: ' . URL . 'users');
            exit();
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $_SESSION['error'] = "Could not read the uploaded file.";
            header('Location: ' . URL . 'users');
            exit();
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];
        $rowNum   = 0;
        $headers  = null;
        $validRoles = ['staff', 'admin', 'super_admin'];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            if ($rowNum === 1) {
                $headers = array_map('trim', array_map('strtolower', $row));
                continue;
            }

            if (empty(array_filter($row))) continue;

            $data = [];
            foreach ($headers as $i => $h) {
                $data[$h] = isset($row[$i]) ? trim($row[$i]) : '';
            }

            $email      = strtolower($data['email'] ?? '');
            $password   = $data['password'] ?? '';
            $role       = strtolower($data['role'] ?? 'staff');
            $countryName = $data['country'] ?? '';
            $stateName   = $data['state'] ?? '';
            $deptName    = $data['department'] ?? '';

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row $rowNum: Invalid or missing email.";
                continue;
            }

            if ($this->model->emailExists($email)) {
                $skipped++;
                continue;
            }

            if (!in_array($role, $validRoles)) {
                $errors[] = "Row $rowNum ($email): Invalid role '$role'. Use: staff, admin, or super_admin.";
                continue;
            }

            if (empty($password)) {
                $errors[] = "Row $rowNum ($email): Password is required.";
                continue;
            }

            $country_id = null;
            if (!empty($countryName)) {
                $country = $this->model->getCountryByName($countryName);
                if (!$country) {
                    $errors[] = "Row $rowNum ($email): Country '$countryName' not found.";
                    continue;
                }
                $country_id = $country->id;
            }

            $state_id = null;
            if (!empty($stateName)) {
                $state = $this->model->getStateByName($stateName, $country_id);
                if (!$state) {
                    $label = $countryName ? "$stateName in $countryName" : $stateName;
                    $errors[] = "Row $rowNum ($email): State '$label' not found.";
                    continue;
                }
                $state_id = $state->id;
            }

            $dept_id = null;
            if (!empty($deptName)) {
                $dept = $this->model->getDepartmentByName($deptName);
                if (!$dept) {
                    $errors[] = "Row $rowNum ($email): Department '$deptName' not found.";
                    continue;
                }
                $dept_id = $dept->id;
            }

            $result = $this->model->createStaff([
                'email'         => $email,
                'password'      => password_hash($password, PASSWORD_DEFAULT),
                'role'          => $role,
                'country_id'    => $country_id,
                'state_id'      => $state_id,
                'department_id' => $dept_id,
            ]);

            if ($result) {
                $imported++;
            } else {
                $errors[] = "Row $rowNum ($email): Database insert failed.";
            }
        }

        fclose($handle);

        $_SESSION['import_results'] = [
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];

        header('Location: ' . URL . 'users');
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