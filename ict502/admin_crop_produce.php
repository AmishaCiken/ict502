<?php

include('./conn/conn.php');  // Ensure the database connection is included

// Fetch the user's crop produces from the database
$query = "SELECT CropProduce.CropProduceID,
                 Crop.CropID,
                 CropType.CropTypeName,
                 CropProduce.Quantity,
                 CropProduce.CropProduceDate,
                 CropProduce.CropStorageLocation
          FROM CropProduce
          JOIN Crop ON CropProduce.CropID = Crop.CropID
          JOIN CropType ON Crop.CropTypeID = CropType.CropTypeID
		   ";

$stmt = oci_parse($conn, $query);
//oci_bind_by_name($stmt, ':user_id', $user_id);
oci_execute($stmt);

// Fetching the results
$cropProduces = [];
while (($row = oci_fetch_assoc($stmt)) != false) {
    $cropProduces[] = $row;
}

// Handle add crop produce (inserting into the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cropproduce'])) {
    $crop_id = $_POST['crop_id'];
    $quantity = $_POST['quantity'];
    $crop_storage_location = $_POST['crop_storage_location'];

    // Inserting the crop produce with user_id
    $query = "INSERT INTO CropProduce (user_id, CropID, Quantity, CropProduceDate, CropStorageLocation)
              VALUES (:user_id, :crop_id, :quantity, SYSDATE, :crop_storage_location)";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':crop_id', $crop_id);
    oci_bind_by_name($stmt, ':quantity', $quantity);
    oci_bind_by_name($stmt, ':crop_storage_location', $crop_storage_location);

    if (oci_execute($stmt)) {
        oci_commit($conn);  // Ensure data is committed
        $_SESSION['success_message'] = "Crop produce added successfully!";
        header("Location: admin_crop_produce.php");
        exit();
    } else {
        $e = oci_error($stmt);
        echo "Error inserting data: " . htmlspecialchars($e['message']);
    }
}

// Handle delete crop produce
if (isset($_GET['delete_cropproduce'])) {
    $cropproduce_id = $_GET['delete_cropproduce'];

    $query = "DELETE FROM CropProduce WHERE CropProduceID = :cropproduce_id AND user_id = :user_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':cropproduce_id', $cropproduce_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Crop produce deleted successfully.";  // Set success message
        header("Location: admin_crop_produce.php");  // Redirect to show the success message
        exit();
    } else {
        $e = oci_error($stmt);
        echo "Error deleting crop produce: " . htmlspecialchars($e['message']);
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
    <script>
        // JavaScript function to hide success message after 3 seconds
        window.onload = function() {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.display = 'none';  // Hide the success message after 3 seconds
                }, 3000);  // 3000 ms = 3 seconds
            }
        }

        // JavaScript function for confirmation before delete
        function confirmDelete(cropproduce_id) {
            // Set the delete crop produce ID
            document.getElementById('confirmDeleteBtn').onclick = function() {
                window.location.href = "?delete_cropproduce=" + cropproduce_id;  // Trigger deletion
            };

            // Show the modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            deleteModal.show();
        }
    </script>
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Admin Crop Produce Management</h2>

    <!-- Display Success or Error Message -->
    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div id="successMessage" class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['delete_message'])) {
        echo '<div id="successMessage" class="alert alert-success">' . $_SESSION['delete_message'] . '</div>';
        unset($_SESSION['delete_message']);
    }
    ?>

    <!-- Add Crop Produce Button -->
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCropProduceModal">Add Crop Produce</button>
    </div>

    <!-- Table for crop produce -->
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Crop ID</th>
            <th>Crop Type</th>
            <th>Quantity</th>
            <th>Produce Date</th>
            <th>Storage Location</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($cropProduces)): ?>
            <?php foreach ($cropProduces as $produce): ?>
                <tr>
                    <td><?= htmlspecialchars($produce['CROPID']) ?></td>
                    <td><?= htmlspecialchars($produce['CROPTYPENAME']) ?></td>
                    <td><?= htmlspecialchars($produce['QUANTITY']) ?></td>
                    <td><?= htmlspecialchars($produce['CROPPRODUCEDATE']) ?></td>
                    <td><?= htmlspecialchars($produce['CROPSTORAGELOCATION']) ?></td>
                    <td>
                        <!-- Delete button with confirmation -->
                        <a href="javascript:void(0);" onclick="confirmDelete(<?= htmlspecialchars($produce['CROPPRODUCEID']) ?>)" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No crop produce records found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Crop Produce Modal -->
<div class="modal fade" id="addCropProduceModal" tabindex="-1" aria-labelledby="addCropProduceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCropProduceModalLabel">Add Crop Produce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="crop_id" class="form-label">Select Crop</label>
                        <select class="form-select" id="crop_id" name="crop_id" required>
                            <?php
                            // Fetch list of crops with their type from the database
                            $query = "SELECT Crop.CropID, CropType.CropTypeName 
                                      FROM Crop 
                                      JOIN CropType ON Crop.CropTypeID = CropType.CropTypeID 
                                      WHERE Crop.user_id = :user_id";
                            $stmt = oci_parse($conn, $query);
                            oci_bind_by_name($stmt, ':user_id', $user_id);
                            oci_execute($stmt);
                            while (($crop = oci_fetch_assoc($stmt)) != false) {
                                echo "<option value='" . htmlspecialchars($crop['CROPID']) . "'>" . htmlspecialchars($crop['CROPID']) . " - " . htmlspecialchars($crop['CROPTYPENAME']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="crop_storage_location" class="form-label">Storage Location</label>
                        <input type="text" class="form-control" id="crop_storage_location" name="crop_storage_location" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="add_cropproduce">Add Produce</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this crop produce?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<script src="script.js"></script>
    <script src="bootstrap.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
