<div class="container-fluid px-4">
    <h3 class="mt-4"><?= isset($request) ? 'Edit' : 'New' ?> Intrastate Trip Request</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= URL; ?>intrastate/myrequests">My Requests</a></li>
        <li class="breadcrumb-item"><?= isset($request) ? 'Edit' : 'New' ?> Request</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-car me-1"></i>
            Trip Request Form
        </div>
        <div class="card-body">
            <form id="tripRequestForm" method="POST" action="<?= URL; ?>intrastate/save">
                <input type="hidden" name="id" value="<?= isset($request) ? $request->id : '' ?>">
                <input type="hidden" name="action" id="formAction" value="submit">
                
                <!-- Staff Information -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Staff Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="staff_email" class="form-label">Staff Email</label>
                                <input type="email" class="form-control" id="staff_email" 
                                       value="<?= $_SESSION['user_email']; ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="staff_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="staff_phone" name="staff_phone" 
                                       value="<?= isset($request) ? htmlspecialchars($request->staff_phone) : '' ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="supervisor_email" class="form-label">Supervisor <span class="text-danger">*</span></label>
                                <select class="form-control" id="supervisor_email" name="supervisor_email" required>
                                    <option value="">Select Supervisor</option>
                                    <?php foreach ($supervisors as $supervisor): ?>
                                        <option value="<?= htmlspecialchars($supervisor->email); ?>" 
                                            <?= (isset($request) && $request->supervisor_email == $supervisor->email) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($supervisor->email); ?> (<?= htmlspecialchars($supervisor->role); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Trip Details -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Trip Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_location_state_id" class="form-label">Vehicle Location (State) <span class="text-danger">*</span></label>
                                <select class="form-control" id="vehicle_location_state_id" name="vehicle_location_state_id" required>
                                    <option value="">Select State</option>
                                    <?php foreach ($eaStates as $state): ?>
                                        <option value="<?= $state->state_id; ?>" 
                                            <?= (isset($request) && $request->vehicle_location_state_id == $state->state_id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($state->country_name); ?> - <?= htmlspecialchars($state->state_name); ?> (<?= htmlspecialchars($state->state_code); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="trip_destination" class="form-label">Trip Destination (City/Town) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="trip_destination" name="trip_destination" 
                                       value="<?= isset($request) ? htmlspecialchars($request->trip_destination) : '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="trip_date" class="form-label">Trip Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="trip_date" name="trip_date" 
                                       value="<?= isset($request) ? $request->trip_date : '' ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="return_date" class="form-label">Return Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="return_date" name="return_date" 
                                       value="<?= isset($request) ? $request->return_date : '' ?>" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="total_nights" class="form-label">Total Nights</label>
                                <input type="text" class="form-control" id="total_nights" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="trip_destination_time" class="form-label">Destination Arrival Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="trip_destination_time" name="trip_destination_time" 
                                       value="<?= isset($request) ? $request->trip_destination_time : '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pickup_location" class="form-label">Pickup Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pickup_location" name="pickup_location" 
                                       value="<?= isset($request) ? htmlspecialchars($request->pickup_location) : '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="funder_code_id" class="form-label">Funder Code <span class="text-danger">*</span></label>
                                <select class="form-control" id="funder_code_id" name="funder_code_id" required>
                                    <option value="">Select Funder Code</option>
                                    <?php foreach ($funderCodes as $funder): ?>
                                        <option value="<?= $funder->id; ?>" 
                                            <?= (isset($request) && $request->funder_code_id == $funder->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($funder->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose of Trip <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" required><?= isset($request) ? htmlspecialchars($request->purpose) : '' ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="route_information" class="form-label">Route Information</label>
                                <textarea class="form-control" id="route_information" name="route_information" rows="2"><?= isset($request) ? htmlspecialchars($request->route_information) : '' ?></textarea>
                                <small class="text-muted">Optional: Provide details about the route to be taken</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Auto-populated Approvers -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Approvers (Auto-populated based on location)</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="reviewer_email" class="form-label">Reviewer</label>
                                <input type="text" class="form-control" id="reviewer_email" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="co_reviewer_email" class="form-label">Co-Reviewer</label>
                                <input type="text" class="form-control" id="co_reviewer_email" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="manager_email" class="form-label">Manager</label>
                                <input type="text" class="form-control" id="manager_email" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="security_manager_email" class="form-label">Security Manager</label>
                                <input type="text" class="form-control" id="security_manager_email" readonly>
                            </div>
                        </div>
                        <input type="hidden" id="reviewer_email_hidden" name="reviewer_email">
                        <input type="hidden" id="co_reviewer_email_hidden" name="co_reviewer_email">
                        <input type="hidden" id="manager_email_hidden" name="manager_email">
                        <input type="hidden" id="security_manager_email_hidden" name="security_manager_email">
                    </div>
                </div>
                
                <!-- Driver Overtime -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Driver Overtime</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Will the trip exceed 6:00 PM (1800hrs)?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="driver_overtime" id="overtime_no" value="no" checked>
                                        <label class="form-check-label" for="overtime_no">No</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="driver_overtime" id="overtime_yes" value="yes">
                                        <label class="form-check-label" for="overtime_yes">Yes</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="overtimeSection" style="display: none;">
                            <hr>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="trip_activity" class="form-label">Trip Activity</label>
                                    <textarea class="form-control" id="trip_activity" name="trip_activity" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="reason_for_overtime" class="form-label">Reason for Overtime</label>
                                    <textarea class="form-control" id="reason_for_overtime" name="reason_for_overtime" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="overtime_manager_email" class="form-label">Overtime Trip Manager</label>
                                    <select class="form-control" id="overtime_manager_email" name="overtime_manager_email">
                                        <option value="">Select Manager</option>
                                        <?php foreach ($overtimeManagers as $manager): ?>
                                            <option value="<?= htmlspecialchars($manager->email); ?>">
                                                <?= htmlspecialchars($manager->email); ?> (<?= htmlspecialchars($manager->role); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Driver Pickup -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Driver Pickup</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Do you need a driver for pickup?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="need_driver_pickup" id="pickup_no" value="no" checked>
                                        <label class="form-check-label" for="pickup_no">No</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="need_driver_pickup" id="pickup_yes" value="yes">
                                        <label class="form-check-label" for="pickup_yes">Yes</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="pickupTimeSection" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pickup_time" class="form-label">Pickup Time</label>
                                    <input type="time" class="form-control" id="pickup_time" name="pickup_time">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Buttons -->
                <div class="text-end">
                    <button type="button" class="btn btn-secondary" onclick="saveAsDraft()">Save as Draft</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                    <a href="<?= URL; ?>intrastate/myrequests" class="btn btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate total nights
    const tripDate = document.getElementById('trip_date');
    const returnDate = document.getElementById('return_date');
    const totalNights = document.getElementById('total_nights');
    
    function calculateNights() {
        if (tripDate.value && returnDate.value) {
            const start = new Date(tripDate.value);
            const end = new Date(returnDate.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            totalNights.value = diffDays;
        }
    }
    
    tripDate.addEventListener('change', calculateNights);
    returnDate.addEventListener('change', calculateNights);
    
    // Auto-populate approvers based on selected state
    const stateSelect = document.getElementById('vehicle_location_state_id');
    const reviewerField = document.getElementById('reviewer_email');
    const coReviewerField = document.getElementById('co_reviewer_email');
    const managerField = document.getElementById('manager_email');
    const securityManagerField = document.getElementById('security_manager_email');
    
    const reviewerHidden = document.getElementById('reviewer_email_hidden');
    const coReviewerHidden = document.getElementById('co_reviewer_email_hidden');
    const managerHidden = document.getElementById('manager_email_hidden');
    const securityManagerHidden = document.getElementById('security_manager_email_hidden');
    
    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        if (stateId) {
            fetch('<?= URL; ?>intrastate/getApproversByState?state_id=' + stateId)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        reviewerField.value = data.reviewer_email + ' (' + (data.reviewer_name || data.reviewer_email.split('@')[0]) + ')';
                        coReviewerField.value = data.co_reviewer_email ? data.co_reviewer_email + ' (' + (data.co_reviewer_name || data.co_reviewer_email.split('@')[0]) + ')' : 'Not assigned';
                        managerField.value = data.manager_email + ' (' + (data.manager_name || data.manager_email.split('@')[0]) + ')';
                        securityManagerField.value = data.security_manager_email ? data.security_manager_email + ' (' + (data.security_manager_name || data.security_manager_email.split('@')[0]) + ')' : 'Not assigned';
                        
                        reviewerHidden.value = data.reviewer_email;
                        coReviewerHidden.value = data.co_reviewer_email || '';
                        managerHidden.value = data.manager_email;
                        securityManagerHidden.value = data.security_manager_email || '';
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });
    
    // Trigger change if editing with existing state
    if (stateSelect.value) {
        stateSelect.dispatchEvent(new Event('change'));
        calculateNights();
    }
    
    // Driver overtime toggle
    const overtimeYes = document.getElementById('overtime_yes');
    const overtimeNo = document.getElementById('overtime_no');
    const overtimeSection = document.getElementById('overtimeSection');
    
    function toggleOvertime() {
        if (overtimeYes.checked) {
            overtimeSection.style.display = 'block';
        } else {
            overtimeSection.style.display = 'none';
        }
    }
    
    overtimeYes.addEventListener('change', toggleOvertime);
    overtimeNo.addEventListener('change', toggleOvertime);
    
    // Driver pickup toggle
    const pickupYes = document.getElementById('pickup_yes');
    const pickupNo = document.getElementById('pickup_no');
    const pickupTimeSection = document.getElementById('pickupTimeSection');
    
    function togglePickup() {
        if (pickupYes.checked) {
            pickupTimeSection.style.display = 'block';
        } else {
            pickupTimeSection.style.display = 'none';
        }
    }
    
    pickupYes.addEventListener('change', togglePickup);
    pickupNo.addEventListener('change', togglePickup);
    
    // Pre-fill values if editing
    <?php if (isset($request)): ?>
        if (document.querySelector('input[name="driver_overtime"][value="<?= $request->driver_overtime ?>"]')) {
            document.querySelector('input[name="driver_overtime"][value="<?= $request->driver_overtime ?>"]').checked = true;
            toggleOvertime();
        }
        if (document.querySelector('input[name="need_driver_pickup"][value="<?= $request->need_driver_pickup ?>"]')) {
            document.querySelector('input[name="need_driver_pickup"][value="<?= $request->need_driver_pickup ?>"]').checked = true;
            togglePickup();
        }
        if ('<?= $request->trip_activity ?>') {
            document.getElementById('trip_activity').value = '<?= htmlspecialchars($request->trip_activity) ?>';
        }
        if ('<?= $request->reason_for_overtime ?>') {
            document.getElementById('reason_for_overtime').value = '<?= htmlspecialchars($request->reason_for_overtime) ?>';
        }
        if ('<?= $request->overtime_manager_email ?>') {
            document.getElementById('overtime_manager_email').value = '<?= $request->overtime_manager_email ?>';
        }
        if ('<?= $request->pickup_time ?>') {
            document.getElementById('pickup_time').value = '<?= $request->pickup_time ?>';
        }
    <?php endif; ?>
});

function saveAsDraft() {
    document.getElementById('formAction').value = 'draft';
    document.getElementById('tripRequestForm').submit();
}
</script>