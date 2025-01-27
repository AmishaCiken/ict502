<<<<<<< HEAD
<?php
session_start();
include('conn/conn.php'); // Oracle database connection

// Ensure the user is logged in
if (!isset($_SESSION['USERNAME'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch the number of farmers (from TBL_USER)
$query_admin = "SELECT COUNT(*) AS TOTAL FROM TBL_USER";
$stmt_farmers = oci_parse($conn, $query_admin);
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

// Fetch the number of animal produce (from FARM)
$query_farms = "SELECT COUNT(*) AS TOTAL FROM ANIMALPRODUCE";
$stmt_farms = oci_parse($conn, $query_farms);
if (!$stmt_farms) {
    die('Query failed: ' . oci_error($conn));
}
oci_execute($stmt_farms);
$row_farms = oci_fetch_assoc($stmt_farms);
$nbr_cours = $row_farms['TOTAL'];
oci_free_statement($stmt_farms);

// Fetch the number of crop produce (from FARM)
$query_farms = "SELECT COUNT(*) AS TOTAL FROM CROPPRODUCE";
$stmt_farms = oci_parse($conn, $query_farms);
if (!$stmt_farms) {
    die('Query failed: ' . oci_error($conn));
}
oci_execute($stmt_farms);
$row_farms = oci_fetch_assoc($stmt_farms);
$nbr_cours = $row_farms['TOTAL'];
oci_free_statement($stmt_farms);

// Close database connection
oci_close($conn);
?>

=======
>>>>>>> 0c8af2e7174a9248811efb5b25f0ae4972200d2e
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
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
        <?php include "admin_sidebar.php"; ?>

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
=======
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Navigation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .button-container button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .button-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Admin Navigation</h1>
    <div class="button-container">
        <button onclick="window.location.href='admin_AddTool.php'">Add Tool</button>
        <button onclick="window.location.href='admin_animal.php'">Animal</button>
        <button onclick="window.location.href='admin_animalproduce.php'">Animal Produce</button>
        <button onclick="window.location.href='admin_availablefarm.php'">Available Farm</button>
        <button onclick="window.location.href='admin_bookingFarm.php'">Booking Farm</button>
        <button onclick="window.location.href='admin_crop_produce.php'">Crop Produce</button>
        <button onclick="window.location.href='admin_crop.php'">Crop</button>
        <button onclick="window.location.href='admin_tool.php'">Tool</button>
    </div>
</body>
</html>
>>>>>>> 0c8af2e7174a9248811efb5b25f0ae4972200d2e
