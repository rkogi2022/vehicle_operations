<?php
use Google\Client as Google_Client;
use Google\Service\Oauth2;

class Login extends Controller
{
   
    public function index(){
        $error_message = '';
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'invalid_credentials') {
                $error_message = '<div class="error-msg"> Invalid email or password </div>';
            } elseif ($_GET['error'] == 'user_not_found') {
                $error_message = '<div class="error-msg"> User not found. Please contact administrator. </div>';
            }
        }
        require APP . 'view/login/index.php';
    }

    public function loginUser() {
        session_start();
        
        if ($this->model === null) {
            die("Model not loaded properly!");
        }
        
        $email = $_POST["inputEmailAddress"];
        $password = $_POST["inputPassword"];
        
        // Debug log
        error_log("=== LOGIN ATTEMPT ===");
        error_log("Email: " . $email);
        error_log("Password entered: " . $password);
        
        // Get user using basic method
        $staff = $this->model->getStaff($email);
        
        if (!$staff) {
            error_log("User not found: " . $email);
            header('Location:' . URL . 'login/index?error=invalid_credentials');
            exit();
        }
        
        error_log("User found in database");
        error_log("Stored hash: " . $staff->password);
        
        if (empty($staff->password)) {
            error_log("Password field is empty in database");
            header('Location:' . URL . 'login/index?error=invalid_credentials');
            exit();
        }
        
        $is_correct_user = password_verify($password, $staff->password);
        error_log("Password verify result: " . ($is_correct_user ? "TRUE" : "FALSE"));
        
        if ($is_correct_user) {
            error_log("Login successful for: " . $email);
            
            // Get full user details with location for session data
            $staffWithLocation = $this->model->getStaffWithLocation($email);
            
            $_SESSION['user_email'] = $staff->email;
            $_SESSION['role'] = $staff->role;
            $_SESSION['user_id'] = $staff->id;
            
            // Store location information if available
            if ($staffWithLocation) {
                $_SESSION['country_id'] = $staffWithLocation->country_id;
                $_SESSION['country_name'] = $staffWithLocation->country_name;
                $_SESSION['country_code'] = $staffWithLocation->country_code;
                $_SESSION['state_id'] = $staffWithLocation->state_id;
                $_SESSION['state_name'] = $staffWithLocation->state_name;
                $_SESSION['state_code'] = $staffWithLocation->state_code;
                $_SESSION['department_id'] = $staffWithLocation->department_id;
                $_SESSION['department_name'] = $staffWithLocation->department_name;
            }
            
            $user_name = explode('@', $staff->email)[0];
            $_SESSION['user'] = ucfirst($user_name);
            
            error_log("Session created, redirecting to home");
            header('Location:' . URL . 'home/index/');
            exit();
        } else {
            error_log("Password verification FAILED for: " . $email);
            header('Location:' . URL . 'login/index?error=invalid_credentials');
            exit();
        }
    }
    
    public function password_reset() {
        $message = '';
        if (isset($_GET['error'])) {
            $message = '<div class="error-msg"> An error occurred, please try again.</div>';
        } elseif (isset($_GET['success'])) {
            $message = '<div class="success-msg"> Password reset successfully. Taking you back to login page...</div>';
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "' . URL . 'login/index/";
                    }, 1700); 
                  </script>';
        } elseif (isset($_GET['null'])) {
            $message = '<div class="error-msg"> No user found with the given email address.</div>';
        }
        require APP . 'view/login/forgot_password.php';
    }

    public function submit_password_reset() {
        if ($this->model === null) {
            die("Model not loaded properly!");
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['inputEmailAddress'];
            $newPassword1 = $_POST['newPassword1'];
            
            error_log("=== PASSWORD RESET ===");
            error_log("Email: " . $email);
            error_log("New password length: " . strlen($newPassword1));
            
            // First check if user exists using getStaff
            $user = $this->model->getStaff($email);
            if (!$user) {
                error_log("User not found for password reset: " . $email);
                header('Location:' . URL . 'login/password_reset?null');
                exit();
            }
            
            error_log("User found, hashing new password");
            $hashed_password = password_hash($newPassword1, PASSWORD_DEFAULT);
            error_log("New hash: " . $hashed_password);
            
            $update_query = $this->model->reset_password($email, $hashed_password);
            
            if ($update_query) {
                error_log("Password reset successful for: " . $email);
                
                // Verify the update worked
                $updatedUser = $this->model->getStaff($email);
                if (password_verify($newPassword1, $updatedUser->password)) {
                    error_log("VERIFICATION: Password works correctly!");
                } else {
                    error_log("VERIFICATION: Password verification FAILED!");
                }
                
                header('Location:' . URL . 'login/password_reset?success');
            } else {
                error_log("Password reset FAILED for: " . $email);
                header('Location:' . URL . 'login/password_reset?null');
            }
        } else {
            header('Location:' . URL . 'login/password_reset?error=invalid_credentials');
        }
    }

    public function logout(){
        session_start();
        session_unset();
        session_destroy();
        header('Location:' . URL . 'login/index/');
    }

    public function loginWithGoogle() 
    {
        session_start();
        require_once __DIR__ . '/../../vendor/autoload.php';

        $client = new Google_Client();
        $client->setClientId('1031515607275-kdrn3ahaoomji57i16137091u0sfc082.apps.googleusercontent.com');
        $client->setClientSecret('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');
        $client->setRedirectUri('https://vehiclerequest.evidenceaction.org/login/googleCallback');
        $client->addScope('email');
        $client->addScope('profile');
        $client->setPrompt('select_account');

        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit();
    }

    public function googleCallback() 
    {
        session_start();
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../config/config.php';

        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }

        try {
            $client = new Google_Client();
            $client->setClientId('1031515607275-kdrn3ahaoomji57i16137091u0sfc082.apps.googleusercontent.com');
            $client->setClientSecret('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');
            $client->setRedirectUri('https://vehiclerequest.evidenceaction.org/login/googleCallback');

            if (!isset($_GET['code'])) {
                throw new Exception("Authorization code not found in callback.");
            }

            $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            if (!is_array($accessToken) || isset($accessToken['error'])) {
                error_log("Access token fetch error: " . json_encode($accessToken));
                $errorMsg = $accessToken['error_description'] ?? $accessToken['error'] ?? 'Unknown error';
                throw new Exception("Access token error: " . $errorMsg);
            }

            $client->setAccessToken($accessToken['access_token']);
            $oauth2 = new \Google\Service\Oauth2($client);
            $googleUser = $oauth2->userinfo->get();

            if (!isset($googleUser->email)) {
                throw new Exception("Unable to retrieve Google account email.");
            }

            $email = $googleUser->email;
            
            // Use getStaff for authentication check
            $staff = $this->model->getStaff($email);
            
            if ($staff) {
                $_SESSION['user_email'] = $staff->email;
                $_SESSION['role'] = $staff->role;
                $_SESSION['user_id'] = $staff->id;
                
                // Get full details with location
                $staffWithLocation = $this->model->getStaffWithLocation($email);
                if ($staffWithLocation) {
                    $_SESSION['country_id'] = $staffWithLocation->country_id;
                    $_SESSION['country_name'] = $staffWithLocation->country_name;
                    $_SESSION['country_code'] = $staffWithLocation->country_code;
                    $_SESSION['state_id'] = $staffWithLocation->state_id;
                    $_SESSION['state_name'] = $staffWithLocation->state_name;
                    $_SESSION['state_code'] = $staffWithLocation->state_code;
                }
                $_SESSION['user'] = ucfirst(explode('@', $staff->email)[0]);

                header('Location: ' . URL . 'home/index/');
                exit();
            } else {
                header('Location: ' . URL . 'login/index?error=user_not_found');
                exit();
            }
        } catch (Exception $e) {
            error_log("Google Login Error: " . $e->getMessage());
            header('Location: ' . URL . 'login/index?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}
?>