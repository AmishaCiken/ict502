<?php
session_start();
include('./conn/conn.php');  // Ensure the database connection is included

// Get the FarmerID from the session (assuming the user is logged in)
$farmerID = $_SESSION['user_id'];  // Replace with your session variable

// Get the FarmID from the URL
$farmID = $_GET['farmID'];  // This is passed when the user clicks the "Book Now" button

// Calculate the booking price or fetch it based on farm
$bookingPrice = 1000.00; // Example price, adjust as needed

// Insert the booking into the FarmBooking table with status 'Pending'
$query = "
    INSERT INTO FarmBooking (FarmerID, FarmID, Status, BookingPrice, AdminID)
    VALUES (:farmerID, :farmID, 'Pending', :bookingPrice, NULL)"; // NULL for AdminID for now

// Prepare and execute the query using OCI8
$stid = oci_parse($conn, $query);

// Bind the parameters for the query
oci_bind_by_name($stid, ":farmerID", $farmerID);
oci_bind_by_name($stid, ":farmID", $farmID);
oci_bind_by_name($stid, ":bookingPrice", $bookingPrice);

// Execute the query
if (!oci_execute($stid)) {
    $error = oci_error($stid);
    echo "Error executing query: " . $error['message'];
    exit;
}

// Redirect to the "My Bookings" page
header("Location: booking_farm.php");
exit(); // Make sure to call exit to stop further script execution
?>
