<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
include('./conn/conn.php');

// Fetch the user's farm bookings from the database
$query = "SELECT FarmBooking.FarmBookingID, 
                 Farm.FarmName, 
                 FarmBooking.RequestDate, 
                 FarmBooking.Status, 
                 FarmBooking.BookingPrice 
          FROM FarmBooking 
          JOIN Farm ON FarmBooking.FarmID = Farm.FarmID";

$stmt = oci_parse($conn, $query);
if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo "Error executing query: " . $e['message'];
    exit();
}

$bookings = [];
while (($row = oci_fetch_assoc($stmt)) != false) {
    $bookings[] = $row;
}

// Handle add booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_booking'])) {
    // Collect form data
    $farm_id = $_POST['farm_id']; // Farm ID passed from hidden input field
    $booking_price = $_POST['booking_price'];
    $status = $_POST['status'];

    // Check if the farm ID is empty
    if (empty($farm_id)) {
        $_SESSION['error_message'] = "Farm ID cannot be empty. Please select a valid farm.";
        header("Location: admin_bookingFarm.php");
        exit();
    }

    // Ensure the user ID is set
    if (!isset($user_id)) {
        echo "User ID is not set.";
        exit();
    }

    // Fetch the next value for FarmBookingID
    $query = "SELECT FarmBookingSeq.NEXTVAL AS new_id FROM dual";
    $stmt = oci_parse($conn, $query);
    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        echo "Error executing sequence query: " . $e['message'];
        exit();
    }

    $row = oci_fetch_assoc($stmt);
    $new_booking_id = $row['NEW_ID'];

    // Insert the booking into the database
    $query = "INSERT INTO FarmBooking (FarmBookingID, FARMERID, FarmID, BookingPrice, RequestDate, Status)
              VALUES (:booking_id, :user_id, :farm_id, :booking_price, SYSDATE, :status)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':booking_id', $new_booking_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':farm_id', $farm_id);
    oci_bind_by_name($stmt, ':booking_price', $booking_price);
    oci_bind_by_name($stmt, ':status', $status);

    // Execute the statement and handle success or failure
    if (oci_execute($stmt)) {
        oci_commit($conn); // Commit the transaction
        $_SESSION['success_message'] = "Farm booking added successfully.";
        header("Location: admin_bookingFarm.php"); // Redirect to the booking management page
        exit(); // Make sure the script stops executing
    } else {
        $e = oci_error($stmt);
        $_SESSION['error_message'] = "Error adding farm booking: " . htmlspecialchars($e['message']);
        header("Location: admin_bookingFarm.php"); // Redirect back to the management page with error message
        exit();
    }
}

// Handle delete farm booking with confirmation
if (isset($_POST['delete_booking'])) {
    $FarmBookingID = $_POST['FarmBookingID'];

    $query = "DELETE FROM FarmBooking WHERE FarmBookingID = :FarmBookingID AND FARMERID = :user_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':FarmBookingID', $FarmBookingID);
    oci_bind_by_name($stmt, ':user_id', $user_id);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Booking deleted successfully.";
        header("Location: admin_bookingFarm.php");
        exit();
    } else {
        $e = oci_error($stmt);
        echo "Error deleting farm booking: " . htmlspecialchars($e['message']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Booking Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Admin Farm Booking Management</h2>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div id="successMessage" class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }

    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }

    if (isset($_SESSION['delete_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['delete_message'] . '</div>';
        unset($_SESSION['delete_message']);
    }
    ?>

    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addBookingModal">Add Farm Booking</button>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Farm Name</th>
            <th>Booking Price</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['FARMNAME']) ?></td>
                    <td><?= htmlspecialchars($booking['BOOKINGPRICE']) ?></td>
                    <td><?= htmlspecialchars($booking['REQUESTDATE']) ?></td>
                    <td><?= htmlspecialchars($booking['STATUS']) ?></td>
                    <td>
						<!-- Delete button with confirmation -->
						<a href="javascript:void(0);" onclick="confirmDelete(<?= htmlspecialchars($booking['FARMBOOKINGID']) ?>)" class="btn btn-danger btn-sm">Delete</a>
					</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No farm bookings found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Farm Booking Modal -->
<div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBookingModalLabel">Add Farm Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Farm Name Input -->
                    <div class="mb-3">
                        <label for="farm_name" class="form-label">Search Farm</label>
                        <input type="text" class="form-control" id="farm_name" name="farm_name" placeholder="Search Farm" required>
                        <input type="hidden" id="farm_id" name="farm_id" value=""> <!-- Hidden field for farm ID -->
                        <div id="farm_suggestions"></div> <!-- Suggestions list -->
                    </div>

                    <!-- Booking Price -->
                    <div class="mb-3">
                        <label for="booking_price" class="form-label">Booking Price</label>
                        <input type="number" class="form-control" id="booking_price" name="booking_price" required>
                    </div>

                    <!-- Booking Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Booking Status</label>
                        <input type="text" class="form-control" id="status" name="status" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_booking" class="btn btn-primary">Add Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Farm Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this booking?</p>
                    <input type="hidden" id="booking_id" name="FarmBookingID">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_booking" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Handle delete button click in modal
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const bookingId = button.getAttribute('data-booking-id');
        document.getElementById('booking_id').value = bookingId;
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
