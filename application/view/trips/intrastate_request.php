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
                <input type="hidden" name="status" value="<?= isset($request) ? htmlspecialchars($request->status) : '' ?>">
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
                                            <?= htmlspecialchars(ucwords(str_replace(['.','_','-'],' ', explode('@',$supervisor->email)[0]))); ?> &mdash; <?= htmlspecialchars($supervisor->email); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Other Passengers -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Other Passengers <span class="text-muted fw-normal" style="font-size:0.85rem;">(Optional)</span></h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">Search and add other staff members travelling on this trip. They will receive a confirmation email and can track the booking in their portal.</p>

                        <!-- Chip container -->
                        <div id="passengerChips" class="d-flex flex-wrap gap-2 mb-2" style="min-height:32px;"></div>

                        <!-- Search input -->
                        <div class="position-relative">
                            <input type="text" class="form-control" id="passengerSearchInput"
                                   placeholder="Type a name or email to search..." autocomplete="off">
                            <ul id="passengerDropdown"
                                class="list-group shadow"
                                style="display:none; position:absolute; z-index:1050; width:100%; max-height:220px; overflow-y:auto; top:100%; left:0;"></ul>
                        </div>

                        <div id="selectedPassengerCount" class="text-muted small mt-2">No passengers selected</div>
                        <div id="passengerHiddenInputs"></div>
                    </div>
                </div>

                <!-- Embed staff list for JS (exclude self) -->
                <?php
                $staffForJs = array_values(array_filter(
                    array_map(fn($s) => [
                        'email' => $s->email,
                        'name'  => ucwords(str_replace(['.','_','-'],' ', explode('@',$s->email)[0]))
                    ], $allStaff),
                    fn($s) => $s['email'] !== $_SESSION['user_email']
                ));
                ?>
                <script>
                const PASSENGER_STAFF = <?= json_encode($staffForJs, JSON_HEX_TAG) ?>;
                </script>

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
                            <div class="col-md-3 mb-3">
                                <label for="trip_date" class="form-label">Trip Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="trip_date" name="trip_date"
                                       value="<?= isset($request) ? $request->trip_date : '' ?>" required>
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
                                <label for="trip_destination" class="form-label">Trip Destination <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="trip_destination" name="trip_destination"
                                       value="<?= isset($request) ? htmlspecialchars($request->trip_destination) : '' ?>" required>
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
                                    <label class="form-label">Overtime Manager</label>
                                    <input type="text" class="form-control bg-light" id="overtime_manager_display" readonly
                                           placeholder="Auto-filled from state configuration">
                                    <input type="hidden" id="overtime_manager_email_hidden" name="overtime_manager_email">
                                    <small class="text-muted">Assigned automatically based on your selected state.</small>
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
                    <button type="button" class="btn btn-secondary" id="draftBtn" onclick="saveAsDraft()">Save as Draft</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit Request</button>
                    <a href="<?= URL; ?>intrastate/myrequests" class="btn btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Converts email prefix to display name: "rita.kogi" → "Rita Kogi"
function formatName(email) {
    if (!email) return '';
    const prefix = email.split('@')[0];
    return prefix.split(/[._-]/).map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate approvers based on selected state
    const stateSelect = document.getElementById('vehicle_location_state_id');
    const reviewerField = document.getElementById('reviewer_email');
    const coReviewerField = document.getElementById('co_reviewer_email');
    const managerField = document.getElementById('manager_email');
    const securityManagerField = document.getElementById('security_manager_email');
    const overtimeManagerDisplay = document.getElementById('overtime_manager_display');
    const overtimeManagerHidden  = document.getElementById('overtime_manager_email_hidden');

    const reviewerHidden        = document.getElementById('reviewer_email_hidden');
    const coReviewerHidden      = document.getElementById('co_reviewer_email_hidden');
    const managerHidden         = document.getElementById('manager_email_hidden');
    const securityManagerHidden = document.getElementById('security_manager_email_hidden');

    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        if (stateId) {
            fetch('<?= URL; ?>intrastate/getApproversByState?state_id=' + stateId)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        reviewerField.value        = data.reviewer_email + ' — ' + formatName(data.reviewer_email);
                        coReviewerField.value      = data.co_reviewer_email ? data.co_reviewer_email + ' — ' + formatName(data.co_reviewer_email) : 'Not assigned';
                        managerField.value         = data.manager_email + ' — ' + formatName(data.manager_email);
                        securityManagerField.value = data.security_manager_email ? data.security_manager_email + ' — ' + formatName(data.security_manager_email) : 'Not assigned';

                        reviewerHidden.value        = data.reviewer_email;
                        coReviewerHidden.value      = data.co_reviewer_email || '';
                        managerHidden.value         = data.manager_email;
                        securityManagerHidden.value = data.security_manager_email || '';

                        // Overtime manager
                        if (data.overtime_manager_email) {
                            overtimeManagerDisplay.value = data.overtime_manager_email + ' — ' + formatName(data.overtime_manager_email);
                            overtimeManagerHidden.value  = data.overtime_manager_email;
                        } else {
                            overtimeManagerDisplay.value = 'Not configured for this state';
                            overtimeManagerHidden.value  = '';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            overtimeManagerDisplay.value = '';
            overtimeManagerHidden.value  = '';
        }
    });
    
    <?php if (isset($request) && $request->reviewer_email): ?>
    // Edit mode: pre-populate approver fields from saved request data
    function populateApproverFields(reviewer, coReviewer, manager, securityManager, overtimeManager) {
        reviewerField.value        = reviewer        ? reviewer        + ' — ' + formatName(reviewer)        : '';
        coReviewerField.value      = coReviewer      ? coReviewer      + ' — ' + formatName(coReviewer)      : 'Not assigned';
        managerField.value         = manager         ? manager         + ' — ' + formatName(manager)         : '';
        securityManagerField.value = securityManager ? securityManager + ' — ' + formatName(securityManager) : 'Not assigned';
        reviewerHidden.value        = reviewer        || '';
        coReviewerHidden.value      = coReviewer      || '';
        managerHidden.value         = manager         || '';
        securityManagerHidden.value = securityManager || '';
        if (overtimeManagerDisplay) {
            overtimeManagerDisplay.value = overtimeManager ? overtimeManager + ' — ' + formatName(overtimeManager) : 'Not configured for this state';
            overtimeManagerHidden.value  = overtimeManager || '';
        }
    }
    populateApproverFields(
        '<?= addslashes($request->reviewer_email ?? '') ?>',
        '<?= addslashes($request->co_reviewer_email ?? '') ?>',
        '<?= addslashes($request->manager_email ?? '') ?>',
        '<?= addslashes($request->security_manager_email ?? '') ?>',
        '<?= addslashes($request->overtime_manager_email ?? '') ?>'
    );
    <?php else: ?>
    // New form: auto-populate approvers from the pre-selected state
    if (stateSelect.value) {
        stateSelect.dispatchEvent(new Event('change'));
    }
    <?php endif; ?>
    
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
    const btn = document.getElementById('draftBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    document.getElementById('formAction').value = 'draft';
    document.getElementById('tripRequestForm').submit();
}

