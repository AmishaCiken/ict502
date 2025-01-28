<?php
session_start();
include('./conn/conn.php');

// Fetch all bookings from the database
$query = "SELECT FarmBooking.FarmBookingID, 
                 Farm.FarmName, 
                 FarmBooking.RequestDate, 
                 FarmBooking.Status, 
                 FarmBooking.BookingPrice, 
                 FarmBooking.FarmerID, 
                 FarmBooking.ApprovalDate
          FROM FarmBooking 
          JOIN Farm ON FarmBooking.FarmID = Farm.FarmID";

$stmt = oci_parse($conn, $query);
if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo "Error executing query: " . htmlspecialchars($e['message']);
    exit();
}

$bookings = [];
while (($row = oci_fetch_assoc($stmt)) != false) {
    $bookings[] = $row;
}

// Handle delete farm booking with confirmation
if (isset($_POST['delete_booking'])) {
    $FarmBookingID = $_POST['FarmBookingID'];

    $query = "DELETE FROM FarmBooking WHERE FarmBookingID = :FarmBookingID";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':FarmBookingID', $FarmBookingID);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Booking deleted successfully.";
        header("Location: admin_bookingFarm.php");
        exit();
    } else {
        $e = oci_error($stmt);
        $_SESSION['error_message'] = "Error deleting booking: " . htmlspecialchars($e['message']);
        header("Location: admin_bookingFarm.php");
        exit();
    }
}

// Handle CRUD operations (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_booking'])) {
        // Approve farm booking
        $approvalDate = $_POST['approval_date'];
        $bookingId = $_POST['booking_id'];

        // Validate date format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $approvalDate)) {
            $updateQuery = "UPDATE FarmBooking 
                            SET Status = 'Approved', 
                                ApprovalDate = TO_DATE(:approvalDate, 'YYYY-MM-DD') 
                            WHERE FarmBookingID = :bookingId";
            $stid = oci_parse($conn, $updateQuery);
            oci_bind_by_name($stid, ':approvalDate', $approvalDate);
            oci_bind_by_name($stid, ':bookingId', $bookingId);

            if (oci_execute($stid)) {
                oci_commit($conn);
                $_SESSION['success_message'] = "Booking approved successfully.";
            } else {
                $e = oci_error($stid);
                $_SESSION['error_message'] = "Error approving booking: " . htmlspecialchars($e['message']);
            }
        } else {
            $_SESSION['error_message'] = "Invalid date format. Please use YYYY-MM-DD.";
        }
        header("Location: admin_bookingFarm.php");
        exit();
    }

    if (isset($_POST['reject_booking'])) {
        // Reject farm booking
        $bookingId = $_POST['booking_id'];
        $updateQuery = "UPDATE FarmBooking SET Status = 'Rejected' WHERE FarmBookingID = :bookingId";
        $stid = oci_parse($conn, $updateQuery);
        oci_bind_by_name($stid, ':bookingId', $bookingId);

        if (oci_execute($stid)) {
            oci_commit($conn);
            $_SESSION['success_message'] = "Booking rejected successfully.";
        } else {
            $e = oci_error($stid);
            $_SESSION['error_message'] = "Error rejecting booking: " . htmlspecialchars($e['message']);
        }
        header("Location: admin_bookingFarm.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Farm Booking Management</title>
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style3.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" crossorigin="anonymous" />
</head>
<body class="bg-content">
    <main class="dashboard d-flex">
        <!-- Sidebar -->
        <?php include "admin_sidebar.php"; ?>
        <!-- Content Page -->
        <div class="container-fluid px">
            <?php include "header.php"; ?>
            <div class="container my-4">
                <h2 class="text-center">Admin Farm Booking Management</h2>

                <!-- Display success or error messages -->
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                    unset($_SESSION['error_message']);
                }
                ?>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Farm Name</th>
                            <th>Farmer ID</th>
                            <th>Booking Price</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Approval Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bookings)): ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['FARMNAME']) ?></td>
                                    <td><?= htmlspecialchars($booking['FARMERID']) ?></td>
                                    <td><?= htmlspecialchars($booking['BOOKINGPRICE']) ?></td>
                                    <td><?= htmlspecialchars($booking['REQUESTDATE']) ?></td>
                                    <td><?= htmlspecialchars($booking['STATUS']) ?></td>
                                    <td><?= $booking['STATUS'] == 'Approved' ? htmlspecialchars($booking['APPROVALDATE']) : 'N/A' ?></td>
                                    <td>
                                        <?php if ($booking['STATUS'] == 'Pending'): ?>
                                            <form action="admin_bookingFarm.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['FARMBOOKINGID']) ?>">
                                                <input type="date" name="approval_date" required>
                                                <button type="submit" name="approve_booking" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form action="admin_bookingFarm.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['FARMBOOKINGID']) ?>">
                                                <button type="submit" name="reject_booking" class="btn btn-warning btn-sm">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?= htmlspecialchars($booking['FARMBOOKINGID']) ?>)" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No farm bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

        </div>
    </main>

    <script src="script.js"></script>
    <script src="bootstrap.bundle.js"></script>
    <script>
        function confirmDelete(bookingId) {
            document.getElementById('booking_id').value = bookingId;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>
