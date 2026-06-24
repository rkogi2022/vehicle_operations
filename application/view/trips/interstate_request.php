<div class="container-fluid px-4">
    <h3 class="mt-4"><?= isset($request) ? 'Edit' : 'New' ?> Interstate Trip Request</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= URL; ?>interstate">My Requests</a></li>
        <li class="breadcrumb-item"><?= isset($request) ? 'Edit' : 'New' ?> Request</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-road me-1"></i>
            Interstate Trip Request Form
        </div>
        <div class="card-body">
            <form id="tripRequestForm" method="POST" action="<?= URL; ?>interstate/save">
                <input type="hidden" name="id" value="<?= isset($request) ? $request->id : '' ?>">
                <input type="hidden" name="action" id="formAction" value="submit">
                <input type="hidden" name="status" value="<?= isset($request) ? $request->status : '' ?>">
                
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
                                    <?php if (isset($supervisors)): ?>
                                        <?php foreach ($supervisors as $supervisor): ?>
                                            <option value="<?= htmlspecialchars($supervisor->email); ?>"
                                                <?= (isset($request) && $request->supervisor_email == $supervisor->email) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucwords(str_replace(['.','_','-'],' ', explode('@',$supervisor->email)[0]))); ?> &mdash; <?= htmlspecialchars($supervisor->email); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Have you filled your TAF and received approval? <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="taf_approved" id="taf_yes" value="yes" required
                                            <?= (isset($request) && $request->taf_approved == 'yes') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="taf_yes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="taf_approved" id="taf_no" value="no"
                                            <?= (isset($request) && $request->taf_approved == 'no') || !isset($request) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="taf_no">No</label>
                                    </div>
                                </div>
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
                                <label for="vehicle_location_state_id" class="form-label">Departure State (Vehicle Location) <span class="text-danger">*</span></label>
                                <select class="form-control" id="vehicle_location_state_id" name="vehicle_location_state_id" required>
                                    <option value="">Select Departure State</option>
                                    <?php if (isset($departure_states)): ?>
                                        <?php foreach ($departure_states as $state): ?>
                                            <option value="<?= $state->id; ?>"
                                                <?= (isset($request) && $request->vehicle_location_state_id == $state->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($state->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="arrival_location_state_id" class="form-label">Arrival State <span class="text-danger">*</span></label>
                                <select class="form-control" id="arrival_location_state_id" name="arrival_location_state_id" required>
                                    <option value="">Select Arrival State</option>
                                    <?php if (isset($states)): ?>
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?= $state->id; ?>"
                                                <?= (isset($request) && $request->arrival_location_state_id == $state->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($state->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
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
                                <input type="text" class="form-control" id="total_nights" name="total_nights" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="trip_destination_time" class="form-label">Destination Arrival Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="trip_destination_time" name="trip_destination_time"
                                       value="<?= isset($request) ? $request->trip_destination_time : '' ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="pickup_location" class="form-label">Pickup Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pickup_location" name="pickup_location"
                                       value="<?= isset($request) ? htmlspecialchars($request->pickup_location) : '' ?>" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="destination_city" class="form-label">Trip Destination <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="destination_city" name="destination_city"
                                       value="<?= isset($request) ? htmlspecialchars($request->destination_city) : '' ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="funder_code_id" class="form-label">Funder Code <span class="text-danger">*</span></label>
                                <select class="form-control" id="funder_code_id" name="funder_code_id" required>
                                    <option value="">Select Funder Code</option>
                                    <?php if (isset($funder_codes)): ?>
                                        <?php foreach ($funder_codes as $funder): ?>
                                            <option value="<?= $funder->id; ?>" 
                                                <?= (isset($request) && $request->funder_code_id == $funder->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($funder->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
                
                <!-- Hotel Accommodation -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Hotel Accommodation</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Do you require hotel accommodation?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="require_hotel" id="hotel_no" value="no" checked>
                                        <label class="form-check-label" for="hotel_no">No</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="require_hotel" id="hotel_yes" value="yes">
                                        <label class="form-check-label" for="hotel_yes">Yes</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="hotelDetails" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Hotels will be filtered based on your selected <strong>Arrival State</strong>.
                                <span id="selectedArrivalState"></span>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="hotel_option" class="form-label">Hotel Selection <span class="text-danger">*</span></label>
                                    <select class="form-control" id="hotel_option" name="hotel_option">
                                        <option value="">Select Hotel Option</option>
                                        <option value="existing">Select from existing hotels</option>
                                        <option value="other">Other (Manual entry)</option>
                                    </select>
                                </div>
                            </div>

                            <div id="existingHotelSection" style="display: none;">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="hotel_id" class="form-label">Select Hotel <span class="text-danger">*</span></label>
                                        <select class="form-control" id="hotel_id" name="hotel_id">
                                            <option value="">-- Select Arrival State first --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="hotel_location_display" class="form-label">Hotel Location</label>
                                        <input type="text" class="form-control" id="hotel_location_display" name="hotel_location" placeholder="Auto-filled or type manually">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="hotel_location_state_id_display" class="form-label">State</label>
                                        <input type="text" class="form-control" id="hotel_location_state_id_display" readonly>
                                        <input type="hidden" id="hotel_location_state_id" name="hotel_location_state_id">
                                    </div>
                                </div>
                            </div>

                            <div id="otherHotelSection" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="hotel_other_name" class="form-label">Hotel Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="hotel_other_name" name="hotel_other_name"
                                               value="<?= isset($request) ? htmlspecialchars($request->hotel_other_name) : '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="hotel_other_location" class="form-label">Hotel Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="hotel_other_location" name="hotel_other_location"
                                               value="<?= isset($request) ? htmlspecialchars($request->hotel_location) : '' ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="hotel_other_state_id" class="form-label">State (Location) <span class="text-danger">*</span></label>
                                        <select class="form-control" id="hotel_other_state_id" name="hotel_other_state_id">
                                            <option value="">Select State</option>
                                            <?php if (isset($states)): ?>
                                                <?php foreach ($states as $state): ?>
                                                    <option value="<?= $state->id; ?>"
                                                        <?= (isset($request) && $request->hotel_location_state_id == $state->id) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($state->name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mode of Travel -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Mode of Travel</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Select Mode of Travel <span class="text-danger">*</span></label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mode_of_travel" id="mode_road" value="road"
                                            <?= (!isset($request) || $request->mode_of_travel == 'road') ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="mode_road">Road</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mode_of_travel" id="mode_air" value="air"
                                            <?= (isset($request) && $request->mode_of_travel == 'air') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="mode_air">Air</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mode_of_travel" id="mode_both" value="both"
                                            <?= (isset($request) && $request->mode_of_travel == 'both') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="mode_both">Both (Road & Air)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Airport Pickup (shown when mode includes air) -->
                <div class="card mb-3" id="airportPickupCard" style="display: none;">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Airport Pickup Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Do you require airport pickup?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="require_airport_pickup" id="airport_pickup_no" value="no" checked>
                                        <label class="form-check-label" for="airport_pickup_no">No</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="require_airport_pickup" id="airport_pickup_yes" value="yes">
                                        <label class="form-check-label" for="airport_pickup_yes">Yes</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="airportPickupDetails" style="display: none;">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="airport_pickup_dropoff_destination" class="form-label">Drop-off Destination after Pickup</label>
                                    <input type="text" class="form-control" id="airport_pickup_dropoff_destination" name="airport_pickup_dropoff_destination"
                                           value="<?= isset($request) ? htmlspecialchars($request->airport_pickup_dropoff_destination) : '' ?>">
                                    <small class="text-muted">Where should you be dropped off after airport pickup?</small>
                                </div>
                            </div>
                        </div>

                        <!-- Flight Details (filled by requester) -->
                        <div id="requesterFlightDetails">
                            <hr>
                            <h6>Flight Details (To be filled by requester)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="requester_departure_flight_airline_id" class="form-label">Departure Airline</label>
                                    <select class="form-control" id="requester_departure_flight_airline_id" name="requester_departure_flight_airline_id">
                                        <option value="">Select Airline</option>
                                        <?php if (isset($airlines)): ?>
                                            <?php foreach ($airlines as $airline): ?>
                                                <option value="<?= $airline->id; ?>"
                                                    <?= (isset($request) && $request->requester_departure_flight_airline_id == $airline->id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($airline->name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="requester_return_flight_airline_id" class="form-label">Return Airline</label>
                                    <select class="form-control" id="requester_return_flight_airline_id" name="requester_return_flight_airline_id">
                                        <option value="">Select Airline</option>
                                        <?php if (isset($airlines)): ?>
                                            <?php foreach ($airlines as $airline): ?>
                                                <option value="<?= $airline->id; ?>"
                                                    <?= (isset($request) && $request->requester_return_flight_airline_id == $airline->id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($airline->name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <small class="text-muted">Note: Flight schedules and details will be confirmed by operations team during approval.</small>
                        </div>
                    </div>
                </div>
                
                <!-- Auto-populated Approvers -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Approvers (Auto-populated based on departure state)</h6>
                    </div>
                    <div class="card-body">
                        <div id="approverFallbackNotice" class="alert alert-warning py-2 mb-3" style="display:none;">
                            <i class="fas fa-exclamation-triangle"></i> This state has no specific EA configuration — using country-level default approvers.
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="reviewer_email" class="form-label">Reviewer</label>
                                <input type="text" class="form-control" id="reviewer_email" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="co_reviewer_email" class="form-label">Co-Reviewer</label>
                                <input type="text" class="form-control" id="co_reviewer_email" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="manager_email" class="form-label">Manager</label>
                                <input type="text" class="form-control" id="manager_email" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
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
                                <label class="form-label">Will the trip exceed 5:30 PM (1730hrs)?</label>
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
                                    <textarea class="form-control" id="trip_activity" name="trip_activity" rows="2"><?= isset($request) ? htmlspecialchars($request->trip_activity) : '' ?></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="reason_for_overtime" class="form-label">Reason for Overtime</label>
                                    <textarea class="form-control" id="reason_for_overtime" name="reason_for_overtime" rows="2"><?= isset($request) ? htmlspecialchars($request->reason_for_overtime) : '' ?></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Overtime Manager</label>
                                    <input type="text" class="form-control bg-light" id="overtime_manager_display" readonly
                                           placeholder="Auto-filled from state configuration"
                                           value="<?= isset($request) && $request->overtime_manager_email ? htmlspecialchars($request->overtime_manager_email . ' (' . explode('@', $request->overtime_manager_email)[0] . ')') : '' ?>">
                                    <input type="hidden" id="overtime_manager_email_hidden" name="overtime_manager_email"
                                           value="<?= isset($request) ? htmlspecialchars($request->overtime_manager_email ?? '') : '' ?>">
                                    <small class="text-muted">Assigned automatically based on your selected state.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="need_driver_pickup" value="no">
                
                <!-- Form Buttons -->
                <div class="text-end">
                    <button type="button" class="btn btn-secondary" id="draftBtn" onclick="saveAsDraft()">Save as Draft</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit Request</button>
                    <a href="<?= URL; ?>interstate" class="btn btn-danger">Cancel</a>
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

// Store all hotels data
let allHotels = [];

// Load all hotels on page load
<?php if (isset($hotels)): ?>
allHotels = <?= json_encode($hotels); ?>;
<?php endif; ?>

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
    
    // Mode of travel toggle
    const modeRoad = document.getElementById('mode_road');
    const modeAir = document.getElementById('mode_air');
    const modeBoth = document.getElementById('mode_both');
    const airportPickupCard = document.getElementById('airportPickupCard');
    
    function toggleAirportPickupCard() {
        if (modeAir.checked || modeBoth.checked) {
            airportPickupCard.style.display = 'block';
        } else {
            airportPickupCard.style.display = 'none';
        }
    }
    
    modeRoad.addEventListener('change', toggleAirportPickupCard);
    modeAir.addEventListener('change', toggleAirportPickupCard);
    modeBoth.addEventListener('change', toggleAirportPickupCard);
    toggleAirportPickupCard();
    
    // Airport pickup toggle
    const airportPickupYes = document.getElementById('airport_pickup_yes');
    const airportPickupNo = document.getElementById('airport_pickup_no');
    const airportPickupDetails = document.getElementById('airportPickupDetails');
    
    function toggleAirportPickupDetails() {
        if (airportPickupYes.checked) {
            airportPickupDetails.style.display = 'block';
        } else {
            airportPickupDetails.style.display = 'none';
        }
    }
    
    airportPickupYes.addEventListener('change', toggleAirportPickupDetails);
    airportPickupNo.addEventListener('change', toggleAirportPickupDetails);
    
    // Auto-populate approvers based on selected departure state
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
            fetch('<?= URL; ?>interstate/getEaStateByStateId?state_id=' + stateId)
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        const config = data.data;

                        reviewerField.value        = config.reviewer_email + ' — ' + formatName(config.reviewer_email);
                        coReviewerField.value      = config.co_reviewer_email ? config.co_reviewer_email + ' — ' + formatName(config.co_reviewer_email) : 'Not assigned';
                        managerField.value         = config.manager_email + ' — ' + formatName(config.manager_email);
                        securityManagerField.value = config.security_manager_email ? config.security_manager_email + ' — ' + formatName(config.security_manager_email) : 'Not assigned';

                        reviewerHidden.value        = config.reviewer_email;
                        coReviewerHidden.value      = config.co_reviewer_email || '';
                        managerHidden.value         = config.manager_email;
                        securityManagerHidden.value = config.security_manager_email || '';

                        // Overtime manager
                        if (config.overtime_manager_email) {
                            overtimeManagerDisplay.value = config.overtime_manager_email + ' — ' + formatName(config.overtime_manager_email);
                            overtimeManagerHidden.value  = config.overtime_manager_email;
                        } else {
                            overtimeManagerDisplay.value = 'Not configured';
                            overtimeManagerHidden.value  = '';
                        }

                        document.getElementById('approverFallbackNotice').style.display = data.is_fallback ? 'block' : 'none';
                    } else {
                        reviewerField.value = 'Not configured';
                        coReviewerField.value = 'Not configured';
                        managerField.value = 'Not configured';
                        securityManagerField.value = 'Not configured';
                        reviewerHidden.value = '';
                        coReviewerHidden.value = '';
                        managerHidden.value = '';
                        securityManagerHidden.value = '';
                        overtimeManagerDisplay.value = '';
                        overtimeManagerHidden.value = '';
                        document.getElementById('approverFallbackNotice').style.display = 'none';
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            reviewerField.value = '';
            coReviewerField.value = '';
            managerField.value = '';
            securityManagerField.value = '';
            reviewerHidden.value = '';
            coReviewerHidden.value = '';
            managerHidden.value = '';
            securityManagerHidden.value = '';
            overtimeManagerDisplay.value = '';
            overtimeManagerHidden.value  = '';
            document.getElementById('approverFallbackNotice').style.display = 'none';
        }
    });
    
    // Hotel toggle
    const hotelYes = document.getElementById('hotel_yes');
    const hotelNo = document.getElementById('hotel_no');
    const hotelDetails = document.getElementById('hotelDetails');
    const hotelOption = document.getElementById('hotel_option');
    const existingHotelSection = document.getElementById('existingHotelSection');
    const otherHotelSection = document.getElementById('otherHotelSection');
    const arrivalStateSelect = document.getElementById('arrival_location_state_id');
    const hotelSelect = document.getElementById('hotel_id');
    const selectedArrivalStateSpan = document.getElementById('selectedArrivalState');
    
    function toggleHotelDetails() {
        if (hotelYes.checked) {
            hotelDetails.style.display = 'block';
            // Trigger arrival state change to load hotels
            if (arrivalStateSelect.value) {
                loadHotelsByState(arrivalStateSelect.value);
            }
        } else {
            hotelDetails.style.display = 'none';
            existingHotelSection.style.display = 'none';
            otherHotelSection.style.display = 'none';
            hotelOption.value = '';
        }
    }
    
    // Load hotels by state ID
    function loadHotelsByState(stateId) {
        const stateName = arrivalStateSelect.options[arrivalStateSelect.selectedIndex]?.text || '';
        selectedArrivalStateSpan.innerHTML = `<strong>${stateName}</strong>`;
        
        // Filter hotels by state
        const filteredHotels = allHotels.filter(hotel => hotel.state_id == stateId);
        
        // Clear and populate hotel select
        hotelSelect.innerHTML = '<option value="">Select Hotel</option>';
        
        if (filteredHotels.length > 0) {
            filteredHotels.forEach(hotel => {
                const option = document.createElement('option');
                option.value = hotel.id;
                option.setAttribute('data-state-id', hotel.state_id);
                option.setAttribute('data-state-name', hotel.state_name);
                option.setAttribute('data-location', hotel.location || '');
                option.textContent = `${hotel.name} - ${hotel.state_name}`;
                hotelSelect.appendChild(option);
            });
            
            // If there's a previously selected hotel ID, try to select it
            <?php if (isset($request) && $request->hotel_id): ?>
            if (filteredHotels.some(h => h.id == <?= $request->hotel_id ?>)) {
                hotelSelect.value = '<?= $request->hotel_id ?>';
                hotelSelect.dispatchEvent(new Event('change'));
            }
            <?php endif; ?>
        } else {
            // No hotels found, show message
            const option = document.createElement('option');
            option.value = '';
            option.textContent = '-- No hotels found in this state --';
            option.disabled = true;
            hotelSelect.appendChild(option);
        }
    }
    
    // Listen for arrival state change to reload hotels
    arrivalStateSelect.addEventListener('change', function() {
        if (hotelYes.checked && hotelOption.value === 'existing') {
            loadHotelsByState(this.value);
        }
    });
    
    hotelYes.addEventListener('change', toggleHotelDetails);
    hotelNo.addEventListener('change', toggleHotelDetails);
    
    hotelOption.addEventListener('change', function() {
        if (this.value === 'existing') {
            existingHotelSection.style.display = 'block';
            otherHotelSection.style.display = 'none';
            if (arrivalStateSelect.value) {
                loadHotelsByState(arrivalStateSelect.value);
            } else {
                alert('Please select Arrival State first to see available hotels.');
                hotelOption.value = '';
            }
        } else if (this.value === 'other') {
            existingHotelSection.style.display = 'none';
            otherHotelSection.style.display = 'block';
        } else {
            existingHotelSection.style.display = 'none';
            otherHotelSection.style.display = 'none';
        }
    });
    
    // Hotel selection - auto-populate location and state
    const hotelLocationDisplay = document.getElementById('hotel_location_display');
    const hotelStateDisplay = document.getElementById('hotel_location_state_id_display');
    const hotelStateHidden = document.getElementById('hotel_location_state_id');

    hotelSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const location = selectedOption.getAttribute('data-location') || '';
        const stateName = selectedOption.getAttribute('data-state-name') || '';
        const stateId = selectedOption.getAttribute('data-state-id') || '';

        hotelLocationDisplay.value = location;
        hotelStateDisplay.value = stateName;
        hotelStateHidden.value = stateId;
    });
    
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
    
    // Pre-fill values if editing
    <?php if (isset($request)): ?>
        if (document.querySelector('input[name="mode_of_travel"][value="<?= $request->mode_of_travel ?>"]')) {
            document.querySelector('input[name="mode_of_travel"][value="<?= $request->mode_of_travel ?>"]').checked = true;
            toggleAirportPickupCard();
        }
        if (document.querySelector('input[name="require_airport_pickup"][value="<?= $request->require_airport_pickup ?>"]')) {
            document.querySelector('input[name="require_airport_pickup"][value="<?= $request->require_airport_pickup ?>"]').checked = true;
            toggleAirportPickupDetails();
        }
        if (document.querySelector('input[name="require_hotel"][value="<?= $request->require_hotel ?>"]')) {
            document.querySelector('input[name="require_hotel"][value="<?= $request->require_hotel ?>"]').checked = true;
            toggleHotelDetails();
        }
        if (document.querySelector('input[name="driver_overtime"][value="<?= $request->driver_overtime ?>"]')) {
            document.querySelector('input[name="driver_overtime"][value="<?= $request->driver_overtime ?>"]').checked = true;
            toggleOvertime();
        }
        if ('<?= $request->hotel_id ?>') {
            hotelOption.value = 'existing';
            hotelOption.dispatchEvent(new Event('change'));
        } else if ('<?= $request->hotel_other_name ?>') {
            hotelOption.value = 'other';
            hotelOption.dispatchEvent(new Event('change'));
        }
        if ('<?= $request->trip_activity ?>') {
            document.getElementById('trip_activity').value = '<?= addslashes($request->trip_activity) ?>';
        }
        if ('<?= $request->reason_for_overtime ?>') {
            document.getElementById('reason_for_overtime').value = '<?= addslashes($request->reason_for_overtime) ?>';
        }
        if ('<?= $request->overtime_manager_email ?>') {
            document.getElementById('overtime_manager_email').value = '<?= $request->overtime_manager_email ?>';
        }
        // Pre-populate approver fields from saved request data instead of re-fetching
        (function() {
            const r  = '<?= addslashes($request->reviewer_email ?? '') ?>';
            const cr = '<?= addslashes($request->co_reviewer_email ?? '') ?>';
            const m  = '<?= addslashes($request->manager_email ?? '') ?>';
            const sm = '<?= addslashes($request->security_manager_email ?? '') ?>';
            const om = '<?= addslashes($request->overtime_manager_email ?? '') ?>';
            if (r) {
                reviewerField.value        = r  + ' — ' + formatName(r);
                coReviewerField.value      = cr ? cr + ' — ' + formatName(cr) : 'Not assigned';
                managerField.value         = m  + ' — ' + formatName(m);
                securityManagerField.value = sm ? sm + ' — ' + formatName(sm) : 'Not assigned';
                reviewerHidden.value        = r;
                coReviewerHidden.value      = cr;
                managerHidden.value         = m;
                securityManagerHidden.value = sm;
                if (overtimeManagerDisplay) {
                    overtimeManagerDisplay.value = om ? om + ' — ' + formatName(om) : 'Not configured';
                    overtimeManagerHidden.value  = om;
                }
            }
        })();
        calculateNights();
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
</script>

<style>
.card {
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.form-label {
    font-weight: 500;
    font-size: 14px;
}
.text-danger {
    color: #dc3545 !important;
}
.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}
</style>