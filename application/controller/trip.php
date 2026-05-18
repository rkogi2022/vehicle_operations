<?php
/**
 * Trip Controller - Handles vehicle operations
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class trip extends Controller
{
    private $operations_email = 'rita.kogi@evidenceaction.org'; // Change to your operations email
    
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header('Location: ' . URL . 'login/index');
            exit();
        }
    }
    
    /**
     * Helper function to get name from email
     */
    private function getNameFromEmail($email)
    {
        if (empty($email)) return 'N/A';
        $name = explode('@', $email)[0];
        return ucfirst(str_replace(['.', '_', '-'], ' ', $name));
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendEmail($to, $subject, $body, $cc = null)
    {
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'information.systems@evidenceaction.org';
            $mail->Password   = 'rtnbqnbajjhcifbr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            $mail->setFrom('information.systems@evidenceaction.org', 'Vehicle Operations');
            $mail->addAddress($to);
            
            if ($cc) {
                $mail->addCC($cc);
            }
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            error_log("Email sent successfully to: " . $to);
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Show create trip request form
     */
    public function index()
    {
        // Get current user with department
        $user = $this->model->getUserWithDepartment($_SESSION['user_email']);
        
        // Check if user exists
        if (!$user) {
            $_SESSION['error'] = "User not found. Please login again.";
            header('Location: ' . URL . 'login/logout');
            exit();
        }
        
        // Get user's states based on their country
        $user_states = $this->model->getUserStates($user->id);
        
        // Get approved supervisors (users with admin or super_admin role)
        $supervisors = $this->model->getStaffByRole('admin');
        $supervisors = array_merge($supervisors, $this->model->getStaffByRole('super_admin'));
        
        // Get reviewers (users with admin or super_admin role)
        $reviewers = $this->model->getStaffByRole('admin');
        $co_reviewers = $this->model->getStaffByRole('admin');
        $managers = $this->model->getStaffByRole('super_admin');
        
        // Get user's trip requests
        $trips = $this->model->getUserTripRequests($user->id);
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/my_requests.php';
        require APP . 'view/_templates/footer.php';
    }
    
    /**
     * Show create trip request form
     */
    public function create()
    {
        // If it's a POST request, save the data
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->store();
        }
        
        // Get current user with department
        $user = $this->model->getUserWithDepartment($_SESSION['user_email']);
        
        // Check if user exists
        if (!$user) {
            $_SESSION['error'] = "User not found. Please login again.";
            header('Location: ' . URL . 'login/logout');
            exit();
        }
        
        // Get user's states based on their country
        $user_states = $this->model->getUserStates($user->id);
        
        // Get approved supervisors (users with admin or super_admin role)
        $supervisors_admin = $this->model->getStaffByRole('admin');
        $supervisors_super = $this->model->getStaffByRole('super_admin');
        $supervisors = array_merge($supervisors_admin, $supervisors_super);
        
        // Get reviewers (users with admin or super_admin role)
        $reviewers_admin = $this->model->getStaffByRole('admin');
        $reviewers_super = $this->model->getStaffByRole('super_admin');
        $reviewers = array_merge($reviewers_admin, $reviewers_super);
        
        // Get co-reviewers (users with admin or super_admin role)
        $co_reviewers_admin = $this->model->getStaffByRole('admin');
        $co_reviewers_super = $this->model->getStaffByRole('super_admin');
        $co_reviewers = array_merge($co_reviewers_admin, $co_reviewers_super);
        
        // Get managers (users with super_admin role)
        $managers = $this->model->getStaffByRole('super_admin');
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/createtrip.php';
        require APP . 'view/_templates/footer.php';
    }

    /**
     * Save trip request
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'trip');
            exit();
        }
        
        // Get current user
        $user = $this->model->getUserWithDepartment($_SESSION['user_email']);
        
        if (!$user) {
            $_SESSION['error'] = "User not found";
            header('Location: ' . URL . 'trip/create');
            exit();
        }
        
        $token = bin2hex(random_bytes(32));
        
        $data = [
            ':requester_id' => $user->id,
            ':department_id' => $user->department_id,
            ':trip_type' => $_POST['trip_type'],
            ':trip_destination' => $_POST['trip_destination'],
            ':purpose' => $_POST['purpose'],
            ':departure_date' => $_POST['departure_date'],
            ':departure_time' => $_POST['departure_time'],
            ':vehicle_departure_location' => !empty($_POST['vehicle_departure_location']) ? $_POST['vehicle_departure_location'] : null,
            ':vehicle_destination_location' => !empty($_POST['vehicle_destination_location']) ? $_POST['vehicle_destination_location'] : null,
            ':return_date' => !empty($_POST['return_date']) ? $_POST['return_date'] : null,
            ':need_driver' => isset($_POST['need_driver']) ? 1 : 0,
            ':driver_overtime' => isset($_POST['driver_overtime']) ? 1 : 0,
            ':approved_supervisor_id' => $_POST['approved_supervisor_id'],
            ':reviewer_id' => $_POST['reviewer_id'],
            ':co_reviewer_id' => !empty($_POST['co_reviewer_id']) ? $_POST['co_reviewer_id'] : null,
            ':manager_id' => !empty($_POST['manager_id']) ? $_POST['manager_id'] : null,
            ':approval_token' => $token
        ];
        
        $result = $this->model->createTripRequest($data);
        
        if ($result) {
            $trip = $this->model->getTripByToken($token);
            if ($trip) {
                // Send approval email to supervisor
                $this->sendSupervisorApprovalEmail($trip);
            }
            $_SESSION['success'] = "Trip request created! An approval email has been sent to your supervisor.";
            header('Location: ' . URL . 'trip/myrequests');
        } else {
            $_SESSION['error'] = "Failed to create trip request.";
            header('Location: ' . URL . 'trip/create');
        }
        exit();
    }
    
    /**
     * Send approval request email to supervisor
     */
    private function sendSupervisorApprovalEmail($trip)
    {
        $staffName = $this->getNameFromEmail($trip->requester_email);
        $supervisor = $this->model->getStaffById($trip->approved_supervisor_id);
        
        if (!$supervisor) {
            error_log("Supervisor not found for ID: " . $trip->approved_supervisor_id);
            return;
        }
        
        $subject = "Vehicle Request Approval Needed - " . $staffName;
        $body = $this->getSupervisorEmailTemplate($trip, $staffName);
        
        $this->sendEmail($supervisor->email, $subject, $body);
    }
    
    /**
     * Send final status email to Operations and CC requester
     */
    private function sendStatusUpdateEmail($trip, $status)
    {
        $staffName = $this->getNameFromEmail($trip->requester_email);
        $subject = "Vehicle Request " . strtoupper($status) . " - " . $staffName;
        
        $body = $this->getOperationsEmailTemplate($trip, $staffName, $status);
        
        // Send to Operations email with CC to requester
        $this->sendEmail($this->operations_email, $subject, $body, $trip->requester_email);
    }
    
    /**
     * Supervisor email template (with approval links)
     */
    private function getSupervisorEmailTemplate($trip, $staffName)
    {
        $approveUrl = URL . "trip/approve/" . $trip->approval_token;
        $declineUrl = URL . "trip/decline/" . $trip->approval_token;
        $cancelUrl = URL . "trip/cancel/" . $trip->approval_token;
        
        $driverOvertimeWarning = '';
        if ($trip->driver_overtime) {
            $driverOvertimeWarning = '<p style="color: red; font-weight: bold;">⚠ An approval email from Mr. Tope is required for any driver overtime bookings.</p>';
        }
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #007bff; }
                .button { display: inline-block; padding: 10px 20px; margin: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .approve { background: #28a745; color: white; }
                .decline { background: #dc3545; color: white; }
                .cancel { background: #ffc107; color: black; }
                .warning { color: red; font-weight: bold; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Vehicle Request Approval Required</h2>
                </div>
                <div class="content">
                    <p>Hi,</p>
                    <p>I hope this message finds you well.</p>
                    
                    ' . $driverOvertimeWarning . '
                    
                    <p>Your supervisee, <strong>' . htmlspecialchars($staffName) . '</strong>, has submitted a vehicle request with the following details:</p>
                    
                    <div class="details">
                        <p><strong>Email:</strong> ' . htmlspecialchars($trip->requester_email) . '</p>
                        <p><strong>Purpose of trip:</strong> ' . nl2br(htmlspecialchars($trip->purpose)) . '</p>
                        <p><strong>Trip destination:</strong> ' . htmlspecialchars($trip->trip_destination) . '</p>
                        <p><strong>Date of trip:</strong> ' . date('F j, Y', strtotime($trip->departure_date)) . '</p>
                        <p><strong>Time:</strong> ' . $trip->departure_time . '</p>
                        ' . ($trip->return_date ? '<p><strong>Return Date:</strong> ' . date('F j, Y', strtotime($trip->return_date)) . '</p>' : '') . '
                        ' . ($trip->need_driver ? '<p><strong>Driver Required:</strong> Yes</p>' : '') . '
                    </div>
                    
                    <p><strong>Kindly review the request and confirm if you approve this trip.</strong></p>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . $approveUrl . '" class="button approve">✓ APPROVE</a>
                        <a href="' . $declineUrl . '" class="button decline">✗ DECLINE</a>
                        <a href="' . $cancelUrl . '" class="button cancel">⟳ CANCEL</a>
                    </div>
                    
                    <p><small>Click one of the buttons above to take action. You can also login to the portal for more details.</small></p>
                </div>
                <div class="footer">
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Operations email template (after status update)
     */
    private function getOperationsEmailTemplate($trip, $staffName, $status)
    {
        $statusColor = $status == 'approved' ? '#28a745' : ($status == 'declined' ? '#dc3545' : '#ffc107');
        $statusText = strtoupper($status);
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: ' . $statusColor . '; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid ' . $statusColor . '; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Vehicle Request ' . $statusText . '</h2>
                </div>
                <div class="content">
                    <p>Dear Operations,</p>
                    
                    <p>The vehicle request for <strong>' . htmlspecialchars($staffName) . '</strong> has been <strong>' . $statusText . '</strong>.</p>
                    
                    <div class="details">
                        <p><strong>Purpose of trip:</strong> ' . nl2br(htmlspecialchars($trip->purpose)) . '</p>
                        <p><strong>Trip destination:</strong> ' . htmlspecialchars($trip->trip_destination) . '</p>
                        <p><strong>Date of trip:</strong> ' . date('F j, Y', strtotime($trip->departure_date)) . '</p>
                        <p><strong>Time:</strong> ' . $trip->departure_time . '</p>
                        ' . ($trip->return_date ? '<p><strong>Return Date:</strong> ' . date('F j, Y', strtotime($trip->return_date)) . '</p>' : '') . '
                        ' . ($trip->need_driver ? '<p><strong>Driver pick-up time:</strong> As per request schedule</p>' : '') . '
                    </div>
                    
                    <p>Please make necessary arrangements.</p>
                    
                    <p>Thanks,</p>
                    <p>Vehicle Operations System</p>
                </div>
                <div class="footer">
                    <p>This is an automated message from Vehicle Operations System.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Approve via email
     */
    public function approve($token)
    {
        $trip = $this->model->getTripByToken($token);
        
        if (!$trip) {
            die("Invalid or expired approval link.");
        }
        
        $this->model->updateTripStatus($trip->id, 'approved', "Approved via email on " . date('Y-m-d H:i:s'));
        
        // Send email to Operations and CC requester
        $this->sendStatusUpdateEmail($trip, 'approved');
        
        $this->showResultPage('approved', $trip);
        exit();
    }
    
    /**
     * Decline via email
     */
    public function decline($token)
    {
        $trip = $this->model->getTripByToken($token);
        
        if (!$trip) {
            die("Invalid or expired approval link.");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showDeclineForm($trip);
            exit();
        }
        
        $reason = $_POST['reason'] ?? "No reason provided";
        $this->model->updateTripStatus($trip->id, 'declined', $reason);
        
        // Send email to Operations and CC requester
        $this->sendStatusUpdateEmail($trip, 'declined');
        
        $this->showResultPage('declined', $trip, $reason);
        exit();
    }
    
    /**
     * Cancel via email
     */
    public function cancel($token)
    {
        $trip = $this->model->getTripByToken($token);
        
        if (!$trip) {
            die("Invalid or expired link.");
        }
        
        $this->model->updateTripStatus($trip->id, 'cancelled', "Cancelled via email by supervisor");
        
        // Send email to Operations and CC requester
        $this->sendStatusUpdateEmail($trip, 'cancelled');
        
        $this->showResultPage('cancelled', $trip);
        exit();
    }
    
    /**
     * My trip requests
     */
    public function myrequests()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_email'])) {
            $_SESSION['error'] = "Please login first";
            header('Location: ' . URL . 'login');
            exit();
        }
        
        $user = $this->model->getUserWithDepartment($_SESSION['user_email']);
        
        if (!$user) {
            $_SESSION['error'] = "User not found";
            header('Location: ' . URL . 'home');
            exit();
        }
        
        $trips = $this->model->getUserTripRequests($user->id);
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/my_requests.php';
        require APP . 'view/_templates/footer.php';
    }
    
    /**
     * Get trip details for AJAX view
     */
    public function getTripDetails($id)
    {
        header('Content-Type: application/json');
        
        $sql = "SELECT t.*,
                req.email as requester_email,
                sup.email as supervisor_email,
                rev.email as reviewer_email,
                co_rev.email as co_reviewer_email,
                mgr.email as manager_email,
                dep.name as department_name,
                dep_loc.name as departure_location_name,
                dest_loc.name as destination_location_name
                FROM trip_requests t
                LEFT JOIN staff_login req ON t.requester_id = req.id
                LEFT JOIN staff_login sup ON t.approved_supervisor_id = sup.id
                LEFT JOIN staff_login rev ON t.reviewer_id = rev.id
                LEFT JOIN staff_login co_rev ON t.co_reviewer_id = co_rev.id
                LEFT JOIN staff_login mgr ON t.manager_id = mgr.id
                LEFT JOIN departments dep ON t.department_id = dep.id
                LEFT JOIN state dep_loc ON t.vehicle_departure_location = dep_loc.id
                LEFT JOIN state dest_loc ON t.vehicle_destination_location = dest_loc.id
                WHERE t.id = :id";
        
        $stmt = $this->model->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $trip = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($trip) {
            echo json_encode(['success' => true, 'data' => $trip]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Trip not found']);
        }
    }
    
    /**
     * Cancel request (user cancels their own pending request)
     */
    public function cancelRequest($id)
    {
        $user = $this->model->getStaff($_SESSION['user_email']);
        
        if (!$user) {
            $_SESSION['error'] = "User not found";
            header('Location: ' . URL . 'login/logout');
            exit();
        }
        
        // Check if trip belongs to user and is pending
        $sql = "SELECT * FROM trip_requests WHERE id = :id AND requester_id = :user_id AND status = 'pending'";
        $stmt = $this->model->db->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $user->id]);
        $trip = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$trip) {
            $_SESSION['error'] = "Cannot cancel this request";
            header('Location: ' . URL . 'trip');
            exit();
        }
        
        // Update status
        $sql = "UPDATE trip_requests SET status = 'cancelled', comments = 'Cancelled by requester' WHERE id = :id";
        $stmt = $this->model->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $_SESSION['success'] = "Trip request cancelled successfully";
        header('Location: ' . URL . 'trip/myrequests');
        exit();
    }
    
    /**
     * Show decline form
     */
    private function showDeclineForm($trip)
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Decline Trip Request</title>
            <style>
                body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
                .form-group { margin-bottom: 15px; }
                label { display: block; margin-bottom: 5px; font-weight: bold; }
                textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
                button { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
            </style>
        </head>
        <body>
            <h2>Decline Trip Request</h2>
            <p>Trip to: <strong><?= htmlspecialchars($trip->trip_destination ?? 'N/A') ?></strong></p>
            <form method="POST">
                <div class="form-group">
                    <label>Reason for declining (optional):</label>
                    <textarea name="reason" rows="4" placeholder="Enter reason for declining..."></textarea>
                </div>
                <button type="submit">Confirm Decline</button>
                <a href="<?= URL ?>trip" style="margin-left: 10px; color: #666;">Cancel</a>
            </form>
        </body>
        </html>
        <?php
    }
    
    /**
     * Show result page
     */
    private function showResultPage($action, $trip, $reason = null)
    {
        $color = $action == 'approved' ? '#28a745' : ($action == 'declined' ? '#dc3545' : '#ffc107');
        $icon = $action == 'approved' ? '✓' : ($action == 'declined' ? '✗' : '⟳');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Trip <?= ucfirst($action) ?></title>
            <style>
                body { font-family: Arial; text-align: center; padding: 50px; }
                .message { max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 10px; background: #f8f9fa; }
                h2 { color: <?= $color ?>; }
            </style>
        </head>
        <body>
            <div class="message">
                <h2><?= $icon ?> Trip Request <?= ucfirst($action) ?></h2>
                <p>Trip to <strong><?= htmlspecialchars($trip->trip_destination ?? 'N/A') ?></strong> has been <?= $action ?>.</p>
                <?php if($reason): ?>
                <p><strong>Reason:</strong> <?= htmlspecialchars($reason) ?></p>
                <?php endif; ?>
                <p>Operations have been notified.</p>
                <a href="<?= URL ?>trip" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">View Requests</a>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Supervisor dashboard - View requests assigned to this supervisor for approval
     */
    public function supervisorDashboard()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_email'])) {
            $_SESSION['error'] = "Please login first";
            header('Location: ' . URL . 'login');
            exit();
        }
        
        $user = $this->model->getUserWithDepartment($_SESSION['user_email']);
        
        if (!$user) {
            $_SESSION['error'] = "User not found";
            header('Location: ' . URL . 'home');
            exit();
        }
        
        // Get all requests where this user is the approved supervisor
        $pendingRequests = $this->model->getRequestsBySupervisor($user->id, 'pending');
        $approvedRequests = $this->model->getRequestsBySupervisor($user->id, 'approved');
        $declinedRequests = $this->model->getRequestsBySupervisor($user->id, 'declined');
        $cancelledRequests = $this->model->getRequestsBySupervisor($user->id, 'cancelled');
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/approvals/supervisor_dashboard.php';
        require APP . 'view/_templates/footer.php';
    }

    /**
     * Operations dashboard - View all requests from all supervisors
     */
    public function operationsDashboard()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_email'])) {
            $_SESSION['error'] = "Please login first";
            header('Location: ' . URL . 'login');
            exit();
        }
        
        $user = $this->model->getUserWithDepartment($_SESSION['user_email']);
        
        if (!$user) {
            $_SESSION['error'] = "User not found";
            header('Location: ' . URL . 'home');
            exit();
        }
        
        // Check if user has operations role (admin or super_admin)
        if ($user->role != 'admin' && $user->role != 'super_admin') {
            $_SESSION['error'] = "Access denied. Operations dashboard is for administrators only.";
            header('Location: ' . URL . 'home');
            exit();
        }
        
        // Get all requests with their supervisor info
        $allRequests = $this->model->getAllTripRequests();
        $pendingRequests = $this->model->getAllTripRequestsByStatus('pending');
        $approvedRequests = $this->model->getAllTripRequestsByStatus('approved');
        $declinedRequests = $this->model->getAllTripRequestsByStatus('declined');
        $cancelledRequests = $this->model->getAllTripRequestsByStatus('cancelled');
        
        // Get statistics
        $stats = [
            'total' => count($allRequests),
            'pending' => count($pendingRequests),
            'approved' => count($approvedRequests),
            'declined' => count($declinedRequests),
            'cancelled' => count($cancelledRequests)
        ];
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/approvals/operations_dashboard.php';
        require APP . 'view/_templates/footer.php';
    }


}
?>