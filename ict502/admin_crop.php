<?php
session_start();

<<<<<<< HEAD
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

=======
// Ensure the database connection is included
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
include('./conn/conn.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all crops
$query = "SELECT Crop.CropID, Farm.FarmName, CropType.CropTypeName, Crop.PlantingDate, 
                 Crop.HarvestDate, Crop.Yield, Crop.user_id 
          FROM Crop 
          JOIN Farm ON Crop.FarmID = Farm.FarmID 
          JOIN CropType ON Crop.CropTypeID = CropType.CropTypeID";

$stid = oci_parse($conn, $query);
oci_execute($stid);

$crops = [];
while ($row = oci_fetch_assoc($stid)) {
    $crops[] = $row;
}

// Handle crop addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_crop'])) {
    $user_id = $_POST['user_id'];  // Get selected user from dropdown
    $farm_id = $_POST['farm_id'];
    $crop_type_id = $_POST['crop_type_id'];
    $planting_date = $_POST['planting_date'];
    $harvest_date = $_POST['harvest_date'];
    $yield = $_POST['yield'];

    // Validation
    if (!is_numeric($farm_id) || !is_numeric($crop_type_id)) {
        $_SESSION['error_message'] = "Invalid farm or crop type selection.";
    } elseif (!is_numeric($yield)) {
        $_SESSION['error_message'] = "Yield must be a numeric value.";
    } else {
        $query = "INSERT INTO Crop (FarmID, CropTypeID, PlantingDate, HarvestDate, Yield, user_id) 
                  VALUES (:farm_id, :crop_type_id, TO_DATE(:planting_date, 'YYYY-MM-DD'), 
                          TO_DATE(:harvest_date, 'YYYY-MM-DD'), :yield, :user_id)";
        
        $stid = oci_parse($conn, $query);
        oci_bind_by_name($stid, ':farm_id', $farm_id);
        oci_bind_by_name($stid, ':crop_type_id', $crop_type_id);
        oci_bind_by_name($stid, ':planting_date', $planting_date);
        oci_bind_by_name($stid, ':harvest_date', $harvest_date);
        oci_bind_by_name($stid, ':yield', $yield);
        oci_bind_by_name($stid, ':user_id', $user_id);

        if (oci_execute($stid)) {
            oci_commit($conn);
            $_SESSION['success_message'] = "Crop added successfully!";
        } else {
            $e = oci_error($stid);
            $_SESSION['error_message'] = "Error adding crop: " . htmlspecialchars($e['message']);
        }
    }
}

<<<<<<< HEAD
// Handle deletion
=======
// Update crop (UPDATE)
if (isset($_POST['updateCrop'])) {
    $cropID = $_POST['cropID'];
    $plotID = $_POST['plotID'];
    $cropTypeID = $_POST['cropTypeID'];
    $plantingDate = $_POST['plantingDate'];
    $harvestDate = $_POST['harvestDate'];
    $yield = $_POST['yield'];

    $sql = "UPDATE farmingSys.Crop SET PlotID = :plotID, CropTypeID = :cropTypeID, 
            PlantingDate = TO_DATE(:plantingDate, 'YYYY-MM-DD'), HarvestDate = TO_DATE(:harvestDate, 'YYYY-MM-DD'), Yield = :yield
            WHERE CropID = :cropID";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":plotID", $plotID);
    oci_bind_by_name($stid, ":cropTypeID", $cropTypeID);
    oci_bind_by_name($stid, ":plantingDate", $plantingDate);
    oci_bind_by_name($stid, ":harvestDate", $harvestDate);
    oci_bind_by_name($stid, ":yield", $yield);
    oci_bind_by_name($stid, ":cropID", $cropID);
    oci_execute($stid);

    header("Location: crop_crud.php");
}
// Handle delete Crop
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
if (isset($_GET['delete_crop'])) {
    $crop_id = $_GET['delete_crop'];
    
    $query = "DELETE FROM Crop WHERE CropID = :crop_id";  // Removed user_id check
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':crop_id', $crop_id);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Crop deleted successfully.";
    } else {
        $e = oci_error($stmt);
        $_SESSION['error_message'] = "Error deleting crop: " . htmlspecialchars($e['message']);
    }
    header("Location: admin_crop.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Produce Management</title>
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
    <script>
        window.onload = function() {
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) alert.style.display = 'none';
            }, 3000);
        }

        function confirmDelete(cropId) {
            if (confirm("Are you sure you want to delete this crop?")) {
                window.location.href = "?delete_crop=" + cropId;
            }
        }
    </script>
