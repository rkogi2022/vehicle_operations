<?php
/**
 * Interstate Controller
 * Handles all interstate trip request operations with email notifications
 */

// Load PHPMailer
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Interstate extends Controller
{
    
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION)) {
            session_start();
        }
    }
    
    /**
     * Default action - show all trips for the logged in user
     */
    public function index()
    {
        $this->requireLogin();
        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';
        
        // Get all requests for this user based on role
        $requests = [];
        
        // Admin/Super Admin can see all requests
        if ($role == 'admin' || $role == 'super_admin') {
            $requests = $this->model->getAllInterstateRequests();
        }
        // Supervisor - see requests pending their approval
        elseif ($this->isUserSupervisor($user_email)) {
            $requests = $this->model->getInterstateRequestsByApprover($user_email, 'supervisor');
        }
        // Security Manager - see requests pending their approval
        elseif ($this->isUserSecurityManager($user_email)) {
            $requests = $this->model->getInterstateRequestsByApprover($user_email, 'security_manager');
        }
        // Regular staff - see their own requests
        else {
            $requests = $this->model->getInterstateRequestsByStaff($user_email);
        }
        
        // Get counts by status for dashboard
        $counts = $this->getRequestCounts($requests);
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/index.php';
    }
    
    /**
     * Display form to create new interstate request
     */
    public function create()
    {
        $this->requireLogin();
        
        // Get dropdown data
        $departure_states = $this->model->getStatesByCountry($_SESSION['country_id']);
        $states = $this->model->getStatesByCountry($_SESSION['country_id']);
        $airlines = $this->model->getAllAirlines();
        $funder_codes = $this->model->getAllFunders();
        $hotels = $this->model->getHotelsWithStates();
        $supervisors = $this->model->getAdminSupervisorEmails();
        $overtimeManagers = $this->model->getAdminSupervisorEmails();

        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/interstate_request.php';
    }

    /**
     * Edit existing request (only draft status)
     */
    public function edit($id)
    {
        $this->requireLogin();

        $request = $this->model->getInterstateRequestById($id);

        if (!$request) {
            $_SESSION['error'] = "Request not found";
            header('Location: ' . URL . 'interstate');
            exit();
        }

        // Only owner can edit; allowed before supervisor approves (draft or pending-reviewer)
        if ($request->staff_email != $_SESSION['user_email']) {
            $_SESSION['error'] = "You cannot edit this request";
            header('Location: ' . URL . 'interstate');
            exit();
        }

        $canEdit = ($request->status == 'draft') ||
                   ($request->status == 'pending' && $request->current_approval_level == 'reviewer');

        if (!$canEdit) {
            $_SESSION['error'] = "This request cannot be edited — it has already been approved by your supervisor.";
            header('Location: ' . URL . 'interstate');
            exit();
        }

        // Get dropdown data
        $departure_states = $this->model->getStatesByCountry($_SESSION['country_id']);
        $states = $this->model->getStatesByCountry($_SESSION['country_id']);
        $airlines = $this->model->getAllAirlines();
        $funder_codes = $this->model->getAllFunders();
        $hotels = $this->model->getHotelsWithStates();
        $supervisors = $this->model->getAdminSupervisorEmails();
        $overtimeManagers = $this->model->getAdminSupervisorEmails();

        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/interstate_request.php';
    }

    /**
     * View request details
     */
    public function view($id)
    {
        $this->requireLogin();
        
        $request = $this->model->getInterstateRequestById($id);
        
        if (!$request) {
            $_SESSION['error'] = "Request not found";
            header('Location: ' . URL . 'interstate');
            exit();
        }
        
        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';
        
        // Check view permission
        if ($request->staff_email != $user_email && 
            $role != 'admin' && 
            $role != 'super_admin' &&
            $request->supervisor_email != $user_email &&
            $request->security_manager_email != $user_email &&
            $request->reviewer_email != $user_email &&
            $request->co_reviewer_email != $user_email &&
            $request->manager_email != $user_email) {
            $_SESSION['error'] = "You don't have permission to view this request";
            header('Location: ' . URL . 'interstate');
            exit();
        }
        
        require APP . 'view/_templates/header.php';
        require APP . 'view/trips/view_interstate.php';
    }
    
    /**
     * Save interstate request (as draft or submit)
     */
    public function save()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_email = $_SESSION['user_email'];
            $action = $_POST['action'] ?? 'submit';
            $request_id = $_POST['id'] ?? null;
            
            // Get vehicle location state and auto-populate approvers
            $vehicle_location_state_id = $_POST['vehicle_location_state_id'];
            $eaStateConfig = $this->model->getEaStateConfigByStateId($vehicle_location_state_id);

            // Fall back to country-level default approvers if state has no EA config
            if (!$eaStateConfig) {
                $eaStateConfig = $this->model->getCountryDefaultApprovers($_SESSION['country_id'] ?? 0);
            }

            if (!$eaStateConfig && $action == 'submit') {
                $_SESSION['error'] = "No approver configuration found for this state. Please contact administrator.";
                header('Location: ' . URL . 'interstate/create');
                exit();
            }
            
            // Calculate total nights
            $trip_date = $_POST['trip_date'];
            $return_date = $_POST['return_date'];
            $total_nights = $this->model->calculateTotalNights($trip_date, $return_date);
            
            // Prepare hotel data
            $hotel_id = null;
            $hotel_other_name = null;
            $hotel_location = null;
            $hotel_location_state_id = null;
            
            if (isset($_POST['require_hotel']) && $_POST['require_hotel'] == 'yes') {
                if (isset($_POST['hotel_option']) && $_POST['hotel_option'] == 'existing' && !empty($_POST['hotel_id'])) {
                    $hotel_id = $_POST['hotel_id'];
                    $hotel = $this->model->getHotelById($_POST['hotel_id']);
                    if ($hotel) {
                        $hotel_location = $hotel->location ?? '';
                        $hotel_location_state_id = $hotel->state_id;
                    }
                } elseif (isset($_POST['hotel_option']) && $_POST['hotel_option'] == 'other') {
                    $hotel_other_name = trim($_POST['hotel_other_name']);
                    $hotel_location = trim($_POST['hotel_other_location']);
                    $hotel_location_state_id = $_POST['hotel_other_state_id'] ?? null;
                }
            }
            
            // Prepare airport pickup data
            $require_airport_pickup = isset($_POST['require_airport_pickup']) ? $_POST['require_airport_pickup'] : 'no';
            $airport_pickup_dropoff_destination = isset($_POST['airport_pickup_dropoff_destination']) ? trim($_POST['airport_pickup_dropoff_destination']) : null;
            
            // Prepare flight data
            $requester_departure_flight = !empty($_POST['requester_departure_flight_airline_id']) ? $_POST['requester_departure_flight_airline_id'] : null;
            $requester_return_flight = !empty($_POST['requester_return_flight_airline_id']) ? $_POST['requester_return_flight_airline_id'] : null;
            
            // Prepare data array
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
                'return_date' => $return_date,
                'total_nights' => $total_nights,
                'purpose' => trim($_POST['purpose']),
                'arrival_location_state_id' => $_POST['arrival_location_state_id'],
                'destination_city' => trim($_POST['destination_city']),
                'pickup_location' => trim($_POST['pickup_location']),
                'trip_destination' => trim($_POST['destination_city']),
                'trip_destination_time' => $_POST['trip_destination_time'],
                'route_information' => trim($_POST['route_information']),
                'mode_of_travel' => $_POST['mode_of_travel'],
                'require_airport_pickup' => $require_airport_pickup,
                'airport_pickup_dropoff_destination' => $airport_pickup_dropoff_destination,
                'requester_departure_flight_airline_id' => $requester_departure_flight,
                'requester_return_flight_airline_id' => $requester_return_flight,
                'require_hotel' => isset($_POST['require_hotel']) ? $_POST['require_hotel'] : 'no',
                'hotel_id' => $hotel_id,
                'hotel_other_name' => $hotel_other_name,
                'hotel_location' => $hotel_location,
                'hotel_location_state_id' => $hotel_location_state_id,
                'funder_code_id' => $_POST['funder_code_id'],
                'driver_overtime' => $_POST['driver_overtime'] ?? 'no',
                'trip_activity' => isset($_POST['driver_overtime']) && $_POST['driver_overtime'] == 'yes' ? trim($_POST['trip_activity']) : null,
                'reason_for_overtime' => isset($_POST['driver_overtime']) && $_POST['driver_overtime'] == 'yes' ? trim($_POST['reason_for_overtime']) : null,
                'overtime_manager_email' => isset($_POST['driver_overtime']) && $_POST['driver_overtime'] == 'yes' ? trim($_POST['overtime_manager_email']) : null,
                'taf_approved' => $_POST['taf_approved'] ?? 'no',
                'need_driver_pickup' => $_POST['need_driver_pickup'] ?? 'no',
                'pickup_time' => isset($_POST['need_driver_pickup']) && $_POST['need_driver_pickup'] == 'yes' ? $_POST['pickup_time'] : null,
                'status' => ($action == 'draft') ? 'draft' : 'pending',
                'current_approval_level' => ($action == 'submit') ? 'reviewer' : 'none'
            ];
            
            $existingStatus = $_POST['status'] ?? null;

            if ($action == 'draft') {
                if ($request_id) {
                    $result = $this->model->updateInterstateRequest($request_id, $data);
                } else {
                    $result = $this->model->saveInterstateRequestDraft($data);
                }

                if ($result) {
                    $_SESSION['success'] = "Draft saved successfully";
                } else {
                    $_SESSION['error'] = "Failed to save draft";
                }
            } elseif ($request_id && $existingStatus == 'pending') {
                // Re-submit a pending trip that hasn't been supervisor-approved yet
                $result = $this->model->updatePendingInterstateRequest($request_id, $data);
                if ($result) {
                    $request = $this->model->getInterstateRequestById($request_id);
                    if ($request && $request->supervisor_email) {
                        $this->sendSupervisorApprovalEmail($request, true);
                    }
                    $_SESSION['success'] = "Request updated. Your supervisor has been notified of the changes.";
                } else {
                    $_SESSION['error'] = "Failed to update request. It may have already been approved.";
                }
            } else {
                // Submit for approval
                if ($request_id && $existingStatus == 'draft') {
                    $updateResult = $this->model->updateInterstateRequest($request_id, $data);
                    if ($updateResult) {
                        $submitResult = $this->model->submitInterstateRequest($request_id);
                        $request = $this->model->getInterstateRequestById($request_id);
                    } else {
                        $submitResult = false;
                    }
                } else {
                    // Guard against double-submit
                    if ($this->model->isDuplicateInterstateRequest($user_email, $data['trip_date'], $data['trip_destination'])) {
                        $_SESSION['error'] = "A duplicate request was detected. Please check your existing requests before submitting again.";
                        header('Location: ' . URL . 'interstate');
                        exit();
                    }
                    $requestId = $this->model->createInterstateRequest($data);
                    $submitResult = $requestId ? true : false;
                    if ($submitResult) {
                        $request = $this->model->getInterstateRequestById($requestId);
                    }
                }

                if ($submitResult && $request) {
                    $this->sendSupervisorApprovalEmail($request);
                    $_SESSION['success'] = "Request submitted for approval successfully. Supervisor has been notified.";
                } else {
                    $_SESSION['error'] = "Failed to submit request";
                }
            }
            
            header('Location: ' . URL . 'interstate');
            exit();
        }
        
        header('Location: ' . URL . 'interstate/create');
        exit();
    }
    
    /**
     * Approve request via email link (no login required)
     */
    public function approve($id)
    {
        $token = $_GET['token'] ?? null;
        $level = $_GET['level'] ?? null;
        
        $request = $this->model->getInterstateRequestById($id);
        
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
        elseif ($request->status === 'security_approved' || $request->status === 'completed') {
            $this->showSuccessPage("Already Approved", "This request has already been fully approved. No further action is needed.");
            return;
        }
        elseif ($level == 'supervisor' && in_array($current_level, ['security_manager', 'none'])) {
            $this->showSuccessPage("Already Approved", "You have already approved this request. It has been forwarded to the next stage.");
            return;
        }
        elseif ($level == 'security' && $current_level == 'none') {
            $this->showSuccessPage("Already Approved", "You have already approved this request. Operations have been notified for driver assignment.");
            return;
        }
        else {
            $this->showErrorPage("This request cannot be approved at this stage. Current status: " . htmlspecialchars($request->status) . " / Level: " . htmlspecialchars($current_level ?: 'none'));
            return;
        }

        if ($action_taken) {
            if ($current_level == 'reviewer') {
                $this->showSuccessPage("Request Approved!", "The request has been sent to the Security Manager for clearance.");
            } else {
                $this->showSuccessPage("Request Approved!", "The request has been approved. Operations team will be notified for driver assignment.");
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
        
        $request = $this->model->getInterstateRequestById($id);
        
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

        // Can't reject a request that's already been decided
        if (!in_array($request->status, ['pending', 'draft'])) {
            $this->showErrorPage("This request has already been " . htmlspecialchars($request->status) . " and cannot be rejected.");
            return;
        }

        // If POST request from the form
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $reason = trim($_POST['rejection_reason']);
            
            if (empty($reason)) {
                $this->showRejectionForm($request, "Rejection reason is required.");
                return;
            }
            
            $rejected_by = $this->getRejecterEmail($request, $level);
            $rejected_by_name = explode('@', $rejected_by)[0];
            
            $db = $this->model->getDb();
            $sql = "UPDATE interstate_request SET status = 'rejected', rejection_reason = ?, rejected_by = ?, rejected_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$reason, $rejected_by, $id]);
            
            if ($result) {
                $this->sendRejectionEmail($request, $reason, $rejected_by_name);
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
     * Cancel request via email link
     */
    public function cancel($id)
    {
        $token = $_GET['token'] ?? null;
        
        $request = $this->model->getInterstateRequestById($id);
        
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
        
        $result = $this->model->cancelInterstateRequest($id);
        
        if ($result) {
            $this->sendCancellationEmail($request);
            $this->showSuccessPage("Request Cancelled", "Your interstate trip request has been cancelled successfully.");
        } else {
            $this->showErrorPage("Failed to cancel request.");
        }
    }
    
    /**
     * Cancel request from web interface (logged in user)
     */
    public function webCancel($id)
    {
        $this->requireLogin();
        
        $request = $this->model->getInterstateRequestById($id);
        
        if (!$request) {
            $_SESSION['error'] = "Request not found";
            header('Location: ' . URL . 'interstate');
            exit();
        }
        
        // Check permission - only owner or admin can cancel
        if ($request->staff_email != $_SESSION['user_email'] && 
            $_SESSION['role'] != 'admin' && 
            $_SESSION['role'] != 'super_admin') {
            $_SESSION['error'] = "You don't have permission to cancel this request";
            header('Location: ' . URL . 'interstate');
            exit();
        }
        
        if (in_array($request->status, ['completed', 'rejected'])) {
            $_SESSION['error'] = "This request cannot be cancelled";
            header('Location: ' . URL . 'interstate');
            exit();
        }
        
        $result = $this->model->cancelInterstateRequest($id);
        
        if ($result) {
            $this->sendCancellationEmail($request);
            $_SESSION['success'] = "Request cancelled successfully";
        } else {
            $_SESSION['error'] = "Failed to cancel request";
        }
        
        header('Location: ' . URL . 'interstate');
        exit();
    }
    
    /**
     * Delete draft request
     */
    public function delete($id)
    {
        $this->requireLogin();
        
        $request = $this->model->getInterstateRequestById($id);
        
        if (!$request) {
            $_SESSION['error'] = "Request not found";
            header('Location: ' . URL . 'interstate');
            exit();
        }
        
        // Check permission - only owner can delete draft
        if ($request->staff_email != $_SESSION['user_email'] || $request->status != 'draft') {
            $_SESSION['error'] = "You cannot delete this request";
            header('Location: ' . URL . 'interstate');
            exit();
        }
        
        $result = $this->model->deleteInterstateRequest($id);
        
        if ($result) {
            $_SESSION['success'] = "Request deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete request";
        }
        
        header('Location: ' . URL . 'interstate');
        exit();
    }
    
    /**
     * Mark interstate trip as completed (operations team)
     */
    public function complete($id)
    {
        $this->requireLogin();

        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';
        $isOperations = ($role == 'admin' || $role == 'super_admin') || $this->model->isUserOperationsTeam($user_email);

        if (!$isOperations) {
            $_SESSION['error'] = "You don't have permission to mark trips as completed.";
            header('Location: ' . URL . 'home');
            exit();
        }

        $request = $this->model->getInterstateRequestById($id);

        if (!$request || $request->status !== 'security_approved' || !$request->assigned_driver_id) {
            $_SESSION['error'] = "This trip cannot be marked as completed.";
            header('Location: ' . URL . 'interstate/operationsDashboard');
            exit();
        }

        $result = $this->model->completeInterstateRequest($id);

        if ($result) {
            $_SESSION['success'] = "Trip #" . $id . " marked as completed.";
        } else {
            $_SESSION['error'] = "Failed to complete trip.";
        }

        header('Location: ' . URL . 'interstate/operationsDashboard');
        exit();
    }

    /**
     * Get EA state by state ID (AJAX)
     */
    public function getEaStateByStateId()
    {
        $this->requireLogin();

        header('Content-Type: application/json');
        if (isset($_GET['state_id'])) {
            $config = $this->model->getEaStateConfigByStateId($_GET['state_id']);
            if ($config) {
                echo json_encode(['success' => true, 'data' => $config, 'is_fallback' => false]);
            } else {
                // Try country-level default approvers
                $country_id = $_SESSION['country_id'] ?? null;
                $fallback = $country_id ? $this->model->getCountryDefaultApprovers($country_id) : null;
                if ($fallback) {
                    echo json_encode(['success' => true, 'data' => $fallback, 'is_fallback' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No approver configuration for this state']);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No state ID provided']);
        }
        exit();
    }
    
    /**
     * Operations dashboard — pending driver assignment for interstate trips
     */
    public function operationsDashboard()
    {
        $this->requireLogin();

        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';

        $isOperations = ($role == 'admin' || $role == 'super_admin') || $this->model->isUserOperationsTeam($user_email);
        if (!$isOperations) {
            $_SESSION['error'] = "You don't have permission to access the operations dashboard.";
            header('Location: ' . URL . 'home');
            exit();
        }

        $pendingDriverRequests  = $this->model->getInterstateRequestsAwaitingDriver();
        $inProgressRequests     = $this->model->getInterstateRequestsInProgress();
        $allDrivers             = $this->model->getAllAvailableDrivers();
        $airlines               = $this->model->getAllAirlines();
        $hotels                 = $this->model->getHotelsWithStates();

        // Build per-destination-state driver lists for both awaiting and in-progress trips
        $driversByState = [];
        $allTripsForDriverMap = array_merge($pendingDriverRequests, $inProgressRequests);
        foreach ($allTripsForDriverMap as $req) {
            $sid = $req->arrival_location_state_id ?? null;
            if ($sid && !isset($driversByState[$sid])) {
                $stateDrivers = $this->model->getDriversByStateId($sid);
                $driversByState[$sid] = !empty($stateDrivers) ? $stateDrivers : $allDrivers;
            }
        }

        $stats = [
            'awaiting_driver' => count($pendingDriverRequests),
            'in_progress'     => count($inProgressRequests),
        ];

        require APP . 'view/_templates/header.php';
        require APP . 'view/approvals/interstateapproval.php';
    }

    /**
     * Assign driver (and confirm flights/hotel) for an interstate trip
     */
    public function assignDriver($id)
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'interstate/operationsDashboard');
            exit();
        }

        $user_email = $_SESSION['user_email'];
        $role = $_SESSION['role'] ?? '';
        $isOperations = ($role == 'admin' || $role == 'super_admin') || $this->model->isUserOperationsTeam($user_email);

        if (!$isOperations) {
            $_SESSION['error'] = "You don't have permission.";
            header('Location: ' . URL . 'interstate/operationsDashboard');
            exit();
        }

        $driver_id = $_POST['driver_id'] ?? null;
        if (empty($driver_id)) {
            $_SESSION['error'] = "Please select a departure driver.";
            header('Location: ' . URL . 'interstate/operationsDashboard');
            exit();
        }

        $differentReturn = $_POST['different_return_driver'] ?? 'no';
        $returnDriverId  = ($differentReturn === 'yes' && !empty($_POST['return_driver_id'])) ? $_POST['return_driver_id'] : null;

        $rawHotelId = $_POST['hotel_id'] ?? null;
        $opsHotelId = ($rawHotelId && $rawHotelId !== 'other') ? $rawHotelId : null;

        $data = [
            'assigned_driver_id'                     => $driver_id,
            'different_return_driver'                => $differentReturn,
            'return_assigned_driver_id'              => $returnDriverId,
            'operations_departure_flight_airline_id' => $_POST['operations_departure_flight_airline_id'] ?? null,
            'operations_return_flight_airline_id'    => $_POST['operations_return_flight_airline_id'] ?? null,
            'hotel_id'           => $opsHotelId,
            'hotel_other_name'   => trim($_POST['hotel_other_name'] ?? ''),
            'hotel_location'     => trim($_POST['hotel_location'] ?? ''),
        ];

        // Check before updating whether a driver was already assigned (= this is an update, not initial assign)
        $existingRequest = $this->model->getInterstateRequestById($id);
        $isUpdate = !empty($existingRequest->assigned_driver_id);

        $result = $this->model->operationsAssignInterstate($id, $data);

        if ($result) {
            $request = $this->model->getInterstateRequestById($id);
            if ($request) {
                if ($isUpdate) {
                    $emailSent = $this->sendTripDetailsUpdateEmail($request, $existingRequest);
                    $_SESSION['success'] = $emailSent
                        ? "Trip details updated. The requester has been notified by email."
                        : "Trip details updated. Note: email notification could not be sent.";
                } else {
                    $emailSent = $this->sendDriverAssignmentEmail($request, $existingRequest);
                    $_SESSION['success'] = $emailSent
                        ? "Driver assigned and trip details confirmed. Staff has been notified by email."
                        : "Driver assigned successfully. Note: email notification to staff could not be sent.";
                }
            } else {
                $_SESSION['success'] = $isUpdate ? "Trip details updated." : "Driver assigned successfully.";
            }
        } else {
            $_SESSION['error'] = "Failed to save trip details. Please try again.";
        }

        header('Location: ' . URL . 'interstate/operationsDashboard');
        exit();
    }

    /**
     * Get hotels by state (AJAX)
     */
    public function getHotelsByState()
    {
        $this->requireLogin();
        
        if (isset($_GET['state_id'])) {
            $state_id = $_GET['state_id'];
            $hotels = $this->model->getHotelsByState($state_id);
            header('Content-Type: application/json');
            echo json_encode($hotels);
            exit();
        }
        echo json_encode([]);
        exit();
    }
    
    // ========== APPROVAL PROCESSING METHODS ==========

    /**
     * Process supervisor approval (step 1 → security manager)
     */
    private function processSupervisorApproval($request)
    {
        $db = $this->model->getDb();

        $stmt = $db->prepare("UPDATE interstate_request SET reviewer_approved_at = NOW() WHERE id = ?");
        $stmt->execute([$request->id]);

        $stmt = $db->prepare("UPDATE interstate_request SET status = 'pending', current_approval_level = 'security_manager' WHERE id = ?");
        $result = $stmt->execute([$request->id]);

        if ($result) {
            $this->sendSecurityManagerApprovalEmail($request);
        }

        return $result;
    }

    /**
     * Process security manager approval (step 2 → operations team)
     */
    private function processSecurityManagerApproval($request)
    {
        $db = $this->model->getDb();
        
        $updateSql = "UPDATE interstate_request SET security_manager_approved_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($updateSql);
        $stmt->execute([$request->id]);
        
        $sql = "UPDATE interstate_request SET status = 'security_approved', current_approval_level = 'none' WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$request->id]);
        
        if ($result) {
            $this->sendOperationsTeamNotification($request);
        }
        
        return $result;
    }
    
    // ========== EMAIL SENDING METHODS ==========

    /**
     * Send supervisor approval email (initial submission or update)
     */
    private function sendSupervisorApprovalEmail($request, $isUpdate = false)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $prefix = $isUpdate ? "[UPDATED] " : "";
        $subject = $prefix . "Interstate Trip Request Approval Required - " . $request->trip_destination;

        $approveUrl = URL . "interstate/approve/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'supervisor') . "&level=supervisor";
        $declineUrl = URL . "interstate/reject/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'supervisor') . "&level=supervisor";
        $cancelUrl  = URL . "interstate/cancel/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'cancel');

        $body = $this->getSupervisorEmailTemplate($request, $staffName, $approveUrl, $declineUrl, $cancelUrl, $isUpdate);

        return $this->sendEmail($request->supervisor_email, $subject, $body);
    }

    /**
     * Send security manager approval email
     */
    private function sendSecurityManagerApprovalEmail($request)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $subject = "Security Clearance Required: Interstate Trip Request - " . $request->trip_destination;
        
        $approveUrl = URL . "interstate/approve/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'security') . "&level=security";
        $declineUrl = URL . "interstate/reject/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'security') . "&level=security";
        $cancelUrl = URL . "interstate/cancel/" . $request->id . "?token=" . $this->generateApprovalToken($request->id, 'cancel');
        
        $body = $this->getSecurityManagerEmailTemplate($request, $staffName, $approveUrl, $declineUrl, $cancelUrl);
        
        return $this->sendEmail($request->security_manager_email, $subject, $body);
    }
    
    /**
     * Send operations team notification
     */
    private function sendOperationsTeamNotification($request)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $subject = "Interstate Trip Request Fully Approved - Action Required: " . $request->trip_destination;
        $viewUrl = URL . "interstate/operationsDashboard";
        
        $body = $this->getOperationsTeamEmailTemplate($request, $staffName, $viewUrl);
        
        // Send to reviewer, co-reviewer, and manager as operations team
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
        
        $subject = "Interstate Trip Request Declined - " . $request->trip_destination;
        $body = $this->getRejectionEmailTemplate($request, $staffName, $rejected_by, $reason);
        
        return $this->sendEmail($request->staff_email, $subject, $body);
    }
    
    /**
     * Send driver assignment notification to staff (CC supervisor)
     */
    private function sendDriverAssignmentEmail($request, $previousRequest = null)
    {
        $staffName   = explode('@', $request->staff_email)[0];
        $subject     = "Trip Confirmed - Driver Assigned: " . $request->trip_destination;
        $driverName  = $request->driver_name ?? '';
        $driverEmail = $request->approved_driver_email ?? '';
        $driverPhone = $request->approved_driver_phone ?? '';

        // Confirmed flights: operations choice takes priority, fall back to requester's suggestion
        $confirmedDepAirline = $request->operations_departure_airline ?? $request->requester_departure_airline ?? null;
        $confirmedRetAirline = $request->operations_return_airline    ?? $request->requester_return_airline    ?? null;
        $reqDepAirline       = $request->requester_departure_airline ?? null;
        $reqRetAirline       = $request->requester_return_airline    ?? null;
        $depChanged          = $confirmedDepAirline && $reqDepAirline && $confirmedDepAirline !== $reqDepAirline;
        $retChanged          = $confirmedRetAirline && $reqRetAirline && $confirmedRetAirline !== $reqRetAirline;

        $modeOfTravel  = $request->mode_of_travel ?? 'road';
        $isAir         = in_array($modeOfTravel, ['air', 'both']);

        $flightSection = '';
        if ($isAir) {
            if ($confirmedDepAirline) {
                $changeNote = $depChanged ? ' <span style="color:#dc3545;">(changed from: ' . htmlspecialchars($reqDepAirline) . ')</span>' : ' <span style="color:#28a745;">(as requested)</span>';
                $flightSection .= '<p><strong>Departure Airline:</strong> ' . htmlspecialchars($confirmedDepAirline) . $changeNote . '</p>';
            }
            if ($confirmedRetAirline) {
                $changeNote = $retChanged ? ' <span style="color:#dc3545;">(changed from: ' . htmlspecialchars($reqRetAirline) . ')</span>' : ' <span style="color:#28a745;">(as requested)</span>';
                $flightSection .= '<p><strong>Return Airline:</strong> ' . htmlspecialchars($confirmedRetAirline) . $changeNote . '</p>';
            }
        }

        $hotelName    = $request->hotel_name_from_vendor ?? $request->hotel_other_name ?? null;
        $hotelLoc     = $request->hotel_location ?? '';
        $prevHotelName = $previousRequest ? ($previousRequest->hotel_name_from_vendor ?? $previousRequest->hotel_other_name ?? null) : null;
        $hotelSection = '';
        if ($hotelName) {
            $hotelChanged = $prevHotelName && $prevHotelName !== $hotelName;
            $hotelNote    = $hotelChanged
                ? ' <span style="color:#dc3545;">(changed from: ' . htmlspecialchars($prevHotelName) . ')</span>'
                : ($prevHotelName ? ' <span style="color:#28a745;">(as requested)</span>' : '');
            $hotelSection  = '<div style="background:#f0fdf4;padding:10px 14px;margin:8px 0;border-radius:5px;border-left:4px solid #28a745;">';
            $hotelSection .= '<strong>&#127970; Hotel:</strong> ' . htmlspecialchars($hotelName) . $hotelNote;
            if ($hotelLoc) $hotelSection .= ' &mdash; ' . htmlspecialchars($hotelLoc);
            $hotelSection .= '</div>';
        }

        $pickupNote = '';
        if ($request->need_driver_pickup === 'yes' && $request->pickup_time) {
            $pickupNote = '<p><strong>Driver Pickup Time:</strong> ' . $request->pickup_time . ' at ' . htmlspecialchars($request->pickup_location) . '</p>';
        }

        $body = '<!DOCTYPE html><html><head><style>
            body{font-family:Arial,sans-serif;line-height:1.6;}
            .container{max-width:600px;margin:0 auto;padding:20px;}
            .header{background:#28a745;color:white;padding:15px;text-align:center;border-radius:5px 5px 0 0;}
            .content{background:#f8f9fa;padding:20px;border:1px solid #ddd;border-top:none;}
            .details{background:white;padding:15px;margin:15px 0;border-radius:5px;border-left:4px solid #28a745;}
            .driver-box{background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:12px 16px;margin:12px 0;}
            .footer{text-align:center;padding:15px;font-size:12px;color:#666;}
        </style></head><body>
        <div class="container">
            <div class="header"><h2>&#10003; Your Interstate Trip Has Been Confirmed</h2></div>
            <div class="content">
                <p>Dear ' . htmlspecialchars($staffName) . ',</p>
                <p>The operations team has confirmed your interstate trip request to <strong>' . htmlspecialchars($request->trip_destination) . '</strong>.</p>
                <div class="driver-box">
                    <strong><i>&#128663; Assigned Driver</i></strong><br>
                    ' . ($driverName ? 'Name: ' . htmlspecialchars($driverName) . '<br>' : '') . '
                    ' . ($driverEmail ? 'Email: ' . htmlspecialchars($driverEmail) . '<br>' : '') . '
                    Phone: ' . htmlspecialchars($driverPhone) . '
                </div>
                <div class="details">
                    <h4>Confirmed Trip Details:</h4>
                    <p><strong>From:</strong> ' . htmlspecialchars($request->vehicle_location_state_name ?? '') . '</p>
                    <p><strong>To:</strong> ' . htmlspecialchars($request->arrival_state_name ?? '') . ' — ' . htmlspecialchars($request->destination_city ?? '') . '</p>
                    <p><strong>Trip Date:</strong> ' . date('F j, Y', strtotime($request->trip_date)) . '</p>
                    <p><strong>Return Date:</strong> ' . date('F j, Y', strtotime($request->return_date)) . '</p>
                    <p><strong>Mode of Travel:</strong> ' . ucfirst($modeOfTravel) . '</p>
                    ' . $pickupNote . $flightSection . $hotelSection . '
                </div>
                <p>Please be at your pickup location on time. Contact your driver directly if needed.</p>
            </div>
            <div class="footer"><p>This is an automated message. Please do not reply.</p></div>
        </div></body></html>';

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
            $mail->addAddress($request->staff_email);
            if ($request->supervisor_email) {
                $mail->addCC($request->supervisor_email);
            }
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            $mail->send();
            error_log("Driver assignment email sent to: " . $request->staff_email);
            return true;
        } catch (Exception $e) {
            error_log("Driver assignment email failed: " . $mail->ErrorInfo);
            return false;
        }
    }

    private function sendTripDetailsUpdateEmail($request, $previousRequest = null)
    {
        $staffName  = explode('@', $request->staff_email)[0];
        $subject    = "[UPDATED] Your Interstate Trip Details Have Changed — " . $request->trip_destination;
        $modeOfTravel = $request->mode_of_travel ?? 'road';
        $isAir      = in_array(strtolower($modeOfTravel), ['air', 'both']);

        $confirmedDepAirline = $request->ops_departure_airline_name ?? $request->requester_departure_airline ?? null;
        $confirmedRetAirline = $request->ops_return_airline_name    ?? $request->requester_return_airline    ?? null;
        $driverName  = $request->driver_name  ?? ($request->approved_driver_email  ? explode('@', $request->approved_driver_email)[0]  : null);
        $driverPhone = $request->approved_driver_phone ?? '';
        $driverEmail = $request->approved_driver_email ?? '';

        $flightSection = '';
        if ($isAir) {
            $flightSection  = '<div style="background:#e8f4fd;padding:12px 15px;margin:10px 0;border-radius:5px;border-left:4px solid #0d6efd;">';
            $flightSection .= '<strong>&#9992; Confirmed Flights</strong><br>';
            $flightSection .= '<strong>Departure:</strong> ' . htmlspecialchars($confirmedDepAirline ?: 'Not specified') . '<br>';
            $flightSection .= '<strong>Return:</strong> '   . htmlspecialchars($confirmedRetAirline ?: 'Not specified') . '<br>';
            $flightSection .= '</div>';
        }

        $hotelName2    = $request->hotel_name_from_vendor ?? $request->hotel_other_name ?? null;
        $hotelLoc2     = $request->hotel_location ?? '';
        $prevHotelName = $previousRequest ? ($previousRequest->hotel_name_from_vendor ?? $previousRequest->hotel_other_name ?? null) : null;
        $hotelSection  = '';
        if ($hotelName2) {
            $hotelChanged = $prevHotelName && $prevHotelName !== $hotelName2;
            $hotelNote    = $hotelChanged
                ? ' <span style="color:#dc3545;">(changed from: ' . htmlspecialchars($prevHotelName) . ')</span>'
                : ($prevHotelName ? ' <span style="color:#28a745;">(as requested)</span>' : '');
            $hotelSection  = '<div style="background:#f0fdf4;padding:12px 15px;margin:10px 0;border-radius:5px;border-left:4px solid #28a745;">';
            $hotelSection .= '<strong>&#127970; Hotel:</strong> ' . htmlspecialchars($hotelName2) . $hotelNote;
            if ($hotelLoc2) $hotelSection .= ' &mdash; ' . htmlspecialchars($hotelLoc2);
            $hotelSection .= '</div>';
        }

        $pickupNote = ($request->need_driver_pickup === 'yes' && $request->pickup_time)
            ? '<p><strong>Driver Pickup:</strong> ' . $request->pickup_time . ' at ' . htmlspecialchars($request->pickup_location) . '</p>'
            : '';

        $body = '<!DOCTYPE html><html><head><style>
            body{font-family:Arial,sans-serif;line-height:1.6;}
            .container{max-width:600px;margin:0 auto;padding:20px;}
            .header{background:#e67e22;color:white;padding:15px;text-align:center;border-radius:5px 5px 0 0;}
            .banner{background:#fff3cd;border:1px solid #ffc107;border-radius:5px;padding:10px 14px;margin-bottom:14px;}
            .content{background:#f8f9fa;padding:20px;border:1px solid #ddd;border-top:none;}
            .details{background:white;padding:15px;margin:15px 0;border-radius:5px;border-left:4px solid #e67e22;}
            .driver-box{background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:12px 16px;margin:12px 0;}
            .footer{text-align:center;padding:15px;font-size:12px;color:#666;}
        </style></head><body>
        <div class="container">
            <div class="header"><h2>&#9888; Your Trip Details Have Been Updated</h2></div>
            <div class="content">
                <div class="banner"><strong>&#9888; Note:</strong> The operations team has updated the details for your upcoming trip. Please review the changes below.</div>
                <p>Dear ' . htmlspecialchars($staffName) . ',</p>
                <p>The confirmed details for your trip to <strong>' . htmlspecialchars($request->trip_destination) . '</strong> have been updated.</p>
                <div class="driver-box">
                    <strong>&#128663; Assigned Driver</strong><br>
                    ' . ($driverName  ? 'Name: '  . htmlspecialchars($driverName)  . '<br>' : '') . '
                    ' . ($driverEmail ? 'Email: ' . htmlspecialchars($driverEmail) . '<br>' : '') . '
                    ' . ($driverPhone ? 'Phone: ' . htmlspecialchars($driverPhone)             : '') . '
                </div>
                <div class="details">
                    <h4>Updated Trip Details:</h4>
                    <p><strong>From:</strong> ' . htmlspecialchars($request->vehicle_location_state_name ?? '') . '</p>
                    <p><strong>To:</strong> '   . htmlspecialchars($request->arrival_state_name ?? '') . ' — ' . htmlspecialchars($request->destination_city ?? '') . '</p>
                    <p><strong>Trip Date:</strong>    ' . date('F j, Y', strtotime($request->trip_date)) . '</p>
                    <p><strong>Return Date:</strong>  ' . date('F j, Y', strtotime($request->return_date)) . '</p>
                    <p><strong>Mode of Travel:</strong> ' . ucfirst($modeOfTravel) . '</p>
                    ' . $pickupNote . $flightSection . $hotelSection . '
                </div>
                <p>If you have any questions about these changes, please contact the operations team.</p>
            </div>
            <div class="footer"><p>This is an automated message. Please do not reply.</p></div>
        </div></body></html>';

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
            $mail->addAddress($request->staff_email);
            if ($request->supervisor_email) $mail->addCC($request->supervisor_email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Trip update email failed: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Send cancellation email
     */
    private function sendCancellationEmail($request)
    {
        $staffName = explode('@', $request->staff_email)[0];
        $subject = "Interstate Trip Request Cancelled - " . $request->trip_destination;
        $body = $this->getCancellationEmailTemplate($request, $staffName);
        
        return $this->sendEmail($request->staff_email, $subject, $body);
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendEmail($to, $subject, $body)
    {
        if (empty($to)) {
            error_log("No recipient email address provided");
            return false;
        }
        
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
    
    // ========== HELPER METHODS ==========
    
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

        $request = $this->model->getInterstateRequestById($id);
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

        $request = $this->model->getInterstateRequestById($id);
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
        $stmt = $db->prepare("UPDATE interstate_request SET status = 'rejected', rejection_reason = ?, rejected_by = ?, rejected_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$reason, $user_email, $id]);

        if ($result) {
            $this->sendRejectionEmail($request, $reason, explode('@', $user_email)[0]);
            $_SESSION['success'] = "Request rejected. The requester has been notified.";
        } else {
            $_SESSION['error'] = "Failed to reject request.";
        }

        header('Location: ' . URL . 'intrastate/pendingApprovals');
        exit();
    }

    /**
     * Get rejecter email based on level
     */
    private function getRejecterEmail($request, $level)
    {
        switch ($level) {
            case 'supervisor':
                return $request->supervisor_email;
            case 'security':
                return $request->security_manager_email;
            default:
                return $request->supervisor_email;
        }
    }
    
    /**
     * Check if user is a supervisor with pending requests
     */
    private function isUserSupervisor($user_email)
    {
        $sql = "SELECT COUNT(*) as count FROM interstate_request WHERE supervisor_email = ? AND status = 'pending' AND current_approval_level = 'reviewer'";
        $stmt = $this->model->getDb()->prepare($sql);
        $stmt->execute([$user_email]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count > 0;
    }

    /**
     * Check if user is a security manager with pending requests
     */
    private function isUserSecurityManager($user_email)
    {
        $sql = "SELECT COUNT(*) as count FROM interstate_request WHERE security_manager_email = ? AND status = 'pending' AND current_approval_level = 'security_manager'";
        $stmt = $this->model->getDb()->prepare($sql);
        $stmt->execute([$user_email]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count > 0;
    }
    
    /**
     * Get request counts by status
     */
    private function getRequestCounts($requests)
    {
        $counts = [
            'draft' => 0,
            'pending' => 0,
            'reviewer_approved' => 0,
            'co_reviewer_approved' => 0,
            'manager_approved' => 0,
            'security_approved' => 0,
            'completed' => 0,
            'rejected' => 0,
            'cancelled' => 0,
            'total' => count($requests)
        ];
        
        foreach ($requests as $req) {
            if (isset($counts[$req->status])) {
                $counts[$req->status]++;
            }
        }
        
        return $counts;
    }
    
    /**
     * Require login helper
     */
    private function requireLogin()
    {
        if (!isset($_SESSION['user_email'])) {
            header('Location: ' . URL . 'login');
            exit();
        }
    }
    
    // ========== PAGE DISPLAY METHODS ==========
    
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
                    <strong>Destination City:</strong> <?= htmlspecialchars($request->destination_city); ?><br>
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
    
    // ========== EMAIL TEMPLATES ==========

    /**
     * Supervisor email template
     */
    private function getSupervisorEmailTemplate($request, $staffName, $approveUrl, $declineUrl, $cancelUrl, $isUpdate = false)
    {
        $headerBg = $isUpdate ? '#e67e22' : '#007bff';
        $updateBanner = $isUpdate ? '<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:5px;padding:10px 14px;margin-bottom:14px;"><strong>&#9888; Note:</strong> This trip request has been <strong>updated</strong> by the requester. Please review the new details before approving.</div>' : '';
        $actionText = $isUpdate ? 'updated an interstate trip request' : 'submitted an interstate trip request';
        [$flightSection, $hotelSection] = $this->buildEmailTripSections($request);
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
                    <h2>Interstate Trip Request Approval Required</h2>
                </div>
                <div class="content">
                    ' . $updateBanner . '
                    <p>Dear Supervisor,</p>
                    <p>A staff member, <strong>' . htmlspecialchars($staffName) . '</strong>, has ' . $actionText . ' for your approval.</p>

                    <div class="details">
                        <h4>Trip Details:</h4>
                        <p><strong>Staff:</strong> ' . htmlspecialchars($request->staff_email) . ' / ' . htmlspecialchars($request->staff_phone) . '</p>
                        <p><strong>TAF Filled &amp; Approved:</strong> ' . ($request->taf_approved == 'yes' ? '<span style="color:#28a745;font-weight:bold;">Yes</span>' : '<span style="color:#dc3545;font-weight:bold;">No</span>') . '</p>
                        <p><strong>Purpose:</strong> ' . nl2br(htmlspecialchars($request->purpose)) . '</p>
                        <p><strong>From:</strong> ' . htmlspecialchars($request->vehicle_location_state_name ?? 'N/A') . '</p>
                        <p><strong>To:</strong> ' . htmlspecialchars($request->arrival_state_name ?? 'N/A') . ' - ' . htmlspecialchars($request->destination_city) . '</p>
                        <p><strong>Destination Address:</strong> ' . htmlspecialchars($request->trip_destination) . '</p>
                        <p><strong>Trip Dates:</strong> ' . date('F j, Y', strtotime($request->trip_date)) . ' - ' . date('F j, Y', strtotime($request->return_date)) . '</p>
                        <p><strong>Total Nights:</strong> ' . $request->total_nights . '</p>
                        <p><strong>Mode of Travel:</strong> ' . strtoupper($request->mode_of_travel) . '</p>
                        ' . ($request->driver_overtime == 'yes' ? '<p style="color: red;"><strong>&#9888; Overtime Required</strong></p>' : '') . '
                    </div>

                    ' . $flightSection . '
                    ' . $hotelSection . '

                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . $approveUrl . '" class="button approve">&#10003; APPROVE</a>
                        <a href="' . $declineUrl . '" class="button decline">&#10007; DECLINE</a>
                        <a href="' . $cancelUrl . '" class="button cancel">&#10227; CANCEL</a>
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
        [$flightSection, $hotelSection] = $this->buildEmailTripSections($request);
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
                    <h2>Security Clearance Required - Interstate Trip Request</h2>
                </div>
                <div class="content">
                    <p>Dear Security Manager,</p>
                    <p>An interstate trip request from <strong>' . htmlspecialchars($staffName) . '</strong> requires your security clearance.</p>
                    
                    <div class="details">
                        <h4>Trip Details:</h4>
                        <p><strong>Staff:</strong> ' . htmlspecialchars($request->staff_email) . ' / ' . htmlspecialchars($request->staff_phone) . '</p>
                        <p><strong>Supervisor:</strong> ' . htmlspecialchars($request->supervisor_email) . '</p>
                        <p><strong>Purpose:</strong> ' . nl2br(htmlspecialchars($request->purpose)) . '</p>
                        <p><strong>From:</strong> ' . htmlspecialchars($request->vehicle_location_state_name ?? 'N/A') . '</p>
                        <p><strong>To:</strong> ' . htmlspecialchars($request->arrival_state_name ?? 'N/A') . ' - ' . htmlspecialchars($request->destination_city) . '</p>
                        <p><strong>Destination Address:</strong> ' . htmlspecialchars($request->trip_destination) . '</p>
                        <p><strong>Route:</strong> ' . ($request->route_information ?: 'Not provided') . '</p>
                        <p><strong>Trip Dates:</strong> ' . date('F j, Y', strtotime($request->trip_date)) . ' - ' . date('F j, Y', strtotime($request->return_date)) . '</p>
                        <p><strong>Mode of Travel:</strong> ' . strtoupper($request->mode_of_travel) . '</p>
                        ' . ($request->driver_overtime == 'yes' ? '<p style="color: red;"><strong>⚠ Overtime Required</strong></p>' : '') . '
                    </div>

                    ' . $flightSection . '
                    ' . $hotelSection . '

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
        [$flightSection, $hotelSection] = $this->buildEmailTripSections($request);
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
                    <h2>Interstate Trip Request Fully Approved - Action Required</h2>
                </div>
                <div class="content">
                    <p>Dear Operations Team,</p>
                    <p>An interstate trip request from <strong>' . htmlspecialchars($staffName) . '</strong> has been fully approved (Supervisor and Security Manager).</p>
                    
                    <div class="details">
                        <h4>Trip Details:</h4>
                        <p><strong>Staff:</strong> ' . htmlspecialchars($request->staff_email) . ' / ' . htmlspecialchars($request->staff_phone) . '</p>
                        <p><strong>Purpose:</strong> ' . nl2br(htmlspecialchars($request->purpose)) . '</p>
                        <p><strong>From:</strong> ' . htmlspecialchars($request->vehicle_location_state_name ?? 'N/A') . '</p>
                        <p><strong>To:</strong> ' . htmlspecialchars($request->arrival_state_name ?? 'N/A') . ' - ' . htmlspecialchars($request->destination_city) . '</p>
                        <p><strong>Destination Address:</strong> ' . htmlspecialchars($request->trip_destination) . '</p>
                        <p><strong>Trip Date:</strong> ' . date('F j, Y', strtotime($request->trip_date)) . '</p>
                        <p><strong>Return Date:</strong> ' . date('F j, Y', strtotime($request->return_date)) . '</p>
                        <p><strong>Arrival Time:</strong> ' . $request->trip_destination_time . '</p>
                        <p><strong>Mode of Travel:</strong> ' . strtoupper($request->mode_of_travel) . '</p>
                        ' . ($request->driver_overtime == 'yes' ? '<p style="color: red;"><strong>⚠ Overtime Required</strong></p>' : '') . '
                        ' . ($request->need_driver_pickup == 'yes' ? '<p><strong>Driver Pickup Required:</strong> at ' . $request->pickup_time . '</p>' : '') . '
                    </div>

                    ' . $flightSection . '
                    ' . $hotelSection . '

                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . $viewUrl . '" class="button">Go to Operations Dashboard</a>
                    </div>
                    
                    <p><small>Please log in to assign a driver and manage the trip.</small></p>
                </div>
                <div class="footer">
                    <p>This is an automated message. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    // Build reusable flight + hotel HTML blocks for email templates
    private function buildEmailTripSections($request)
    {
        $mode = strtolower($request->mode_of_travel ?? 'road');
        $isAir = in_array($mode, ['air', 'both']);

        $flightSection = '';
        if ($isAir) {
            $depAirline = $request->requester_departure_airline ?? null;
            $retAirline = $request->requester_return_airline   ?? null;
            $airportPickup = ($request->require_airport_pickup ?? 'no') == 'yes';
            $airportDest   = $request->airport_pickup_dropoff_destination ?? '';

            $flightSection  = '<div style="background:#e8f4fd;padding:12px 15px;margin:10px 0;border-radius:5px;border-left:4px solid #0d6efd;">';
            $flightSection .= '<strong>&#9992; Flight Details</strong><br>';
            $flightSection .= '<strong>Departure Airline:</strong> ' . htmlspecialchars($depAirline ?: 'Not specified') . '<br>';
            $flightSection .= '<strong>Return Airline:</strong> '    . htmlspecialchars($retAirline ?: 'Not specified') . '<br>';
            if ($airportPickup) {
                $flightSection .= '<strong>Airport Pickup Required:</strong> Yes';
                if ($airportDest) $flightSection .= ' &mdash; Drop-off: ' . htmlspecialchars($airportDest);
                $flightSection .= '<br>';
            }
            $flightSection .= '</div>';
        }

        $hotelSection = '';
        if (($request->require_hotel ?? 'no') == 'yes') {
            $hotelName = $request->hotel_name_from_vendor ?? $request->hotel_other_name ?? null;
            $hotelLoc  = $request->hotel_location ?? '';
            $hotelSection  = '<div style="background:#f0fdf4;padding:12px 15px;margin:10px 0;border-radius:5px;border-left:4px solid #28a745;">';
            $hotelSection .= '<strong>&#127970; Hotel:</strong> ' . htmlspecialchars($hotelName ?: 'Not specified');
            if ($hotelLoc) $hotelSection .= ' &mdash; ' . htmlspecialchars($hotelLoc);
            $hotelSection .= '</div>';
        }

        return [$flightSection, $hotelSection];
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
                    <h2>Interstate Trip Request Declined</h2>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($staffName) . ',</p>
                    <p>Your interstate trip request to <strong>' . htmlspecialchars($request->destination_city) . '</strong> has been declined by <strong>' . htmlspecialchars($rejecterName) . '</strong>.</p>
                    
                    <div class="reason">
                        <h4>Reason:</h4>
                        <p>' . nl2br(htmlspecialchars($reason)) . '</p>
                    </div>
                    
                    <p>Please contact your supervisor for more information or submit a new request.</p>
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
                    <h2>Interstate Trip Request Cancelled</h2>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($staffName) . ',</p>
                    <p>Your interstate trip request to <strong>' . htmlspecialchars($request->destination_city) . '</strong> has been cancelled.</p>
                    
                    <p>If this was a mistake, please submit a new request.</p>
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