<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interstate Operations Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .stats-card { transition: transform 0.2s, box-shadow 0.2s; border: none; border-radius: 16px; overflow: hidden; }
        .stats-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .card-header-custom { background: white; border-bottom: 2px solid #eef2f6; padding: 16px 20px; font-weight: 700; font-size: 1rem; }
        .trip-card { background: white; border-radius: 16px; border: none; box-shadow: 0 4px 16px rgba(0,0,0,0.06); margin-bottom: 24px; overflow: hidden; }
        .trip-card-header { background: linear-gradient(135deg, #1a7f4b, #25a865); color: white; padding: 14px 20px; }
        .trip-card-body { padding: 20px; }
        .section-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #7c8b9c; margin-bottom: 6px; }
        .section-value { font-size: 0.9rem; font-weight: 500; color: #1a2c3e; }
        .requester-suggestion { background: #f0f7ff; border-left: 3px solid #0d6efd; border-radius: 6px; padding: 6px 10px; font-size: 0.82rem; color: #0d6efd; margin-bottom: 6px; }
        .form-select-sm, .form-control-sm { border-radius: 8px; font-size: 0.83rem; }
        .info-box { background: #e7f3ff; border-left: 4px solid #0d6efd; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; }
        .empty-state { text-align: center; padding: 60px 20px; color: #6c757d; }
        .empty-state i { font-size: 4rem; margin-bottom: 15px; opacity: 0.5; }
        .divider { height: 1px; background: #eef2f6; margin: 14px 0; }
        .badge-mode { padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h3 class="mt-2 fw-bold" style="color: #1a2c3e;">
            <i class="fas fa-route me-2" style="color:#1a7f4b;"></i> Interstate Operations Dashboard
        </h3>
    </div>

    <ol class="breadcrumb mb-4 bg-transparent p-0">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Interstate Driver Assignment</li>
    </ol>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php
    $pendingDriverRequests = isset($pendingDriverRequests) && is_array($pendingDriverRequests) ? $pendingDriverRequests : [];
    $driversByState        = isset($driversByState)        && is_array($driversByState)        ? $driversByState        : [];
    $allDrivers            = isset($allDrivers)            && is_array($allDrivers)            ? $allDrivers            : [];
    $airlines              = isset($airlines)             && is_array($airlines)             ? $airlines             : [];
    $hotels                = isset($hotels)               && is_array($hotels)               ? $hotels               : [];
    $awaitingCount         = count($pendingDriverRequests);
    $inProgressCount       = $stats['in_progress'] ?? 0;
    ?>

    <div class="info-box">
        <i class="fas fa-info-circle me-2 text-primary"></i>
        <strong>Interstate Assignment:</strong> You have <strong><?= $awaitingCount ?></strong> interstate trip(s) awaiting confirmation.
        Review each trip's suggested flights and hotel, make changes if needed, then assign a driver.
    </div>

    <!-- Stats -->
    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="card stats-card bg-warning bg-opacity-10 border-0 border-bottom border-3 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-1">Awaiting Assignment</h6>
                            <h2 class="mb-0 fw-bold"><?= $awaitingCount ?></h2>
                            <small class="text-muted">Flights/hotel/driver pending</small>
                        </div>
                        <i class="fas fa-hourglass-half fa-3x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card bg-primary bg-opacity-10 border-0 border-bottom border-3 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary mb-1">In Progress</h6>
                            <h2 class="mb-0 fw-bold"><?= $inProgressCount ?></h2>
                            <small class="text-muted">Driver assigned</small>
                        </div>
                        <i class="fas fa-truck fa-3x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card bg-success bg-opacity-10 border-0 border-bottom border-3 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-1">Available Drivers</h6>
                            <h2 class="mb-0 fw-bold"><?= count($allDrivers) ?></h2>
                            <small class="text-muted">Ready to assign</small>
                        </div>
                        <i class="fas fa-users fa-3x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trip Cards -->
    <?php if ($awaitingCount > 0): ?>

        <?php foreach ($pendingDriverRequests as $trip): ?>
        <?php
            $tripId        = $trip->id;
            $staffName     = $trip->staff_name ?? explode('@', $trip->staff_email)[0];
            $staffPhone    = $trip->staff_phone ?? '';
            $fromState     = $trip->vehicle_location_state_name ?? '—';
            $toState       = $trip->arrival_state_name ?? '—';
            $destCity      = $trip->destination_city ?? '';
            $destAddr      = $trip->trip_destination ?? '';
            $tripDate      = $trip->trip_date;
            $returnDate    = $trip->return_date;
            $totalNights   = $trip->total_nights;
            $modeOfTravel  = $trip->mode_of_travel ?? 'road';
            $purpose       = $trip->purpose ?? '';
            $pickupLoc     = $trip->pickup_location ?? '—';
            $pickupTime    = $trip->pickup_time ?? null;
            $needPickup    = $trip->need_driver_pickup ?? 'no';
            $requireHotel  = $trip->require_hotel ?? 'no';
            $requireAirportPickup = $trip->require_airport_pickup ?? 'no';
            $airportDropoff = $trip->airport_pickup_dropoff_destination ?? '';
            $reqDepAirline = $trip->requester_departure_airline ?? null;
            $reqRetAirline = $trip->requester_return_airline ?? null;
            $hotelName     = $trip->hotel_name_from_vendor ?? $trip->hotel_other_name ?? null;
            $hotelLocation = $trip->hotel_location ?? '';
            $hotelId       = $trip->hotel_id ?? null;
            $hotelOther    = $trip->hotel_other_name ?? '';
            $hotelLoc      = $trip->hotel_location ?? '';
            $isAir            = in_array($modeOfTravel, ['air', 'both']);
            $departureDrivers = $driversByState[$trip->vehicle_location_state_id ?? 0] ?? $allDrivers;
            $returnDrivers    = $driversByState[$trip->arrival_location_state_id  ?? 0] ?? $allDrivers;
        ?>

        <div class="trip-card">
            <div class="trip-card-header d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1 fw-bold"><i class="fas fa-route me-2"></i>Trip #<?= $tripId ?> — <?= htmlspecialchars($staffName) ?></h6>
                    <small><?= htmlspecialchars($fromState) ?> <i class="fas fa-arrow-right mx-1"></i> <?= htmlspecialchars($toState) ?>, <?= htmlspecialchars($destCity) ?></small>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark badge-mode">
                        <i class="fas <?= $isAir ? 'fa-plane' : 'fa-car' ?> me-1"></i>
                        <?= ucfirst($modeOfTravel) ?>
                    </span>
                    <div class="mt-1 small"><?= date('M d, Y', strtotime($tripDate)) ?> → <?= date('M d, Y', strtotime($returnDate)) ?> (<?= $totalNights ?>n)</div>
                </div>
            </div>

            <form method="POST" action="<?= URL ?>interstate/assignDriver/<?= $tripId ?>" onsubmit="return confirmInterstateAssignment(event, this)">
            <div class="trip-card-body">
                <div class="row g-3">

                    <!-- LEFT: Trip info summary -->
                    <div class="col-md-4">
                        <div class="section-label"><i class="fas fa-user me-1"></i>Requester</div>
                        <div class="section-value mb-1"><?= htmlspecialchars($trip->staff_email) ?></div>
                        <div class="section-value text-muted small"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($staffPhone) ?></div>

                        <div class="divider"></div>

                        <div class="section-label"><i class="fas fa-map-marker-alt me-1"></i>Pickup Location</div>
                        <div class="section-value mb-1"><?= htmlspecialchars($pickupLoc) ?></div>
                        <?php if ($needPickup === 'yes' && $pickupTime): ?>
                            <div class="section-value text-primary small"><i class="fas fa-clock me-1"></i>Pickup time: <?= $pickupTime ?></div>
                        <?php endif; ?>

                        <?php if ($requireAirportPickup === 'yes'): ?>
                        <div class="divider"></div>
                        <div class="section-label"><i class="fas fa-shuttle-van me-1"></i>Airport Pickup Required</div>
                        <div class="section-value">Drop-off: <?= htmlspecialchars($airportDropoff) ?></div>
                        <?php endif; ?>

                        <div class="divider"></div>
                        <div class="section-label"><i class="fas fa-bullhorn me-1"></i>Purpose</div>
                        <div class="section-value small"><?= nl2br(htmlspecialchars(substr($purpose, 0, 120))) ?><?= strlen($purpose) > 120 ? '…' : '' ?></div>
                    </div>

                    <!-- MIDDLE: Flights & Hotel -->
                    <div class="col-md-4">

                        <?php if ($isAir): ?>
                        <div class="section-label"><i class="fas fa-plane-departure me-1"></i>Departure Flight</div>
                        <?php if ($reqDepAirline): ?>
                            <div class="requester-suggestion"><i class="fas fa-user me-1"></i>Requester suggested: <strong><?= htmlspecialchars($reqDepAirline) ?></strong></div>
                        <?php else: ?>
                            <div class="requester-suggestion text-muted"><i class="fas fa-minus-circle me-1"></i>No preference given</div>
                        <?php endif; ?>
                        <select name="operations_departure_flight_airline_id" class="form-select form-select-sm mb-3">
                            <option value="">— Confirm / Override Airline —</option>
                            <?php foreach ($airlines as $airline): ?>
                                <option value="<?= $airline->id ?>"
                                    <?= ($trip->requester_departure_flight_airline_id == $airline->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($airline->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="section-label"><i class="fas fa-plane-arrival me-1"></i>Return Flight</div>
                        <?php if ($reqRetAirline): ?>
                            <div class="requester-suggestion"><i class="fas fa-user me-1"></i>Requester suggested: <strong><?= htmlspecialchars($reqRetAirline) ?></strong></div>
                        <?php else: ?>
                            <div class="requester-suggestion text-muted"><i class="fas fa-minus-circle me-1"></i>No preference given</div>
                        <?php endif; ?>
                        <select name="operations_return_flight_airline_id" class="form-select form-select-sm mb-3">
                            <option value="">— Confirm / Override Airline —</option>
                            <?php foreach ($airlines as $airline): ?>
                                <option value="<?= $airline->id ?>"
                                    <?= ($trip->requester_return_flight_airline_id == $airline->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($airline->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                            <div class="text-muted small mb-3"><i class="fas fa-car me-1"></i>Road travel — no flight required.</div>
                            <input type="hidden" name="operations_departure_flight_airline_id" value="">
                            <input type="hidden" name="operations_return_flight_airline_id" value="">
                        <?php endif; ?>

                        <?php if ($requireHotel === 'yes'): ?>
                        <div class="section-label"><i class="fas fa-hotel me-1"></i>Hotel Accommodation</div>
                        <?php if ($hotelName): ?>
                            <div class="requester-suggestion"><i class="fas fa-user me-1"></i>Requester suggested: <strong><?= htmlspecialchars($hotelName) ?></strong>
                                <?= $hotelLocation ? '<br><small>' . htmlspecialchars($hotelLocation) . '</small>' : '' ?></div>
                        <?php else: ?>
                            <div class="requester-suggestion text-muted"><i class="fas fa-minus-circle me-1"></i>No hotel selected by requester</div>
                        <?php endif; ?>
                        <select name="hotel_id" id="hotel_select_<?= $tripId ?>" class="form-select form-select-sm mb-2" onchange="handleHotelChange(this, <?= $tripId ?>)">
                            <option value="">— Select Hotel from Vendors —</option>
                            <?php foreach ($hotels as $hotel): ?>
                                <option value="<?= $hotel->id ?>"
                                    data-location="<?= htmlspecialchars($hotel->location ?? '') ?>"
                                    <?= ($hotelId == $hotel->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($hotel->name) ?> (<?= htmlspecialchars($hotel->state_name ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                            <option value="other" <?= ($hotelOther && !$hotelId) ? 'selected' : '' ?>>Other (manual entry)</option>
                        </select>
                        <div id="hotel_other_section_<?= $tripId ?>"
                             style="display:<?= ($hotelOther && !$hotelId) ? 'block' : 'none' ?>;" class="mb-2">
                            <input type="text" name="hotel_other_name" class="form-control form-control-sm mb-1"
                                placeholder="Hotel name" value="<?= htmlspecialchars($hotelOther) ?>">
                            <input type="text" name="hotel_location" class="form-control form-control-sm"
                                placeholder="Hotel location / address" value="<?= htmlspecialchars($hotelLoc) ?>">
                        </div>
                        <?php else: ?>
                            <div class="text-muted small mb-3"><i class="fas fa-moon me-1"></i>No hotel required for this trip.</div>
                            <input type="hidden" name="hotel_id" value="">
                            <input type="hidden" name="hotel_other_name" value="">
                            <input type="hidden" name="hotel_location" value="">
                        <?php endif; ?>

                    </div>

                    <!-- RIGHT: Driver Assignment -->
                    <div class="col-md-4">
                        <div class="section-label"><i class="fas fa-user-check me-1"></i>Departure Driver <span class="text-danger">*</span></div>
                        <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt me-1"></i>Drivers at departure: <?= htmlspecialchars($fromState) ?></small>
                        <select name="driver_id" class="form-select form-select-sm mb-3" required>
                            <option value="">— Select Driver —</option>
                            <?php foreach ($departureDrivers as $driver):
                                $driverId   = is_object($driver) ? $driver->id    : $driver['id'];
                                $driverName = is_object($driver) ? ($driver->name ?? $driver->driver_name ?? '') : ($driver['name'] ?? $driver['driver_name'] ?? '');
                                $driverPhone = is_object($driver) ? ($driver->phone ?? '') : ($driver['phone'] ?? '');
                            ?>
                                <option value="<?= $driverId ?>">
                                    <?= htmlspecialchars($driverName) ?> — <?= htmlspecialchars($driverPhone) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="diff_return_<?= $tripId ?>"
                                name="different_return_driver" value="yes"
                                onchange="toggleReturnDriver(this, <?= $tripId ?>)">
                            <label class="form-check-label small" for="diff_return_<?= $tripId ?>">Different return driver?</label>
                        </div>

                        <div id="return_driver_section_<?= $tripId ?>" style="display:none;">
                            <div class="section-label"><i class="fas fa-user-clock me-1"></i>Return Driver</div>
                            <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt me-1"></i>Drivers at destination: <?= htmlspecialchars($toState) ?></small>
                            <select name="return_driver_id" class="form-select form-select-sm mb-3">
                                <option value="">— Select Return Driver —</option>
                                <?php foreach ($returnDrivers as $driver):
                                    $driverId    = is_object($driver) ? $driver->id    : $driver['id'];
                                    $driverName  = is_object($driver) ? ($driver->name ?? $driver->driver_name ?? '') : ($driver['name'] ?? $driver['driver_name'] ?? '');
                                    $driverPhone = is_object($driver) ? ($driver->phone ?? '') : ($driver['phone'] ?? '');
                                ?>
                                    <option value="<?= $driverId ?>">
                                        <?= htmlspecialchars($driverName) ?> — <?= htmlspecialchars($driverPhone) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="divider"></div>
                        <button type="submit" class="btn btn-success w-100 rounded-3">
                            <i class="fas fa-check-circle me-2"></i> Confirm & Assign Driver
                        </button>
                    </div>

                </div>
            </div>
            </form>
        </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-check-circle text-success"></i>
                    <h5>No Interstate Trips Awaiting Assignment</h5>
                    <p class="text-muted">All approved interstate trips have been assigned.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- In-Progress Trips -->
    <?php
    $inProgressRequests = isset($inProgressRequests) && is_array($inProgressRequests) ? $inProgressRequests : [];
    if (!empty($inProgressRequests)):
    ?>
    <h5 class="fw-bold mb-3 mt-2" style="color:#1a2c3e;">
        <i class="fas fa-truck me-2 text-primary"></i> In Progress — Driver Assigned
    </h5>
    <?php foreach ($inProgressRequests as $trip):
        $tripPassed   = strtotime($trip->trip_date) < strtotime('today');
        $canEdit          = !$tripPassed;
        $editTripId       = $trip->id;
        $departureDrivers = $driversByState[$trip->vehicle_location_state_id ?? 0] ?? $allDrivers;
        $returnDrivers    = $driversByState[$trip->arrival_location_state_id  ?? 0] ?? $allDrivers;
        $toState          = $trip->arrival_state_name ?? '—';
    ?>
    <div class="card shadow-sm border-0 rounded-4 mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f8f9fc;">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Requester</th>
                            <th>Route</th>
                            <th>Trip Date</th>
                            <th>Driver</th>
                            <th>Return Driver</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3 fw-bold">#<?= $editTripId ?></td>
                            <td>
                                <div><?= htmlspecialchars($trip->staff_name ?? explode('@', $trip->staff_email)[0]) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($trip->staff_phone ?? '') ?></small>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($trip->vehicle_location_state_name ?? '') ?> → <?= htmlspecialchars($toState) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($trip->destination_city ?? '') ?></small>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($trip->trip_date)) ?>
                                <br><small class="text-muted">Return: <?= date('M d', strtotime($trip->return_date)) ?></small>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($trip->driver_name ?? $trip->driver_email ?? '—') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($trip->driver_phone ?? '') ?></small>
                            </td>
                            <td>
                                <?php if ($trip->different_return_driver === 'yes' && ($trip->return_driver_email ?? null)): ?>
                                    <div><?= htmlspecialchars($trip->return_driver_name ?? $trip->return_driver_email) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($trip->return_driver_phone ?? '') ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Same driver</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= URL ?>interstate/view/<?= $editTripId ?>" class="btn btn-sm btn-outline-secondary me-1" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($canEdit): ?>
                                <button class="btn btn-sm btn-warning me-1" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#editForm_<?= $editTripId ?>"
                                    title="Edit trip details">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-success"
                                    onclick="confirmComplete(<?= $editTripId ?>, '<?= htmlspecialchars(addslashes($trip->trip_destination ?? '')) ?>')">
                                    <i class="fas fa-flag-checkered me-1"></i> Complete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php if ($canEdit): ?>
            <div class="collapse" id="editForm_<?= $editTripId ?>">
                <div class="p-3 border-top" style="background:#fffdf5;">
                    <h6 class="fw-bold text-warning mb-3"><i class="fas fa-edit me-1"></i> Update Trip Details — Trip #<?= $editTripId ?></h6>
                    <form method="POST" action="<?= URL ?>interstate/assignDriver/<?= $editTripId ?>"
                          onsubmit="return confirmUpdateAssignment(event, this)">

                        <div class="row g-3">
                            <!-- Driver -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Departure Driver <span class="text-danger">*</span></label>
                                <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt me-1"></i>Drivers at departure: <?= htmlspecialchars($trip->vehicle_location_state_name ?? '—') ?></small>
                                <select name="driver_id" class="form-select form-select-sm" required>
                                    <option value="">— Select Driver —</option>
                                    <?php foreach ($departureDrivers as $d):
                                        $dId    = is_object($d) ? $d->id    : $d['id'];
                                        $dName  = is_object($d) ? ($d->name ?? '') : ($d['name'] ?? '');
                                        $dPhone = is_object($d) ? ($d->phone ?? '') : ($d['phone'] ?? '');
                                        $sel    = ($trip->assigned_driver_id == $dId) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $dId ?>" <?= $sel ?>><?= htmlspecialchars($dName) ?> — <?= htmlspecialchars($dPhone) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <div class="form-check form-switch mt-2 mb-1">
                                    <input class="form-check-input" type="checkbox" id="editDiffReturn_<?= $editTripId ?>"
                                        name="different_return_driver" value="yes"
                                        <?= ($trip->different_return_driver === 'yes') ? 'checked' : '' ?>
                                        onchange="toggleReturnDriver(this, 'edit_<?= $editTripId ?>')">
                                    <label class="form-check-label small" for="editDiffReturn_<?= $editTripId ?>">Different return driver?</label>
                                </div>
                                <div id="return_driver_section_edit_<?= $editTripId ?>"
                                     style="display:<?= ($trip->different_return_driver === 'yes') ? 'block' : 'none' ?>;">
                                    <label class="form-label fw-semibold small mt-1">Return Driver</label>
                                    <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt me-1"></i>Drivers at destination: <?= htmlspecialchars($toState) ?></small>
                                    <select name="return_driver_id" class="form-select form-select-sm">
                                        <option value="">— Select Return Driver —</option>
                                        <?php foreach ($returnDrivers as $d):
                                            $dId    = is_object($d) ? $d->id    : $d['id'];
                                            $dName  = is_object($d) ? ($d->name ?? '') : ($d['name'] ?? '');
                                            $dPhone = is_object($d) ? ($d->phone ?? '') : ($d['phone'] ?? '');
                                            $sel    = ($trip->return_assigned_driver_id == $dId) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $dId ?>" <?= $sel ?>><?= htmlspecialchars($dName) ?> — <?= htmlspecialchars($dPhone) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Flights (only for air/both) -->
                            <?php $isAirEdit = in_array(strtolower($trip->mode_of_travel ?? 'road'), ['air', 'both']); ?>
                            <?php if ($isAirEdit): ?>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Confirmed Departure Airline</label>
                                <select name="operations_departure_flight_airline_id" class="form-select form-select-sm mb-2">
                                    <option value="">— Select Airline —</option>
                                    <?php foreach ($airlines as $al): $sel = ($trip->operations_departure_flight_airline_id == $al->id) ? 'selected' : ''; ?>
                                    <option value="<?= $al->id ?>" <?= $sel ?>><?= htmlspecialchars($al->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label class="form-label fw-semibold small">Confirmed Return Airline</label>
                                <select name="operations_return_flight_airline_id" class="form-select form-select-sm">
                                    <option value="">— Select Airline —</option>
                                    <?php foreach ($airlines as $al): $sel = ($trip->operations_return_flight_airline_id == $al->id) ? 'selected' : ''; ?>
                                    <option value="<?= $al->id ?>" <?= $sel ?>><?= htmlspecialchars($al->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="operations_departure_flight_airline_id" value="">
                            <input type="hidden" name="operations_return_flight_airline_id" value="">
                            <?php endif; ?>

                            <!-- Hotel -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Hotel (Confirmed)</label>
                                <?php
                                $currentHotelId   = $trip->hotel_id ?? null;
                                $currentHotelOther= $trip->hotel_other_name ?? '';
                                $currentHotelLoc  = $trip->hotel_location ?? '';
                                $editHotelVal     = $currentHotelId ? $currentHotelId : ($currentHotelOther ? 'other' : '');
                                $reqHotelHint     = $trip->hotel_name_from_vendor ?? $trip->hotel_other_name ?? null;
                                ?>
                                <?php if ($reqHotelHint): ?>
                                <div class="small text-muted mb-1">
                                    <i class="fas fa-user me-1"></i>Requester asked for: <strong><?= htmlspecialchars($reqHotelHint) ?></strong>
                                </div>
                                <?php endif; ?>
                                <select name="hotel_id" class="form-select form-select-sm mb-2"
                                        onchange="handleHotelChange(this, 'edit_<?= $editTripId ?>')">
                                    <option value="">— No hotel —</option>
                                    <?php foreach ($hotels as $h): $sel = ($currentHotelId == $h->id) ? 'selected' : ''; ?>
                                    <option value="<?= $h->id ?>" <?= $sel ?>><?= htmlspecialchars($h->name) ?> (<?= htmlspecialchars($h->state_name ?? '') ?>)</option>
                                    <?php endforeach; ?>
                                    <option value="other" <?= ($editHotelVal === 'other') ? 'selected' : '' ?>>Other / Not listed</option>
                                </select>
                                <div id="hotel_other_section_edit_<?= $editTripId ?>"
                                     style="display:<?= ($editHotelVal === 'other') ? 'block' : 'none' ?>;">
                                    <input type="text" name="hotel_other_name" class="form-control form-control-sm mb-1"
                                           placeholder="Hotel name" value="<?= htmlspecialchars($currentHotelOther) ?>">
                                    <input type="text" name="hotel_location" class="form-control form-control-sm"
                                           placeholder="Hotel location" value="<?= htmlspecialchars($currentHotelLoc) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-sm fw-bold">
                                <i class="fas fa-save me-1"></i> Save Changes & Notify Requester
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-toggle="collapse" data-bs-target="#editForm_<?= $editTripId ?>">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleReturnDriver(checkbox, tripId) {
    const section = document.getElementById('return_driver_section_' + tripId);
    if (section) section.style.display = checkbox.checked ? 'block' : 'none';
}

function handleHotelChange(select, tripId) {
    const otherSection = document.getElementById('hotel_other_section_' + tripId);
    if (otherSection) otherSection.style.display = select.value === 'other' ? 'block' : 'none';
}

function confirmUpdateAssignment(event, form) {
    event.preventDefault();
    const driverSelect = form.querySelector('select[name="driver_id"]');
    if (!driverSelect.value) {
        Swal.fire({ title: 'No Driver Selected', text: 'Please select a departure driver.', icon: 'warning', confirmButtonText: 'OK' });
        return false;
    }
    Swal.fire({
        title: 'Save Changes?',
        html: 'The requester will receive an email notification about the updated trip details.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e67e22',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Update & Notify'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
    return false;
}

function confirmInterstateAssignment(event, form) {
    event.preventDefault();

    const driverSelect = form.querySelector('select[name="driver_id"]');
    if (!driverSelect.value) {
        Swal.fire({ title: 'No Driver Selected', text: 'Please select a departure driver.', icon: 'warning', confirmButtonText: 'OK' });
        return false;
    }

    const driverName = driverSelect.options[driverSelect.selectedIndex].text;

    Swal.fire({
        title: 'Confirm Assignment?',
        html: `Assign <strong>${driverName}</strong> to this trip?<br><small>The requester will be notified by email with the confirmed details.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Confirm & Assign'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
    return false;
}

// Pre-show "other hotel" section if hotel_other_name is already set
document.querySelectorAll('[id^="hotel_select_"]').forEach(sel => {
    if (!sel.value && sel.closest('form').querySelector('[name="hotel_other_name"]')?.value) {
        sel.value = 'other';
        const tripId = sel.id.replace('hotel_select_', '');
        document.getElementById('hotel_other_section_' + tripId).style.display = 'block';
    }
});

function confirmComplete(tripId, destination) {
    Swal.fire({
        title: 'Mark as Completed?',
        html: `Confirm trip <strong>#${tripId}</strong> to <strong>${destination}</strong> is complete?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Mark Complete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= URL ?>interstate/complete/' + tripId;
        }
    });
}
</script>
</body>
</html>