// Prevent double-submit on the main submit button
document.getElementById('tripRequestForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    if (btn.disabled) { e.preventDefault(); return; }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting…';
});

// ── Passenger chip selector ──────────────────────────────────────────────────
(function () {
    const searchInput   = document.getElementById('passengerSearchInput');
    const dropdown      = document.getElementById('passengerDropdown');
    const chipsEl       = document.getElementById('passengerChips');
    const hiddenInputs  = document.getElementById('passengerHiddenInputs');
    const countEl       = document.getElementById('selectedPassengerCount');

    if (!searchInput) return;

    const selected = new Map(); // email → name

    function updateCount() {
        const n = selected.size;
        countEl.textContent = n > 0 ? n + ' passenger' + (n > 1 ? 's' : '') + ' selected' : 'No passengers selected';
    }

    function addPassenger(email, name) {
        if (selected.has(email)) return;
        selected.set(email, name);

        // Chip
        const chip = document.createElement('span');
        chip.className = 'badge bg-primary d-inline-flex align-items-center gap-1 px-2 py-2';
        chip.style.fontSize = '0.85rem';
        chip.innerHTML = `<i class="fas fa-user-circle"></i> ${name}
            <button type="button" class="btn-close btn-close-white ms-1" aria-label="Remove"
                    style="font-size:0.6rem;" data-email="${email}"></button>`;
        chip.querySelector('button').addEventListener('click', () => removePassenger(email));
        chipsEl.appendChild(chip);

        // Hidden input
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'passenger_emails[]';
        inp.value = email;
        inp.id    = 'pax_hidden_' + btoa(email).replace(/=/g, '');
        hiddenInputs.appendChild(inp);

        updateCount();
    }

    function removePassenger(email) {
        selected.delete(email);
        chipsEl.querySelectorAll('button[data-email]').forEach(btn => {
            if (btn.dataset.email === email) btn.closest('.badge').remove();
        });
        const inp = document.getElementById('pax_hidden_' + btoa(email).replace(/=/g, ''));
        if (inp) inp.remove();
        updateCount();
    }

    function renderDropdown(results) {
        dropdown.innerHTML = '';
        if (!results.length) {
            dropdown.innerHTML = '<li class="list-group-item text-muted small py-2 px-3">No results found</li>';
        } else {
            results.slice(0, 30).forEach(staff => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action py-2 px-3' + (selected.has(staff.email) ? ' active' : '');
                li.style.cursor = 'pointer';
                li.innerHTML = `<strong>${staff.name}</strong> <span class="text-muted small ms-1">${staff.email}</span>`
                    + (selected.has(staff.email) ? ' <i class="fas fa-check ms-1"></i>' : '');
                li.addEventListener('mousedown', e => {
                    e.preventDefault();
                    if (!selected.has(staff.email)) {
                        addPassenger(staff.email, staff.name);
                    } else {
                        removePassenger(staff.email);
                    }
                    searchInput.value = '';
                    dropdown.style.display = 'none';
                });
                dropdown.appendChild(li);
            });
        }
        dropdown.style.display = 'block';
    }

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        if (!q) { dropdown.style.display = 'none'; return; }
        const results = PASSENGER_STAFF.filter(s =>
            s.name.toLowerCase().includes(q) || s.email.toLowerCase().includes(q)
        );
        renderDropdown(results);
    });

    searchInput.addEventListener('blur', () => {
        setTimeout(() => { dropdown.style.display = 'none'; }, 150);
    });

    searchInput.addEventListener('focus', function () {
        if (this.value.trim()) this.dispatchEvent(new Event('input'));
    });
}());
</script>