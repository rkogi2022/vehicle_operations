<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Trip Request</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #007bff;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Create Trip Request</h3>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= URL; ?>trip/myrequests">My Trips</a></li>
        <li class="breadcrumb-item ">Create Request</li>
    </ol>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-car me-1"></i>
            Trip Request Form
        </div>
        <div class="card-body">
            <form method="POST" action="<?= URL ?>trip/store" id="tripForm">
                <!-- Staff Information Section -->
                <div class="section-title">Staff Information</div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Staff Name</label>
                        <?php 
                        // Extract name from email (everything before @)
                        $staffName = explode('@', $user->email)[0];
                        $staffName = ucfirst(str_replace(['.', '_', '-'], ' ', $staffName));
                        ?>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($staffName) ?>" disabled>
                        <input type="hidden" name="requester_id" value="<?= $user->id ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user->email) ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user->department_name ?? 'Not Assigned') ?>" disabled>
                        <input type="hidden" name="department_id" value="<?= $user->department_id ?>">
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label required">Approved Supervisor</label>
                        <select name="approved_supervisor_id" class="form-select" required>
                            <option value="">Select Supervisor</option>
                            <?php if (!empty($supervisors)): ?>
                                <?php foreach ($supervisors as $supervisor): ?>
                                    <option value="<?= $supervisor->id ?>"><?= htmlspecialchars(ucwords(str_replace(['.','_','-'],' ', explode('@',$supervisor->email)[0]))) ?> &mdash; <?= htmlspecialchars($supervisor->email) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No supervisors available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Trip Details Section -->
                <div class="section-title">Trip Details</div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label required">Trip Type</label>
                        <select name="trip_type" id="trip_type" class="form-select" required>
                            <option value="local">Local</option>
                            <option value="international">International</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label required">Trip Destination</label>
                        <input type="text" name="trip_destination" class="form-control" required placeholder="e.g., Lagos Office, Abuja, etc.">
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label required">Purpose of Trip</label>
                        <textarea name="purpose" class="form-control" rows="3" required placeholder="Explain the reason for this trip..."></textarea>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label required">Departure Date</label>
                        <input type="date" name="departure_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Departure Time</label>
                        <input type="time" name="departure_time" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vehicle Departure State</label>
                        <select name="vehicle_departure_location" id="departure_state" class="form-select">
                            <option value="">Select Departure State</option>
                            <?php if (!empty($user_states)): ?>
                                <?php foreach ($user_states as $state): ?>
                                    <option value="<?= $state->id ?>"><?= htmlspecialchars($state->name) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No states available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vehicle Destination State</label>
                        <select name="vehicle_destination_location" id="destination_state" class="form-select">
                            <option value="">Select Destination State</option>
                            <?php if (!empty($user_states)): ?>
                                <?php foreach ($user_states as $state): ?>
                                    <option value="<?= $state->id ?>"><?= htmlspecialchars($state->name) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No states available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Return Date</label>
                        <input type="date" name="return_date" class="form-control">
                        <small class="text-muted">Leave empty if this is a one-way trip</small>
                    </div>
                </div>
                
                <!-- Driver Options -->
                <div class="section-title">Driver Options</div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" name="need_driver" value="1" id="need_driver" class="form-check-input">
                            <label class="form-check-label" for="need_driver">
                                Need Driver for Pickup
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" name="driver_overtime" value="1" id="driver_overtime" class="form-check-input">
                            <label class="form-check-label" for="driver_overtime">
                                Driver Overtime Required
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Approval Flow Section -->
                <div class="section-title">Approval Flow</div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label required">Request Reviewer</label>
                        <select name="reviewer_id" class="form-select" required>
                            <option value="">Select Reviewer</option>
                            <?php if (!empty($reviewers)): ?>
                                <?php foreach ($reviewers as $reviewer): ?>
                                    <option value="<?= $reviewer->id ?>"><?= htmlspecialchars($reviewer->email) ?> (<?= htmlspecialchars($reviewer->role) ?>)</option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No reviewers available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trip Co-Reviewer</label>
                        <select name="co_reviewer_id" class="form-select">
                            <option value="">Select Co-Reviewer (Optional)</option>
                            <?php if (!empty($co_reviewers)): ?>
                                <?php foreach ($co_reviewers as $co_reviewer): ?>
                                    <option value="<?= $co_reviewer->id ?>"><?= htmlspecialchars($co_reviewer->email) ?> (<?= htmlspecialchars($co_reviewer->role) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trip Manager</label>
                        <select name="manager_id" class="form-select">
                            <option value="">Select Manager (Optional)</option>
                            <?php if (!empty($managers)): ?>
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?= $manager->id ?>"><?= htmlspecialchars($manager->email) ?> (<?= htmlspecialchars($manager->role) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Submit Trip Request</button>
                    <a href="<?= URL ?>trip/myrequests" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<script>
// Update destination states based on trip type
document.getElementById('trip_type').addEventListener('change', function() {
    const tripType = this.value;
    if (tripType === 'international') {
        alert('For international trips, you will need to specify the country and state in the destination field.');
    }
});

// Validate departure date is not in the past
document.querySelector('input[name="departure_date"]').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        alert('Departure date cannot be in the past!');
        this.value = '';
    }
});

// Ensure return date is after departure date
document.querySelector('input[name="return_date"]').addEventListener('change', function() {
    const departureDate = document.querySelector('input[name="departure_date"]').value;
    const returnDate = this.value;
    
    if (departureDate && returnDate && new Date(returnDate) < new Date(departureDate)) {
        alert('Return date cannot be before departure date!');
        this.value = '';
    }
});
</script>

</body>
</html>