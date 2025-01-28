<?php
session_start();


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
    $user_id = $_POST['user_id'];
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

// Handle deletion
if (isset($_GET['delete_crop'])) {
    $crop_id = $_GET['delete_crop'];
    
    $query = "DELETE FROM Crop WHERE CropID = :crop_id";
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

// Handle edit submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_crop'])) {
    $crop_id = $_POST['crop_id'];
    $harvest_date = $_POST['harvest_date'];
    $yield = $_POST['yield'];

    $query = "UPDATE Crop 
              SET HarvestDate = TO_DATE(:harvest_date, 'YYYY-MM-DD'), 
                  Yield = :yield 
              WHERE CropID = :crop_id";
    
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ':harvest_date', $harvest_date);
    oci_bind_by_name($stid, ':yield', $yield);
    oci_bind_by_name($stid, ':crop_id', $crop_id);

    if (oci_execute($stid)) {
        oci_commit($conn);
        $_SESSION['success_message'] = "Crop updated successfully!";
    } else {
        $e = oci_error($stid);
        $_SESSION['error_message'] = "Error updating crop: " . htmlspecialchars($e['message']);
    }
    header("Location: admin_crop.php");
    exit();
}

// Fetch crop data for editing
$editCrop = null;
if (isset($_GET['edit_id'])) {
    $query = "SELECT * FROM Crop WHERE CropID = :crop_id";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ':crop_id', $_GET['edit_id']);
    oci_execute($stid);
    $editCrop = oci_fetch_assoc($stid);
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
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css">
</head>
<body class="bg-content">
    <main class="dashboard d-flex">
        <?php include "admin_sidebar.php"; ?>
        <div class="container-fluid px">
            <?php include "header.php"; ?>
            
            <script>
                window.onload = function() {
                    setTimeout(() => {
                        const alert = document.querySelector('.alert');
                        if (alert) alert.style.display = 'none';
                    }, 3000);

                    // Automatically show edit modal if edit_id is present
                    <?php if (isset($_GET['edit_id'])): ?>
                        const editModal = new bootstrap.Modal(document.getElementById('editCropModal'));
                        editModal.show();
                    <?php endif; ?>
                }

                function confirmDelete(cropId) {
                    if (confirm("Are you sure you want to delete this crop?")) {
                        window.location.href = "?delete_crop=" + cropId;
                    }
                }
            </script>

            <div class="container my-4">
                <h2 class="text-center">Admin Crop Management</h2>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php elseif (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <div class="text-end mb-3">
                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCropModal">Add Crop</button>
                </div>

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
                        <?php foreach ($crops as $crop): ?>
                            <tr>
                                <td><?= htmlspecialchars($crop['USER_ID'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($crop['FARMNAME'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($crop['CROPTYPENAME'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($crop['PLANTINGDATE'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($crop['HARVESTDATE'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($crop['YIELD'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="?edit_id=<?= $crop['CROPID'] ?>" class="btn btn-primary btn-sm me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button onclick="confirmDelete(<?= $crop['CROPID'] ?>)" 
                                            class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Edit Crop Modal -->
            <div class="modal fade" id="editCropModal" tabindex="-1" aria-labelledby="editCropModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Crop</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="crop_id" value="<?= $editCrop['CROPID'] ?? '' ?>">
                                <div class="mb-3">
                                <label class="form-label">Planting Date</label>
                                    <input type="date" name="harvest_date" 
                                    value="<?= htmlspecialchars($editCrop['PLANTINGDATE'] ?? '') ?>" 
                                           class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Harvest Date</label>
                                    <input type="date" name="harvest_date" 
                                           value="<?= htmlspecialchars($editCrop['HARVESTDATE'] ?? '') ?>" 
                                           class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Yield</label>
                                    <input type="number" name="yield" step="0.01"
                                           value="<?= htmlspecialchars($editCrop['YIELD'] ?? '') ?>" 
                                           class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="edit_crop" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
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
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Select User</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <?php
                                        $query = "SELECT tbl_user_id, first_name FROM tbl_user ORDER BY tbl_user_id";
                                        $stid = oci_parse($conn, $query);
                                        oci_execute($stid);
                                        while ($user = oci_fetch_assoc($stid)) {
                                            echo "<option value='{$user['TBL_USER_ID']}'>" 
                                                . htmlspecialchars($user['TBL_USER_ID'] . " - " . ($user['FIRST_NAME'] ?? '')) 
                                                . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
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
            <script src="script.js"></script>
            <script src="bootstrap.bundle.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        </div>
    </main>
</body>
</html>