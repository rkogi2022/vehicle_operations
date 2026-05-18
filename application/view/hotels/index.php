<div class="container-fluid px-4">

    <h3 class="mt-4">Hotel Management</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Hotel Management</li>
    </ol>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- ================= HOTEL TABLE ================= -->
    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between">
            <span><i class="fas fa-hotel me-1"></i> Hotels List</span>

            <button class="btn btn-primary btn-sm" onclick="openHotelModal('add')">
                <i class="fas fa-plus"></i> Add Hotel
            </button>
        </div>

        <div class="card-body table-responsive">

            <table id="hotelsTable" class="table table-striped">

                <thead>
                    <tr>
                        <th>Hotel Name</th>
                        <th>Location</th>
                        <th>State</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!empty($hotels)): ?>

                    <?php foreach ($hotels as $hotel): ?>

                        <tr>

                            <td>
                                <strong><?= htmlspecialchars($hotel->name); ?></strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($hotel->location ?? ''); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($hotel->state_name); ?>
                                (<?= htmlspecialchars($hotel->state_code); ?>)
                            </td>

                            <td>

                                <!-- ✅ FIXED EDIT BUTTON -->
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick='openHotelModal(
                                        "edit",
                                        <?= (int)$hotel->id ?>,
                                        <?= json_encode($hotel->name) ?>,
                                        <?= json_encode($hotel->location ?? "") ?>,
                                        <?= (int)$hotel->state_id ?>
                                    )'>

                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDelete(
                                        <?= (int)$hotel->id ?>,
                                        '<?= htmlspecialchars($hotel->name, ENT_QUOTES) ?>'
                                    )">

                                    <i class="fas fa-trash"></i> Delete
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="text-center">
                            No hotels found
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- ================= HOTEL MODAL ================= -->
<div class="modal fade" id="hotelModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form id="hotel-form" method="POST">

                <div class="modal-header">
                    <h5 id="hotelModalLabel">Add Hotel</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="hotel_id" name="id">

                    <div class="mb-3">
                        <label>Hotel Name</label>
                        <input type="text" id="hotel_name" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Location</label>
                        <input type="text" id="hotel_location" name="location" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>State</label>
                        <select id="state_id" name="state_id" class="form-control" required>
                            <option value="">Select State</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= $state->id ?>">
                                    <?= htmlspecialchars($state->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </form>

        </div>

    </div>

</div>


<!-- ================= DELETE MODAL ================= -->
<div class="modal fade" id="deleteModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">
                <h5>Confirm Delete</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteItemName"></strong>?
            </div>

            <div class="modal-footer">
                <a id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>

        </div>

    </div>

</div>


<!-- ================= JS ================= -->
<script>

let hotelModal = new bootstrap.Modal(document.getElementById('hotelModal'));
let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

function openHotelModal(action, id = '', name = '', location = '', state_id = '') {

    document.getElementById('hotel_id').value = id;
    document.getElementById('hotel_name').value = name;
    document.getElementById('hotel_location').value = location;
    document.getElementById('state_id').value = state_id;

    document.getElementById('hotelModalLabel').innerText =
        action === 'edit' ? 'Edit Hotel' : 'Add Hotel';

    document.getElementById('hotel-form').action = action === 'edit'
        ? '<?= URL ?>hotel/editHotel/' + id
        : '<?= URL ?>hotel/createHotel';

    hotelModal.show();
}

function confirmDelete(id, name) {

    document.getElementById('deleteItemName').innerText = name;
    document.getElementById('confirmDeleteBtn').href =
        '<?= URL; ?>hotel/deleteHotel/' + id;

    deleteModal.show();
}

</script>