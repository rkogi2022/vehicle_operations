<?php
/**
 * Intrastate Controller
 * Handles all intrastate trip request operations including supervisor & operations dashboards
 */

// Load PHPMailer at the top of the file, outside the class
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class intrastate extends Controller
{
    
    public function __construct()
    {
        parent::__construct();
        // Check if user is logged in - only for web interface, not for email links
        if (PHP_SAPI !== 'cli' && !isset($_SESSION)) {
            session_start();
        }
    }
    
    /**
     * Default action - show my requests (requires login)
     */
    public function index()
    {
        $this->requireLogin();
        $this->myrequests();
    }
    
    /**
     * Display all requests for the logged in user (both intrastate and interstate)
     */
    public function myrequests()
    {
        $this->requireLogin();
        $user_email = $_SESSION['user_email'];

        $intrastate = $this->model->getIntrastateRequestsByStaff($user_email);
        foreach ($intrastate as $r) { $r->request_type = 'intrastate'; $r->is_passenger = false; }

        $interstate = $this->model->getInterstateRequestsByStaff($user_email);
        foreach ($interstate as $r) { $r->request_type = 'interstate'; $r->is_passenger = false; }

        // Trips booked on behalf of this user by someone else
        $passengerTrips = $this->model->getIntrastateRequestsAsPassenger($user_email);
        foreach ($passengerTrips as $r) { $r->request_type = 'intrastate'; $r->is_passenger = true; }

        $requests = array_merge($intrastate, $interstate, $passengerTrips);
        usort($requests, fn($a, $b) => strtotime($b->created_at) - strtotime($a->created_at));

        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/index.php';
    }
    
    /**
     * Supervisor Dashboard - View all supervisee requests
     * Shows requests where logged-in user is the supervisor
     */
    public function superviseeRequests()
    {
        $this->requireLogin();
        $user_email = $_SESSION['user_email'];
        
        // Get all requests where this user is the supervisor
        $requests = $this->model->getIntrastateRequestsBySupervisor($user_email);
        
        // Get statistics for dashboard
        $stats = [
            'pending_count' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'total_count' => 0
        ];
        
        foreach ($requests as $req) {
            if ($req->status == 'pending') $stats['pending_count']++;
            elseif ($req->status == 'security_approved') $stats['approved_count']++;
            elseif ($req->status == 'rejected') $stats['rejected_count']++;
            $stats['total_count']++;
        }
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/supervisee_requests.php';
    }
    
    /**
     * All Trips — combined intrastate + interstate for operations overview
     */
    public function allTrips()
    {
        $this->requireLogin();

        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';

        $isOperations = ($role == 'admin' || $role == 'super_admin') || $this->model->isUserOperationsTeam($user_email);
        if (!$isOperations) {
            $_SESSION['error'] = "You don't have permission to view all trips.";
            header('Location: ' . URL . 'home');
            exit();
        }

        $intrastate = $this->model->getAllIntrastateRequests();
        foreach ($intrastate as $r) { $r->request_type = 'intrastate'; }

        $interstate = $this->model->getAllInterstateRequests();
        foreach ($interstate as $r) { $r->request_type = 'interstate'; }

        $allTrips = array_merge($intrastate, $interstate);
        usort($allTrips, fn($a, $b) => strtotime($b->created_at) - strtotime($a->created_at));

        require APP . 'view/_templates/header.php';
        require APP . 'view/approvals/all_trips.php';
    }

    /**
     * Operations Dashboard - View all approved requests awaiting driver assignment
     * Also shows requests that need to be marked as completed
     */
    public function operationsDashboard()
    {
        $this->requireLogin();
        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';
        
        // Check if user has operations privileges (reviewer, co-reviewer, manager, admin)
        $isOperations = ($role == 'admin' || $role == 'super_admin');
        
        if (!$isOperations) {
            // Check if user is a reviewer/manager for any state
            $isOperations = $this->model->isUserOperationsTeam($user_email);
        }
        
        if (!$isOperations) {
            $_SESSION['error'] = "You don't have permission to access the operations dashboard.";
            header('Location: ' . URL . 'home');
            exit();
        }
        
        // Get requests awaiting driver assignment (security_approved with no driver assigned)
        $pendingDriverRequests = $this->model->getIntrastateRequestsAwaitingDriver();
        
        // Get requests with driver assigned but not yet completed
        $pendingCompletionRequests = $this->model->getIntrastateRequestsInProgress();
        
        // Get all available drivers for assignment
        $availableDrivers = $this->model->getAllAvailableDrivers();
        
        // Get statistics
        $stats = [
            'awaiting_driver' => count($pendingDriverRequests),
            'in_progress' => count($pendingCompletionRequests),
            'completed_today' => $this->model->getCompletedRequestsCountToday()
        ];
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/approvals/intrastateapproval.php';
    }
    
    /**
     * Edit an intrastate request (allowed only before supervisor approves)
     */
    public function edit($id)
    {
        $this->requireLogin();

        $request = $this->model->getIntrastateRequestById($id);

        if (!$request) {
            $_SESSION['error'] = "Request not found";
            header('Location: ' . URL . 'intrastate/myrequests');
            exit();
        }

        if ($request->staff_email != $_SESSION['user_email']) {
            $_SESSION['error'] = "You cannot edit this request";
            header('Location: ' . URL . 'intrastate/myrequests');
            exit();
        }

        $canEdit = ($request->status == 'draft') ||
                   ($request->status == 'pending' && $request->current_approval_level == 'reviewer');

        if (!$canEdit) {
            $_SESSION['error'] = "This request cannot be edited — it has already been approved by your supervisor.";
            header('Location: ' . URL . 'intrastate/myrequests');
            exit();
        }

        $eaStates        = $this->model->getAllEaStatesForDropdown();
        $supervisors     = $this->model->getAdminSupervisorEmails();
        $funderCodes     = $this->model->getAllFunderCodesForDropdown();
        $overtimeManagers = $this->model->getAdminSupervisorEmails();
        $allStaff        = $this->model->getAllStaffEmails();

        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/intrastate_request.php';
    }

    /**
     * Display form to create new intrastate request
     */
    public function create()
    {
        $this->requireLogin();
        $user_email = $_SESSION['user_email'];
        $user_profile = $this->model->getUserProfileByEmail($user_email);
        
        // Get dropdown data
        $eaStates        = $this->model->getAllEaStatesForDropdown();
        $supervisors     = $this->model->getAdminSupervisorEmails();
        $funderCodes     = $this->model->getAllFunderCodesForDropdown();
        $overtimeManagers = $this->model->getAdminSupervisorEmails();
        $allStaff        = $this->model->getAllStaffEmails();

        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/intrastate_request.php';
    }
    
    /**
     * Save intrastate request (as draft or submit)
     */
    public function save()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_email = $_SESSION['user_email'];
            $action = $_POST['action'] ?? 'submit';
            
            // Get vehicle location state and auto-populate approvers
            $vehicle_location_state_id = $_POST['vehicle_location_state_id'];
            $eaStateConfig = $this->model->getEaStateConfigByStateId($vehicle_location_state_id);

            // Fall back to country-level default approvers
            if (!$eaStateConfig) {
                $eaStateConfig = $this->model->getCountryDefaultApprovers($_SESSION['country_id'] ?? 0);
            }

            if (!$eaStateConfig && $action == 'submit') {
                $_SESSION['error'] = "No approver configuration found for this state. Please contact administrator.";
                header('Location: ' . URL . 'intrastate/create');
                exit();
            }
            
            $trip_date    = $_POST['trip_date'];

            // Prepare data
            $data = [
                'staff_email' => $user_email,
                'staff_phone' => trim($_POST['staff_phone']),
                'supervisor_email' => trim($_POST['supervisor_email']),
                'vehicle_location_state_id' => $vehicle_location_state_id,
                'reviewer_email' => $eaStateConfig ? $eaStateConfig->reviewer_email : null,
                'co_reviewer_email' => $eaStateConfig ? $eaStateConfig->co_reviewer_email : null,
                'manager_email' => $eaStateConfig ? $eaStateConfig->manager_email : null,
                'security_manager_email' => $eaStateConfig ? $eaStateConfig->security_manager_email : null,
                'trip_date' => $trip_date,
                'purpose' => trim($_POST['purpose']),
                'pickup_location' => trim($_POST['pickup_location']),
                'trip_destination' => trim($_POST['trip_destination']),
                'trip_destination_time' => $_POST['trip_destination_time'],
                'route_information' => trim($_POST['route_information']),
                'funder_code_id' => $_POST['funder_code_id'],
                'driver_overtime' => $_POST['driver_overtime'] ?? 'no',
                'trip_activity' => isset($_POST['driver_overtime']) && $_POST['driver_overtime'] == 'yes' ? trim($_POST['trip_activity']) : null,
                'reason_for_overtime' => isset($_POST['driver_overtime']) && $_POST['driver_overtime'] == 'yes' ? trim($_POST['reason_for_overtime']) : null,
                'overtime_manager_email' => isset($_POST['driver_overtime']) && $_POST['driver_overtime'] == 'yes' ? trim($_POST['overtime_manager_email']) : null,
                'need_driver_pickup' => $_POST['need_driver_pickup'] ?? 'no',
                'pickup_time' => isset($_POST['need_driver_pickup']) && $_POST['need_driver_pickup'] == 'yes' ? $_POST['pickup_time'] : null,
                'id' => $_POST['id'] ?? null
            ];
            
            $existingId = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $existingStatus = $_POST['status'] ?? null;

            if ($action == 'draft') {
                if ($existingId) {
                    $result = $this->model->updateIntrastateRequest($existingId, $data);
                } else {
                    $result = $this->model->saveIntrastateRequestDraft($data);
                }
                $message = "Draft saved successfully";

                if ($result) {
                    $_SESSION['success'] = $message;
                } else {
                    $_SESSION['error'] = "Failed to save draft";
                }

                header('Location: ' . URL . 'intrastate/myrequests');
                exit();
            } elseif ($existingId && $existingStatus == 'pending') {
                // Re-submit an already-pending trip (supervisor hasn't approved yet)
                $result = $this->model->updatePendingIntrastateRequest($existingId, $data);

                if ($result) {
                    $request = $this->model->getIntrastateRequestById($existingId);
                    if ($request && $request->supervisor_email) {
                        $this->sendSupervisorApprovalEmail($request, true);
                    }
                    $_SESSION['success'] = "Request updated. Your supervisor has been notified of the changes.";
                } else {
                    $_SESSION['error'] = "Failed to update request. It may have already been approved.";
                }

                header('Location: ' . URL . 'intrastate/myrequests');
                exit();
            } else {
                // Guard against double-submit (same staff + date + destination within 60s)
                if ($this->model->isDuplicateIntrastateRequest($user_email, $data['trip_date'], $data['trip_destination'])) {
                    $_SESSION['error'] = "A duplicate request was detected. Please check your existing requests before submitting again.";
                    header('Location: ' . URL . 'intrastate/myrequests');
                    exit();
                }

                // Submit for approval - create request and send email to supervisor
                $requestId = $this->model->createIntrastateRequest($data);

                if ($requestId) {
                    // Get the created request
                    $request = $this->model->getIntrastateRequestById($requestId);

                    // Send email to supervisor
                    if ($request && $request->supervisor_email) {
                        $this->sendSupervisorApprovalEmail($request);
                    }

                    // Save and notify passengers
                    $rawPassengers = $_POST['passenger_emails'] ?? [];
                    $passengerEmails = array_values(array_unique(array_filter(
                        array_map('trim', $rawPassengers),
                        fn($e) => !empty($e) && $e !== $user_email
                    )));
                    if (!empty($passengerEmails) && $request) {
                        $this->model->addTripPassengers($requestId, $passengerEmails, $user_email);
                        foreach ($passengerEmails as $passengerEmail) {
                            $this->sendPassengerNotificationEmail($request, $passengerEmail, $user_email);
                        }
                    }

                    $passengerCount = count($passengerEmails);
                    $_SESSION['success'] = "Request submitted successfully. Your supervisor has been notified."
                        . ($passengerCount > 0 ? " {$passengerCount} passenger(s) have been notified." : '');
                } else {
                    $_SESSION['error'] = "Failed to submit request";
                }
                
                header('Location: ' . URL . 'intrastate/myrequests');
                exit();
            }
        }
        
        header('Location: ' . URL . 'intrastate/create');
        exit();
    }
    
    /**
     * Approve request via email link (no login required)
     */
    public function approve($id)
    {
        $token = $_GET['token'] ?? null;
        $level = $_GET['level'] ?? null;
        
        $request = $this->model->getIntrastateRequestById($id);
        
        if (!$request) {
            $this->showErrorPage("Request not found.");
            return;
        }
        
        // Verify token (48-hour window)
        if (!$token || !$level || !$this->verifyApprovalToken($id, $level, $token)) {
            if (isset($_SESSION['user_email'])) {
                $_SESSION['info'] = "The approval link has expired. Please use the approval page below.";
                header('Location: ' . URL . 'intrastate/pendingApprovals');
            } else {
                $_SESSION['expired_redirect'] = URL . 'intrastate/pendingApprovals';
                header('Location: ' . URL . 'login?msg=link_expired');
            }
            exit();
        }

        $current_level = $request->current_approval_level;
        $action_taken = false;

        // Process based on current level
        if ($current_level == 'reviewer' && $level == 'supervisor') {
            $action_taken = $this->processSupervisorApproval($request);
        } 
        elseif ($current_level == 'security_manager' && $level == 'security') {
            $action_taken = $this->processSecurityManagerApproval($request);
        }
        else {
            $this->showErrorPage("This request cannot be approved at this stage.");
            return;
        }
        
        if ($action_taken) {
            if ($current_level == 'reviewer') {
                $this->showSuccessPage("Request Approved!", "The request has been sent to the Security Manager for clearance.");
            } else {
                $this->showSuccessPage("Request Approved!", "The request has been sent to the Operations Team for driver assignment.");
            }
        } else {
            $this->showErrorPage("Failed to approve request. Please try again.");
        }
    }
    
    /**
     * Reject request via email link (no login required)
     */
    public function reject($id)
    {
        $token = $_GET['token'] ?? null;
        $level = $_GET['level'] ?? null;
        
        $request = $this->model->getIntrastateRequestById($id);
        
        if (!$request) {
            $this->showErrorPage("Request not found.");
            return;
        }
        
        // Verify token (48-hour window)
        if (!$token || !$level || !$this->verifyApprovalToken($id, $level, $token)) {
            if (isset($_SESSION['user_email'])) {
                $_SESSION['info'] = "The rejection link has expired. Please use the approval page below.";
                header('Location: ' . URL . 'intrastate/pendingApprovals');
            } else {
                $_SESSION['expired_redirect'] = URL . 'intrastate/pendingApprovals';
                header('Location: ' . URL . 'login?msg=link_expired');
            }
            exit();
        }

        // If POST request from the form
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $reason = trim($_POST['rejection_reason']);
            
            if (empty($reason)) {
                $this->showRejectionForm($request, "Rejection reason is required.");
                return;
            }
            
            $rejected_by = ($level == 'supervisor') ? $request->supervisor_email : $request->security_manager_email;
            
            $db = $this->model->getDb();
            $sql = "UPDATE intrastate_request SET status = 'rejected', rejection_reason = ?, rejected_by = ?, rejected_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$reason, $rejected_by, $id]);
            
            if ($result) {
                $this->sendRejectionEmail($request, $reason, $rejected_by);
                $this->showSuccessPage("Request Rejected", "The request has been rejected and the requester has been notified.");
            } else {
                $this->showErrorPage("Failed to reject request.");
            }
            return;
        }
        
        // Show rejection form
        $this->showRejectionForm($request);
    }
    
    /**
     * Cancel request from web interface (logged-in user)
     */
    public function webCancel($id)
    {
        $this->requireLogin();

        $request = $this->model->getIntrastateRequestById($id);

        if (!$request) {
            $_SESSION['error'] = "Request not found";
            header('Location: ' . URL . 'intrastate/myrequests');
            exit();
        }

        if ($request->staff_email != $_SESSION['user_email'] &&
            $_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin') {
            $_SESSION['error'] = "You don't have permission to cancel this request";
            header('Location: ' . URL . 'intrastate/myrequests');
            exit();
        }

        if (in_array($request->status, ['completed', 'rejected', 'cancelled'])) {
            $_SESSION['error'] = "This request cannot be cancelled";
            header('Location: ' . URL . 'intrastate/myrequests');
            exit();
        }

        $result = $this->model->cancelIntrastateRequest($id);

        if ($result) {
            $this->sendCancellationEmail($request);
            $_SESSION['success'] = "Request cancelled successfully";
        } else {
            $_SESSION['error'] = "Failed to cancel request";
        }

        header('Location: ' . URL . 'intrastate/myrequests');
        exit();
    }

    /**
     * Cancel request via email link
     */
    public function cancel($id)
    {
        $token = $_GET['token'] ?? null;
        
        $request = $this->model->getIntrastateRequestById($id);
        
        if (!$request) {
            $this->showErrorPage("Request not found.");
            return;
        }
        
        // Verify token
        if (!$token || !$this->verifyApprovalToken($id, 'cancel', $token)) {
            $this->showErrorPage("Invalid or expired cancellation link.");
            return;
        }
        
        if (in_array($request->status, ['completed', 'rejected'])) {
            $this->showErrorPage("This request cannot be cancelled.");
            return;
        }
        
        $result = $this->model->cancelIntrastateRequest($id);
        
        if ($result) {
            $this->sendCancellationEmail($request);
            $this->showSuccessPage("Request Cancelled", "Your trip request has been cancelled successfully.");
        } else {
            $this->showErrorPage("Failed to cancel request.");
        }
    }
    
    /**
     * Assign driver to request (operations team)
     */
    public function assignDriver($id)
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $driver_id = $_POST['driver_id'];
            
            if (empty($driver_id)) {
                $_SESSION['error'] = "Please select a driver";
                header('Location: ' . URL . 'intrastate/operationsDashboard');
                exit();
            }
            
            $result = $this->model->assignDriverToRequest($id, $driver_id);
            
            if ($result) {
                $request = $this->model->getIntrastateRequestById($id);
                $this->sendDriverAssignmentEmail($request, $driver_id);
                $_SESSION['success'] = "Driver assigned successfully";
            } else {
                $_SESSION['error'] = "Failed to assign driver";
            }
            
            header('Location: ' . URL . 'intrastate/operationsDashboard');
            exit();
        }
        
        header('Location: ' . URL . 'intrastate/operationsDashboard');
        exit();
    }
    
    /**
     * Mark trip as completed (operations team)
     */
    public function complete($id)
    {
        $this->requireLogin();
        
        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';
        
        // Check if user has operations privileges
        $isOperations = ($role == 'admin' || $role == 'super_admin') || $this->model->isUserOperationsTeam($user_email);
        
        if (!$isOperations) {
            $_SESSION['error'] = "You don't have permission to mark trips as completed.";
            header('Location: ' . URL . 'home');
            exit();
        }
        
        $request = $this->model->getIntrastateRequestById($id);
        
        if (!$request) {
            $_SESSION['error'] = "Request not found";
            header('Location: ' . URL . 'intrastate/operationsDashboard');
            exit();
        }
        
        if ($request->status == 'completed') {
            $_SESSION['error'] = "This trip is already marked as completed";
            header('Location: ' . URL . 'intrastate/operationsDashboard');
            exit();
        }
        
        if (!$request->assigned_driver_id) {
            $_SESSION['error'] = "Cannot complete trip - no driver assigned yet";
            header('Location: ' . URL . 'intrastate/operationsDashboard');
            exit();
        }
        
        $result = $this->model->markTripAsCompleted($id);
        
        if ($result) {
            // Send completion notification to staff
            $this->sendTripCompletionEmail($request);
            $_SESSION['success'] = "Trip marked as completed successfully";
        } else {
            $_SESSION['error'] = "Failed to mark trip as completed";
        }
        
        header('Location: ' . URL . 'intrastate/operationsDashboard');
        exit();
    }
    
    /**
     * View request details (requires login)
     */
    public function view($id)
    {
        $this->requireLogin();
        
        $request = $this->model->getIntrastateRequestById($id);
        
        if (!$request) {
            $_SESSION['error'] = "Request not found";
            header('Location: ' . URL . 'intrastate/myrequests');
            exit();
        }
        
        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';

        // Load passengers so we can check if current user is one
        $passengers = $this->model->getTripPassengers($id);
        $isPassenger = false;
        foreach ($passengers as $p) {
            if ($p->passenger_email === $user_email) { $isPassenger = true; break; }
        }

        if ($request->staff_email != $user_email &&
            !$isPassenger &&
            $role != 'admin' &&
            $role != 'super_admin' &&
            $request->supervisor_email != $user_email &&
            $request->security_manager_email != $user_email &&
            $request->reviewer_email != $user_email &&
            $request->co_reviewer_email != $user_email &&
            $request->manager_email != $user_email) {
            $_SESSION['error'] = "You don't have permission to view this request";
            header('Location: ' . URL . 'intrastate/myrequests');
            exit();
        }

        $drivers = [];
        $user_roles = [$request->reviewer_email, $request->co_reviewer_email, $request->manager_email];
        if (in_array($user_email, $user_roles) || $role == 'admin' || $role == 'super_admin') {
            $drivers = $this->model->getAllAvailableDrivers();
        }

        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/view_intrastate.php';
    }

    /**
     * Pending Approvals for Operations - Show trips awaiting driver assignment
     */
    public function pending()
    {
        $this->requireLogin();
        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';
        
        // Check if user has operations privileges
        $isOperations = ($role == 'admin' || $role == 'super_admin');
        
        if (!$isOperations) {
            $isOperations = $this->model->isUserOperationsTeam($user_email);
        }
        
        if (!$isOperations) {
            $_SESSION['error'] = "You don't have permission to access this page.";
            header('Location: ' . URL . 'home');
            exit();
        }
        
        // DEBUG: Run debug query first
        $debugTrips = $this->model->debugSecurityApprovedTrips();
        
        // Get requests awaiting driver assignment
        $pendingDriverRequests = $this->model->getIntrastateRequestsAwaitingDriver();
        
        // Also try a direct simple query to be sure
        $simpleQuery = $this->model->getSimpleAwaitingDriver();
        
        // Get all available drivers
        $availableDrivers = $this->model->getAllAvailableDrivers();
        
        // Get requests with driver assigned but not completed
        $assignedButNotCompleted = $this->model->getIntrastateRequestsInProgress();
        
        $stats = [
            'awaiting_driver' => count($pendingDriverRequests),
            'available_drivers' => count($availableDrivers),
            'in_progress' => count($assignedButNotCompleted),
            'debug_count' => count($debugTrips),
            'simple_count' => count($simpleQuery)
        ];
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/approvals/intrastateapproval.php';
    }
    /**
     * Process supervisor approval
     */
    private function processSupervisorApproval($request)
    {
        $db = $this->model->getDb();
        
        $updateSql = "UPDATE intrastate_request SET reviewer_approved_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($updateSql);
        $stmt->execute([$request->id]);
        
        $next_level = 'security_manager';
        $status = 'pending';
        
        $sql = "UPDATE intrastate_request SET status = ?, current_approval_level = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$status, $next_level, $request->id]);
        
        if ($result) {
            $this->sendSecurityManagerApprovalEmail($request);
        }
        
        return $result;
    }
    
    /**
     * Process security manager approval
     */
    private function processSecurityManagerApproval($request)
    {
        $db = $this->model->getDb();
        
        $updateSql = "UPDATE intrastate_request SET security_manager_approved_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($updateSql);
        $stmt->execute([$request->id]);
        
        $sql = "UPDATE intrastate_request SET status = 'security_approved', current_approval_level = 'none' WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$request->id]);
        
        if ($result) {
            $this->sendOperationsTeamNotification($request);
        }
        
        return $result;
    }
    
    /**
     * Show success page
     */
    private function showSuccessPage($title, $message)
    {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>' . $title . '</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f4f6f9; }
                .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .success { color: #28a745; font-size: 60px; margin-bottom: 20px; }
                h1 { color: #28a745; }
                .button { display: inline-block; padding: 10px 20px; margin-top: 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success">✓</div>
                <h1>' . $title . '</h1>
                <p>' . $message . '</p>
                <a href="' . URL . '" class="button">Go to Homepage</a>
            </div>
        </body>
        </html>';
        exit();
    }
    
    /**
     * Show error page
     */
    private function showErrorPage($message)
    {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f4f6f9; }
                .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error { color: #dc3545; font-size: 60px; margin-bottom: 20px; }
                h1 { color: #dc3545; }
                .button { display: inline-block; padding: 10px 20px; margin-top: 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error">✗</div>
                <h1>Error</h1>
                <p>' . $message . '</p>
                <a href="' . URL . '" class="button">Go to Homepage</a>
            </div>
        </body>
        </html>';
        exit();
    }
    
    /**
     * Show rejection form
     */
    private function showRejectionForm($request, $error = null)
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reject Trip Request</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 50px; background: #f4f6f9; }
                .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .form-group { margin-bottom: 20px; }
                label { display: block; margin-bottom: 8px; font-weight: bold; }
                textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: Arial; }
                button { padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
                button:hover { background: #c82333; }
                .error { color: red; margin-bottom: 15px; }
                .info { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Reject Trip Request</h2>
                <div class="info">
                    <strong>Trip Destination:</strong> <?= htmlspecialchars($request->trip_destination); ?><br>
                    <strong>Requester:</strong> <?= htmlspecialchars($request->staff_email); ?>
                </div>
                
                <?php if ($error): ?>
                    <div class="error"><?= $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="rejection_reason">Reason for Rejection <span style="color: red;">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="5" required placeholder="Please provide a detailed reason for rejecting this request..."></textarea>
                    </div>
                    <button type="submit">Submit Rejection</button>
                    <a href="<?= URL; ?>" style="margin-left: 15px;">Cancel</a>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    
    /**
     * Require login helper
     */
    private function requireLogin()
    {
        if (!isset($_SESSION['user_email'])) {
            header('Location: ' . URL . 'login/index');
            exit();
        }
    }
    
    /**
     * Get approvers by state (AJAX)
     */
    public function getApproversByState()
    {
        $this->requireLogin();
        
        if (isset($_GET['state_id'])) {
            $config = $this->model->getEaStateConfigByStateId($_GET['state_id']);
            header('Content-Type: application/json');
            echo json_encode($config);
            exit();
        }
        echo json_encode(null);
        exit();
    }
    
    /**
     * Send trip completion email to staff
     */
    private function sendTripCompletionEmail($request)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $subject = "Trip Completed - " . $request->trip_destination;
        
        $body = $this->getTripCompletionEmailTemplate($request, $staffName);
        
        return $this->sendEmail($request->staff_email, $subject, $body);
    }
    
    /**
     * Trip completion email template
     */
    private function getTripCompletionEmailTemplate($request, $staffName)
    {
        $driverName = $request->driver_name ?? ($request->driver_email ? explode('@', $request->driver_email)[0] : 'N/A');
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #17a2b8; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Trip Completed Successfully</h2>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($staffName) . ',</p>
                    <p>Your trip to <strong>' . htmlspecialchars($request->trip_destination) . '</strong> has been marked as completed by the operations team.</p>
                    
                    <p><strong>Trip Date:</strong> ' . date('F j, Y', strtotime($request->trip_date)) . '</p>
                    <p><strong>Driver:</strong> ' . htmlspecialchars($driverName) . '</p>
                    
                    <p>Thank you for using our vehicle services.</p>
                </div>
                <div class="footer">
                    <p>This is an automated message.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    // ========== EMAIL METHODS ==========
    
    /**
     * Send supervisor approval email
     */
    private function sendSupervisorApprovalEmail($request, $isUpdate = false)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $prefix = $isUpdate ? "[UPDATED] " : "";
        $subject = $prefix . "Trip Request Approval Required - " . $request->trip_destination;

        $approveUrl = URL . "intrastate/approve/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'supervisor') . "&level=supervisor";
        $declineUrl = URL . "intrastate/reject/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'supervisor') . "&level=supervisor";
        $cancelUrl  = URL . "intrastate/cancel/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'cancel');

        $body = $this->getSupervisorEmailTemplate($request, $staffName, $approveUrl, $declineUrl, $cancelUrl, $isUpdate);

        return $this->sendEmail($request->supervisor_email, $subject, $body);
    }

    
    /**
     * Notify a passenger that a trip has been booked on their behalf
     */
    private function sendPassengerNotificationEmail($request, $passengerEmail, $bookedByEmail)
    {
        $passengerName = ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $passengerEmail)[0]));
        $bookedByName  = ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $bookedByEmail)[0]));
        $viewUrl       = URL . 'intrastate/view/' . $request->id;
        $subject       = "Trip Booked For You — " . $request->trip_destination . " on " . date('M j, Y', strtotime($request->trip_date));

        $body = $this->getPassengerNotificationEmailTemplate($request, $passengerName, $bookedByEmail, $bookedByName, $viewUrl);
        return $this->sendEmail($passengerEmail, $subject, $body);
    }

    /**
     * Passenger notification email template
     */
    private function getPassengerNotificationEmailTemplate($request, $passengerName, $bookedByEmail, $bookedByName, $viewUrl)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0d6efd; color: white; padding: 18px 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .header h2 { margin: 0; font-size: 20px; }
                .content { background: #f8f9fa; padding: 24px; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 8px 8px; }
                .details { background: white; padding: 16px 20px; margin: 16px 0; border-radius: 8px; border-left: 4px solid #0d6efd; }
                .details p { margin: 6px 0; }
                .btn { display: inline-block; padding: 12px 28px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 12px; }
                .footer { text-align: center; padding: 14px; font-size: 12px; color: #888; }
                .note { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 10px 14px; margin-top: 16px; font-size: 13px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>&#9992; Trip Booked For You</h2>
                </div>
                <div class="content">
                    <p>Dear <strong>' . htmlspecialchars($passengerName) . '</strong>,</p>
                    <p><strong>' . htmlspecialchars($bookedByName) . '</strong> (<em>' . htmlspecialchars($bookedByEmail) . '</em>) has included you as a passenger on the following trip:</p>

                    <div class="details">
                        <p><strong>Destination:</strong> ' . htmlspecialchars($request->trip_destination) . '</p>
                        <p><strong>Pickup Location:</strong> ' . htmlspecialchars($request->pickup_location) . '</p>
                        <p><strong>Trip Date:</strong> ' . date('l, F j, Y', strtotime($request->trip_date)) . '</p>
                        <p><strong>Arrival Time:</strong> ' . htmlspecialchars($request->trip_destination_time) . '</p>
                        <p><strong>Purpose:</strong> ' . nl2br(htmlspecialchars($request->purpose)) . '</p>
                        ' . ($request->route_information ? '<p><strong>Route:</strong> ' . nl2br(htmlspecialchars($request->route_information)) . '</p>' : '') . '
                    </div>

                    <p>You can track the status of this trip at any time by clicking the button below:</p>
                    <div style="text-align:center;">
                        <a href="' . $viewUrl . '" class="btn">Track This Trip</a>
                    </div>

                    <div class="note">
                        <strong>Note:</strong> This trip is currently pending approval. You will be notified once a driver has been assigned.
                    </div>
                </div>
                <div class="footer">
                    <p>This is an automated notification from Vehicle Operations. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Send security manager approval email
     */
    private function sendSecurityManagerApprovalEmail($request)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $subject = "Security Clearance Required: Trip Request - " . $request->trip_destination;
        
        $approveUrl = URL . "intrastate/approve/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'security') . "&level=security";
        $declineUrl = URL . "intrastate/reject/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'security') . "&level=security";
        $cancelUrl = URL . "intrastate/cancel/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'cancel');
        
        $body = $this->getSecurityManagerEmailTemplate($request, $staffName, $approveUrl, $declineUrl, $cancelUrl);
        
        return $this->sendEmail($request->security_manager_email, $subject, $body);
    }
    
    /**
     * Send operations team notification
     */
    private function sendOperationsTeamNotification($request)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $subject = "Approved Trip Request - Action Required: " . $request->trip_destination;
        $viewUrl = URL . "intrastate/operationsDashboard";
        
        $body = $this->getOperationsTeamEmailTemplate($request, $staffName, $viewUrl);
        
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
            $mail->addAddress($request->reviewer_email);
            
            if ($request->co_reviewer_email && !empty($request->co_reviewer_email)) {
                $mail->addCC($request->co_reviewer_email);
            }
            
            $mail->addBCC($request->manager_email);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            error_log("Operations team notification sent");
            return true;
        } catch (Exception $e) {
            error_log("Operations team notification failed: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send rejection email
     */
    private function sendRejectionEmail($request, $reason, $rejected_by)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $rejecterName = explode('@', $rejected_by)[0];
        
        $subject = "Trip Request Declined - " . $request->trip_destination;
        $body = $this->getRejectionEmailTemplate($request, $staffName, $rejecterName, $reason);
        
        return $this->sendEmail($request->staff_email, $subject, $body);
    }
    
    /**
     * Send cancellation email
     */
    private function sendCancellationEmail($request)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $subject = "Trip Request Cancelled - " . $request->trip_destination;
        $body = $this->getCancellationEmailTemplate($request, $staffName);
        
        return $this->sendEmail($request->staff_email, $subject, $body);
    }
    
    /**
     * Send driver assignment email
     */
    private function sendDriverAssignmentEmail($request, $driver_id)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $driver = $this->model->getDriverById($driver_id);
        
        $subject = "Driver Assigned for Trip - " . $request->trip_destination;
        $body = $this->getDriverAssignmentEmailTemplate($request, $staffName, $driver);
        
        return $this->sendEmail($request->staff_email, $subject, $body);
    }
    
    /**
     * Generate approval token
     */
    private function generateApprovalToken($requestId, $level)
    {
        $secret = 'EVIDENCE_ACTION_SECRET_KEY_2024';
        $payload = $requestId . '|' . $level . '|' . floor(time() / 86400);
        return hash_hmac('sha256', $payload, $secret);
    }
    
    /**
     * Verify approval token — accepts tokens from the last 48 hours
     */
    private function verifyApprovalToken($requestId, $level, $token)
    {
        $secret = 'EVIDENCE_ACTION_SECRET_KEY_2024';
        $today = floor(time() / 86400);
        for ($i = 0; $i <= 1; $i++) {
            $payload = $requestId . '|' . $level . '|' . ($today - $i);
            $expected = hash_hmac('sha256', $payload, $secret);
            if (hash_equals($expected, $token)) return true;
        }
        return false;
    }

    /**
     * System-based approval (logged-in supervisor or security manager)
     */
    public function systemApprove($id)
    {
        $this->requireLogin();
        $user_email = $_SESSION['user_email'];

        $request = $this->model->getIntrastateRequestById($id);
        if (!$request) {
            $_SESSION['error'] = "Request not found.";
            header('Location: ' . URL . 'intrastate/pendingApprovals');
            exit();
        }

        $level = $request->current_approval_level;

        if ($level == 'reviewer' && $request->supervisor_email == $user_email) {
            $result = $this->processSupervisorApproval($request);
            $_SESSION[$result ? 'success' : 'error'] = $result
                ? "Request approved. Security manager has been notified."
                : "Failed to approve request.";
        } elseif ($level == 'security_manager' && $request->security_manager_email == $user_email) {
            $result = $this->processSecurityManagerApproval($request);
            $_SESSION[$result ? 'success' : 'error'] = $result
                ? "Request approved. Operations team has been notified."
                : "Failed to approve request.";
        } else {
            $_SESSION['error'] = "You are not authorized to approve this request at its current stage.";
        }

        header('Location: ' . URL . 'intrastate/pendingApprovals');
        exit();
    }

    /**
     * System-based rejection (logged-in supervisor or security manager)
     */
    public function systemReject($id)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'intrastate/pendingApprovals');
            exit();
        }

        $user_email = $_SESSION['user_email'];
        $reason = trim($_POST['rejection_reason'] ?? '');

        if (empty($reason)) {
            $_SESSION['error'] = "Rejection reason is required.";
            header('Location: ' . URL . 'intrastate/pendingApprovals');
            exit();
        }

        $request = $this->model->getIntrastateRequestById($id);
        if (!$request) {
            $_SESSION['error'] = "Request not found.";
            header('Location: ' . URL . 'intrastate/pendingApprovals');
            exit();
        }

        $level = $request->current_approval_level;
        $authorized = ($level == 'reviewer' && $request->supervisor_email == $user_email) ||
                      ($level == 'security_manager' && $request->security_manager_email == $user_email);

        if (!$authorized) {
            $_SESSION['error'] = "You are not authorized to reject this request.";
            header('Location: ' . URL . 'intrastate/pendingApprovals');
            exit();
        }

        $db = $this->model->getDb();
        $stmt = $db->prepare("UPDATE intrastate_request SET status = 'rejected', rejection_reason = ?, rejected_by = ?, rejected_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$reason, $user_email, $id]);

        if ($result) {
            $this->sendRejectionEmail($request, $reason, $user_email);
            $_SESSION['success'] = "Request rejected. The requester has been notified.";
        } else {
            $_SESSION['error'] = "Failed to reject request.";
        }

        header('Location: ' . URL . 'intrastate/pendingApprovals');
        exit();
    }

    /**
     * Pending approvals page — trips awaiting the logged-in user's approval (supervisor or security manager)
     */
    public function pendingApprovals()
    {
        $this->requireLogin();
        $user_email = $_SESSION['user_email'];

        $intra_sup = $this->model->getPendingIntrastateForSupervisor($user_email);
        foreach ($intra_sup as $r) { $r->trip_type = 'intrastate'; $r->approval_role = 'supervisor'; }

        $intra_sec = $this->model->getPendingIntrastateForSecurityManager($user_email);
        foreach ($intra_sec as $r) { $r->trip_type = 'intrastate'; $r->approval_role = 'security_manager'; }

        $inter_sup = $this->model->getPendingInterstateForSupervisor($user_email);
        foreach ($inter_sup as $r) { $r->trip_type = 'interstate'; $r->approval_role = 'supervisor'; }

        $inter_sec = $this->model->getPendingInterstateForSecurityManager($user_email);
        foreach ($inter_sec as $r) { $r->trip_type = 'interstate'; $r->approval_role = 'security_manager'; }

        $pendingApprovals = array_merge($intra_sup, $intra_sec, $inter_sup, $inter_sec);
        usort($pendingApprovals, fn($a, $b) => strtotime($a->trip_date) - strtotime($b->trip_date));

        require APP . 'view/_templates/header.php';
        require APP . 'view/approvals/pending_approvals.php';
    }

    public function getRequestJson($id)
    {
        $this->requireLogin();
        $request = $this->model->getIntrastateRequestById($id);
        
        if ($request) {
            echo json_encode(['success' => true, 'request' => $request]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Request not found']);
        }
        exit();
    }
    /**
     * Send email using PHPMailer
     */
    private function sendEmail($to, $subject, $body)
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
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            error_log("Email sent successfully to: " . $to);
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed to {$to}: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    
    // ========== EMAIL TEMPLATES ==========
    
    /**
     * Supervisor email template
     */
    private function getSupervisorEmailTemplate($request, $staffName, $approveUrl, $declineUrl, $cancelUrl, $isUpdate = false)
    {
        $headerBg = $isUpdate ? '#e67e22' : '#007bff';
        $updateBanner = $isUpdate ? '<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:5px;padding:10px 14px;margin-bottom:14px;"><strong>&#9888; Note:</strong> This trip request has been <strong>updated</strong> by the requester. Please review the new details before approving.</div>' : '';
        $actionText = $isUpdate ? 'updated an intrastate trip request' : 'submitted an intrastate trip request';
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: ' . $headerBg . '; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #007bff; }
                .button { display: inline-block; padding: 10px 20px; margin: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .approve { background: #28a745; color: white; }
                .decline { background: #dc3545; color: white; }
                .cancel { background: #ffc107; color: black; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Intrastate Trip Request Approval Required</h2>
                </div>
                <div class="content">
                    ' . $updateBanner . '
                    <p>Dear Supervisor,</p>
                    <p>Your supervisee, <strong>' . htmlspecialchars($staffName) . '</strong>, has ' . $actionText . ' for your approval.</p>
                    
                    <div class="details">
                        <h4>Trip Details:</h4>
                        <p><strong>Staff Email:</strong> ' . htmlspecialchars($request->staff_email) . '</p>
                        <p><strong>Staff Phone:</strong> ' . htmlspecialchars($request->staff_phone) . '</p>
                        <p><strong>Purpose:</strong> ' . nl2br(htmlspecialchars($request->purpose)) . '</p>
                        <p><strong>Pickup Location:</strong> ' . htmlspecialchars($request->pickup_location) . '</p>
                        <p><strong>Destination:</strong> ' . htmlspecialchars($request->trip_destination) . '</p>
                        <p><strong>Trip Date:</strong> ' . date('F j, Y', strtotime($request->trip_date)) . '</p>
                        <p><strong>Arrival Time:</strong> ' . $request->trip_destination_time . '</p>
                        ' . ($request->route_information ? '<p><strong>Route:</strong> ' . nl2br(htmlspecialchars($request->route_information)) . '</p>' : '') . '
                        ' . ($request->driver_overtime == 'yes' ? '<p style="color: red;"><strong>⚠ Overtime Required</strong></p>' : '') . '
                    </div>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . $approveUrl . '" class="button approve">✓ APPROVE</a>
                        <a href="' . $declineUrl . '" class="button decline">✗ DECLINE</a>
                        <a href="' . $cancelUrl . '" class="button cancel">⟳ CANCEL</a>
                    </div>
                    
                    <p><small>Click one of the buttons above. Links expire in 48 hours. If expired, log in and visit <a href="' . URL . 'intrastate/pendingApprovals">Pending Approvals</a>.</small></p>
                </div>
                <div class="footer">
                    <p>This is an automated message. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Security manager email template
     */
    private function getSecurityManagerEmailTemplate($request, $staffName, $approveUrl, $declineUrl, $cancelUrl)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #dc3545; }
                .button { display: inline-block; padding: 10px 20px; margin: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .approve { background: #28a745; color: white; }
                .decline { background: #dc3545; color: white; }
                .cancel { background: #ffc107; color: black; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Security Clearance Required</h2>
                </div>
                <div class="content">
                    <p>Dear Security Manager,</p>
                    <p>A trip request from <strong>' . htmlspecialchars($staffName) . '</strong> requires your security clearance.</p>
                    
                    <div class="details">
                        <h4>Trip Details:</h4>
                        <p><strong>Staff:</strong> ' . htmlspecialchars($request->staff_email) . ' / ' . htmlspecialchars($request->staff_phone) . '</p>
                        <p><strong>Supervisor:</strong> ' . htmlspecialchars($request->supervisor_email) . '</p>
                        <p><strong>Purpose:</strong> ' . nl2br(htmlspecialchars($request->purpose)) . '</p>
                        <p><strong>Pickup:</strong> ' . htmlspecialchars($request->pickup_location) . '</p>
                        <p><strong>Destination:</strong> ' . htmlspecialchars($request->trip_destination) . '</p>
                        <p><strong>Trip Date:</strong> ' . date('M j, Y', strtotime($request->trip_date)) . '</p>
                        <p><strong>Route:</strong> ' . ($request->route_information ?: 'Not provided') . '</p>
                        ' . ($request->driver_overtime == 'yes' ? '<p style="color: red;"><strong>⚠ Overtime Required</strong></p>' : '') . '
                    </div>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . $approveUrl . '" class="button approve">✓ APPROVE</a>
                        <a href="' . $declineUrl . '" class="button decline">✗ DECLINE</a>
                        <a href="' . $cancelUrl . '" class="button cancel">⟳ CANCEL</a>
                    </div>
                    
                    <p><small>Click one of the buttons above. Links expire in 48 hours. If expired, log in and visit <a href="' . URL . 'intrastate/pendingApprovals">Pending Approvals</a>.</small></p>
                </div>
                <div class="footer">
                    <p>This is an automated message. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Operations team email template
     */
    private function getOperationsTeamEmailTemplate($request, $staffName, $viewUrl)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #28a745; }
                .button { display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; background: #28a745; color: white; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Trip Request Approved - Action Required</h2>
                </div>
                <div class="content">
                    <p>Dear Operations Team,</p>
                    <p>A trip request from <strong>' . htmlspecialchars($staffName) . '</strong> has been approved by both supervisor and security manager.</p>
                    
                    <div class="details">
                        <h4>Trip Details:</h4>
                        <p><strong>Staff:</strong> ' . htmlspecialchars($request->staff_email) . ' / ' . htmlspecialchars($request->staff_phone) . '</p>
                        <p><strong>Supervisor:</strong> ' . htmlspecialchars($request->supervisor_email) . '</p>
                        <p><strong>Security Manager:</strong> ' . htmlspecialchars($request->security_manager_email) . '</p>
                        <p><strong>Purpose:</strong> ' . nl2br(htmlspecialchars($request->purpose)) . '</p>
                        <p><strong>Pickup:</strong> ' . htmlspecialchars($request->pickup_location) . '</p>
                        <p><strong>Destination:</strong> ' . htmlspecialchars($request->trip_destination) . '</p>
                        <p><strong>Trip Date:</strong> ' . date('F j, Y', strtotime($request->trip_date)) . '</p>
                        ' . ($request->driver_overtime == 'yes' ? '<p style="color: red;"><strong>⚠ Overtime Required</strong></p>' : '') . '
                        ' . ($request->need_driver_pickup == 'yes' ? '<p><strong>Driver Pickup Required:</strong> at ' . $request->pickup_time . '</p>' : '') . '
                    </div>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . $viewUrl . '" class="button">Go to Operations Dashboard</a>
                    </div>
                    
                    <p><small>Please log in to assign a driver.</small></p>
                </div>
                <div class="footer">
                    <p>This is an automated message. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Rejection email template
     */
    private function getRejectionEmailTemplate($request, $staffName, $rejecterName, $reason)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .reason { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #dc3545; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Trip Request Declined</h2>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($staffName) . ',</p>
                    <p>Your trip request to <strong>' . htmlspecialchars($request->trip_destination) . '</strong> has been declined by <strong>' . htmlspecialchars($rejecterName) . '</strong>.</p>
                    
                    <div class="reason">
                        <h4>Reason:</h4>
                        <p>' . nl2br(htmlspecialchars($reason)) . '</p>
                    </div>
                    
                    <p>Please contact your supervisor for more information.</p>
                </div>
                <div class="footer">
                    <p>This is an automated message.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Cancellation email template
     */
    private function getCancellationEmailTemplate($request, $staffName)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ffc107; color: black; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Trip Request Cancelled</h2>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($staffName) . ',</p>
                    <p>Your trip request to <strong>' . htmlspecialchars($request->trip_destination) . '</strong> has been cancelled.</p>
                    
                    <p>If this was a mistake, please submit a new request.</p>
                </div>
                <div class="footer">
                    <p>This is an automated message.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Driver assignment email template
     */
    private function getDriverAssignmentEmailTemplate($request, $staffName, $driver)
    {
        $driverName = $driver ? ($driver->name ?? $driver->driver_name ?? explode('@', $driver->email ?? 'unknown@')[0]) : 'N/A';
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .driver-info { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #28a745; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Driver Assigned for Your Trip</h2>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($staffName) . ',</p>
                    <p>A driver has been assigned for your trip to <strong>' . htmlspecialchars($request->trip_destination) . '</strong>.</p>
                    
                    <div class="driver-info">
                        <h4>Driver Details:</h4>
                        <p><strong>Name:</strong> ' . htmlspecialchars($driverName) . '</p>
                        <p><strong>Email:</strong> ' . htmlspecialchars($driver->email ?? 'N/A') . '</p>
                        <p><strong>Phone:</strong> ' . htmlspecialchars($driver->phone ?? 'N/A') . '</p>
                    </div>
                    
                    <p>Trip Date: <strong>' . date('F j, Y', strtotime($request->trip_date)) . '</strong></p>
                    <p>Pickup Location: <strong>' . htmlspecialchars($request->pickup_location) . '</strong></p>
                    ' . ($request->need_driver_pickup == 'yes' ? '<p>Pickup Time: <strong>' . $request->pickup_time . '</strong></p>' : '') . '
                </div>
                <div class="footer">
                    <p>This is an automated message.</p>
                </div>
            </div>
        </body>
        </html>';
    }
}
?>