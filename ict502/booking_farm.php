<?php
session_start();
include('./conn/conn.php');  // Ensure the database connection is included

// Get the FarmerID from the session (assuming the user is logged in)
$farmerID = $_SESSION['user_id'];  // Replace with your session variable

// Fetch all bookings for the logged-in farmer
$query = "
    SELECT fb.FarmBookingID, f.FarmName, f.FarmLocation, fb.RequestDate, fb.ApprovalDate, fb.Status, fb.BookingPrice
    FROM FarmBooking fb
    JOIN Farm f ON fb.FarmID = f.FarmID
    WHERE fb.FarmerID = :farmerID
    ORDER BY fb.RequestDate DESC"; // Order by most recent bookings

// Prepare and execute the query using OCI8
$stid = oci_parse($conn, $query);

// Bind the variable for the farmer ID
oci_bind_by_name($stid, ":farmerID", $farmerID);

// Execute the query
if (!oci_execute($stid)) {
    $error = oci_error($stid);
    echo "Error executing query: " . $error['message'];
    exit;
}

// Fetching the results
$myBookings = [];
while ($row = oci_fetch_assoc($stid)) {
    $myBookings[] = $row;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .content-box {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        th {
            background-color: #f8f9fa;
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
        }

        h4 {
            font-size: 18px;
            font-weight: 500;
        }

        .title-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .request-button {
            background-color: #343a40;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        .request-button:hover {
            background-color: #23272b;
        }
    </style>
</head>
<body>

    <div class="container my-4">
        <div class="content-box">
            <h2 class="text-center">My Bookings</h2>

            <div class="title-bar">
                <h4>Your Bookings</h4>
                <button class="btn request-button" onclick="window.location.href='available_farm.php'">Request New Farm</button>
            </div>

            <div class="table-wrapper">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Booking ID</th>
                            <th>Farm Name</th>
                            <th>Request Date</th>
                            <th>Approval Date</th>
                            <th>Status</th>
                            <th>Booking Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myBookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['FARMBOOKINGID']) ?></td>
                            <td><?= htmlspecialchars($booking['FARMNAME']) ?>, <?= htmlspecialchars($booking['FARMLOCATION']) ?></td>
                            <td><?= htmlspecialchars($booking['REQUESTDATE']) ?></td>
                            <td><?= htmlspecialchars($booking['APPROVALDATE']) ?></td>
                            <td><?= htmlspecialchars($booking['STATUS']) ?></td>
                            <td>RM <?= number_format($booking['BOOKINGPRICE'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
