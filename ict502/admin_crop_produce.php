<?php

<<<<<<< HEAD
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include('./conn/conn.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all crop produces
$query = "SELECT 
            cp.user_id,
            cp.cropproduceid,
            c.cropid,
            ct.croptypename,
            cp.quantity,
            TO_CHAR(cp.cropproducedate, 'YYYY-MM-DD') AS cropproducedate,
            cp.cropstoragelocation
          FROM cropproduce cp
          JOIN crop c ON cp.cropid = c.cropid
          JOIN croptype ct ON c.croptypeid = ct.croptypeid";

$stmt = oci_parse($conn, $query);
=======
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
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
oci_execute($stmt);

$cropProduces = [];
while ($row = oci_fetch_assoc($stmt)) {
    $cropProduces[] = $row;
}

// Handle add crop produce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cropproduce'])) {
    $crop_id = $_POST['crop_id'];
    $quantity = $_POST['quantity'];
    $crop_storage_location = $_POST['crop_storage_location'];

    // Get user_id from selected crop
    $user_query = "SELECT user_id FROM crop WHERE cropid = :crop_id";
    $user_stmt = oci_parse($conn, $user_query);
    oci_bind_by_name($user_stmt, ':crop_id', $crop_id);
    oci_execute($user_stmt);
    $user_row = oci_fetch_assoc($user_stmt);
    
    if (!$user_row) {
        $_SESSION['error_message'] = "Error: Selected crop has no associated user.";
        header("Location: admin_crop_produce.php");
        exit();
    }
    
    $crop_user_id = $user_row['USER_ID'] ?? $user_row['user_id'];

    // Insert new crop produce
    $insert_query = "INSERT INTO cropproduce 
                    (user_id, cropid, quantity, cropproducedate, cropstoragelocation)
                    VALUES 
                    (:user_id, :crop_id, :quantity, SYSDATE, :crop_storage_location)";
    
    $stmt = oci_parse($conn, $insert_query);
    oci_bind_by_name($stmt, ':user_id', $crop_user_id);
    oci_bind_by_name($stmt, ':crop_id', $crop_id);
    oci_bind_by_name($stmt, ':quantity', $quantity);
    oci_bind_by_name($stmt, ':crop_storage_location', $crop_storage_location);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['success_message'] = "Crop produce added successfully!";
        header("Location: admin_crop_produce.php");
        exit();
    } else {
        $e = oci_error($stmt);
        $_SESSION['error_message'] = "Error: " . htmlspecialchars($e['message']);
        header("Location: admin_crop_produce.php");
        exit();
    }
}

// Handle delete
if (isset($_GET['delete_cropproduce'])) {
    $cropproduce_id = $_GET['delete_cropproduce'];
    
    $delete_query = "DELETE FROM cropproduce WHERE cropproduceid = :cropproduce_id";
    $stmt = oci_parse($conn, $delete_query);
    oci_bind_by_name($stmt, ':cropproduce_id', $cropproduce_id);
    
    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Record deleted successfully!";
    } else {
        $e = oci_error($stmt);
        $_SESSION['error_message'] = "Delete error: " . htmlspecialchars($e['message']);
    }
    header("Location: admin_crop_produce.php");
    exit();
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
        window.onload = function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => alert.style.display = 'none', 3000);
            });
        }

        function confirmDelete(id) {
            if (confirm("Delete this record permanently?")) {
                window.location.href = `?delete_cropproduce=${id}`;
            }
        }
    </script>
</head>
<body>
<div class="container my-4">
    <h2 class="text-center mb-4">Crop Produce Management</h2>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addModal">
            Add New Produce
        </button>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Crop ID</th>
                <th>Crop Type</th>
                <th>Quantity</th>
                <th>Produce Date</th>
                <th>Storage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cropProduces as $produce): ?>
                <tr>
                    <td><?= htmlspecialchars($produce['USER_ID'] ?? $produce['user_id'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($produce['CROPID'] ?? '') ?></td>
                    <td><?= htmlspecialchars($produce['CROPTYPENAME'] ?? '') ?></td>
                    <td><?= htmlspecialchars($produce['QUANTITY'] ?? '') ?></td>
                    <td><?= htmlspecialchars($produce['CROPPRODUCEDATE'] ?? '') ?></td>
                    <td><?= htmlspecialchars($produce['CROPSTORAGELOCATION'] ?? '') ?></td>
                    <td>
                        <button onclick="confirmDelete(<?= $produce['CROPPRODUCEID'] ?>)" 
                                class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Crop Produce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Crop</label>
                        <select name="crop_id" class="form-select" required>
                            <?php
                            $crop_query = "SELECT c.cropid, c.user_id, ct.croptypename 
                                         FROM crop c
                                         JOIN croptype ct ON c.croptypeid = ct.croptypeid";
                            $stmt = oci_parse($conn, $crop_query);
                            oci_execute($stmt);
                            
                            while ($crop = oci_fetch_assoc($stmt)) {
                                $display = sprintf("%s - %s (%s)",
                                    $crop['USER_ID'] ?? $crop['user_id'],
                                    $crop['CROPTYPENAME'],
                                    $crop['CROPID']
                                );
                                echo "<option value='{$crop['CROPID']}'>" . htmlspecialchars($display) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" step="0.01" name="quantity" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Storage Location</label>
                        <input type="text" name="crop_storage_location" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_cropproduce" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<<<<<<< HEAD
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
=======
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
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
</body>
</html>