</head>
<body>

<div class="container my-4">
    <h2 class="text-center">Admin Crop Management</h2>
    
    <!-- Messages -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php elseif (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Add Button -->
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCropModal">Add Crop</button>
    </div>

    <!-- Crop Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>UserID</th>
                <th>Farm Name</th>
                <th>Crop Type</th>
                <th>Planting Date</th>
                <th>Harvest Date</th>
                <th>Yield</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
<<<<<<< HEAD
            <?php foreach ($crops as $crop): ?>
=======
            <?php if (!empty($crops)): ?>
                <?php foreach ($crops as $crop): ?>
                    <tr>
                        <td><?= htmlspecialchars($crop['FARMNAME'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($crop['CROPTYPENAME'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($crop['PLANTINGDATE'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($crop['HARVESTDATE'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($crop['YIELD'] ?? 'N/A') ?></td>
                        <td>
                        <a href="admin_crop.php?updateCropID=<?php echo $crop['CROPID']; ?>">Edit</a> |
                            <a href="javascript:void(0);" onclick="confirmDelete(<?= htmlspecialchars($crop['CROPID']) ?>)" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
                <tr>
                    <td><?= htmlspecialchars($crop['USER_ID'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($crop['FARMNAME'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($crop['CROPTYPENAME'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($crop['PLANTINGDATE'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($crop['HARVESTDATE'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($crop['YIELD'] ?? 'N/A') ?></td>
                    <td>
                        <a href="javascript:void(0);" 
                           onclick="confirmDelete(<?= $crop['CROPID'] ?>)" 
                           class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Crop Modal -->
<div class="modal fade" id="addCropModal" tabindex="-1" aria-labelledby="addCropModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Add Crop</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- In the Add Crop Modal section -->
                <div class="mb-3">
                    <label for="user_id" class="form-label">Select User</label>
                    <select class="form-select" id="user_id" name="user_id" required>
                        <?php
                        $query = "SELECT tbl_user_id, first_name FROM tbl_user ORDER BY tbl_user_id";
                        $stid = oci_parse($conn, $query);
                        if (!oci_execute($stid)) {
                            $e = oci_error($stid);
                            echo "<option>Error fetching users: " . htmlspecialchars($e['message']) . "</option>";
                        } else {
                            while ($user = oci_fetch_assoc($stid)) {
                                $display = htmlspecialchars($user['TBL_USER_ID'] . " - " . ($user['FIRST_NAME'] ?? ''));
                                echo "<option value='{$user['TBL_USER_ID']}'>$display</option>";
                            }
                            if (oci_num_rows($stid) === 0) {
                                echo "<option>No users found</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                    <!-- Farm Selection -->
                    <div class="mb-3">
                        <label for="farm_id" class="form-label">Select Farm</label>
                        <select class="form-select" id="farm_id" name="farm_id" required>
                            <?php
                            $query = "SELECT FarmID, FarmName FROM Farm";
                            $stid = oci_parse($conn, $query);
                            oci_execute($stid);
                            while ($farm = oci_fetch_assoc($stid)) {
                                echo "<option value='{$farm['FARMID']}'>" . htmlspecialchars($farm['FARMNAME']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Crop Type Selection -->
                    <div class="mb-3">
                        <label for="crop_type_id" class="form-label">Select Crop Type</label>
                        <select class="form-select" id="crop_type_id" name="crop_type_id" required>
                            <?php
                            $query = "SELECT CropTypeID, CropTypeName FROM CropType";
                            $stid = oci_parse($conn, $query);
                            oci_execute($stid);
                            while ($type = oci_fetch_assoc($stid)) {
                                echo "<option value='{$type['CROPTYPEID']}'>" . htmlspecialchars($type['CROPTYPENAME']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Date Inputs -->
                    <div class="mb-3">
                        <label for="planting_date" class="form-label">Planting Date</label>
                        <input type="date" class="form-control" id="planting_date" name="planting_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="harvest_date" class="form-label">Harvest Date</label>
                        <input type="date" class="form-control" id="harvest_date" name="harvest_date">
                    </div>
                    <div class="mb-3">
                        <label for="yield" class="form-label">Yield</label>
                        <input type="number" class="form-control" id="yield" name="yield" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_crop" class="btn btn-primary">Add Crop</button>
                </div>
            </form>
        </div>
    </div>
</div>

<<<<<<< HEAD
=======
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Delete Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this crop?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
<script src="script.js"></script>
    <script src="bootstrap.bundle.js"></script>
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>