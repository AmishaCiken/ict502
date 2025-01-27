<?php
session_start();
include('conn/conn.php'); // Oracle database connection

// Ensure the user is logged in
if (!isset($_SESSION['EMAIL_ADDRESS'])) {
    header("Location: index.php");
    exit;
}

// Fetch the number of farmers (from TBL_USER)
$query_farmers = "SELECT COUNT(*) AS TOTAL FROM TBL_USER";
$stmt_farmers = oci_parse($conn, $query_farmers);
if (!$stmt_farmers) {
    die('Query failed: ' . oci_error($conn));
}
oci_execute($stmt_farmers);
$row_farmers = oci_fetch_assoc($stmt_farmers);
$nbr_students = $row_farmers['TOTAL'];
oci_free_statement($stmt_farmers);

// Fetch the number of farms (from FARM)
$query_farms = "SELECT COUNT(*) AS TOTAL FROM FARM";
$stmt_farms = oci_parse($conn, $query_farms);
if (!$stmt_farms) {
    die('Query failed: ' . oci_error($conn));
}
oci_execute($stmt_farms);
$row_farms = oci_fetch_assoc($stmt_farms);
$nbr_cours = $row_farms['TOTAL'];
oci_free_statement($stmt_farms);

// Fetch the total payments (from PAYMENTS table)
$query_payments = "SELECT SUM(PAYMENT_AMOUNT) AS TOTAL_PAYMENTS FROM PAYMENTS";
$stmt_payments = oci_parse($conn, $query_payments);
if (!$stmt_payments) {
    die('Query failed: ' . oci_error($conn));
}

// Close database connection
oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style3.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
</head>
<body class="bg-content">
    <main class="dashboard d-flex">
        <!-- Sidebar -->
        <?php include "sidebar.php"; ?>

        <!-- Content Page -->
        <div class="container-fluid px">
            <?php include "header.php"; ?>
            <div class="cards row gap-3 justify-content-center mt-5">
                <div class=" card__items card__items--blue col-md-3 position-relative">
                    <div class="card__students d-flex flex-column gap-2 mt-3">
                        <i class="far fa-graduation-cap h3"></i>
                        <span></span>
                    </div>
                    <div class="card__nbr-students">
                        <span class="h5 fw-bold nbr"><?php echo $nbr_students; ?></span>
                    </div>
                </div>

                <div class=" card__items card__items--rose col-md-3 position-relative">
                    <div class="card__Course d-flex flex-column gap-2 mt-3">
                        <i class="fal fa-bookmark h3"></i>
                        <span></span>
                    </div>
                    <div class="card__nbr-course">
                        <span class="h5 fw-bold nbr"><?php echo $nbr_cours; ?></span>
                    </div>
                </div>

                <div class=" card__items card__items--yellow col-md-3 position-relative">
                    <div class="card__payments d-flex flex-column gap-2 mt-3">
                        <i class="fal fa-usd-square h3"></i>
                        <span></span>
                    </div>
                
                </div>
            </div>
        </div>
        <!-- End Content Page -->
    </main>
    <script src="script.js"></script>
    <script src="bootstrap.bundle.js"></script>
</body>
</html>
