<?php
session_start();

<<<<<<< HEAD
=======
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];  // Critical missing line!
>>>>>>> b943c2013a6de75910b6531d71e14daed930972b

// Ensure the database connection is included
include('./conn/conn.php');

// Fetch the user's crops from the database
$query = "SELECT Crop.CropID, Farm.FarmName, CropType.CropTypeName, Crop.PlantingDate, Crop.HarvestDate, Crop.Yield
          FROM Crop
          JOIN Farm ON Crop.FarmID = Farm.FarmID
          JOIN CropType ON Crop.CropTypeID = CropType.CropTypeID
          WHERE Crop.user_id = :user_id";

$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ':user_id', $user_id);
oci_execute($stid);

// Fetching the results
$crops = [];
while ($row = oci_fetch_assoc($stid)) {
    $crops[] = $row;
}

// Handle add crop (inserting into the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_crop'])) {
    $farm_id = $_POST['farm_id'];
    $crop_type_id = $_POST['crop_type_id'];
    $planting_date = $_POST['planting_date'];
    $harvest_date = $_POST['harvest_date'];
    $yield = $_POST['yield'];

    $query = "INSERT INTO Crop (user_id, FarmID, CropTypeID, PlantingDate, HarvestDate, Yield) 
              VALUES (:user_id, :farm_id, :crop_type_id, TO_DATE(:planting_date, 'YYYY-MM-DD'), TO_DATE(:harvest_date, 'YYYY-MM-DD'), :yield)";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ':user_id', $user_id);
    oci_bind_by_name($stid, ':farm_id', $farm_id);
    oci_bind_by_name($stid, ':crop_type_id', $crop_type_id);
    oci_bind_by_name($stid, ':planting_date', $planting_date);
    oci_bind_by_name($stid, ':harvest_date', $harvest_date);
    oci_bind_by_name($stid, ':yield', $yield);

    if (oci_execute($stid)) {
        oci_commit($conn);
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Crop added successfully!';
        header("Location: crop.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to add the crop. Please try again.';
    }
}

// Handle edit crop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_crop'])) {
    $crop_id = $_POST['crop_id'];
    $farm_id = $_POST['farm_id'];
    $crop_type_id = $_POST['crop_type_id'];
    $planting_date = $_POST['planting_date'];
    $harvest_date = $_POST['harvest_date'];
    $yield = $_POST['yield'];

    $query = "UPDATE Crop 
              SET FarmID = :farm_id, 
                  CropTypeID = :crop_type_id, 
                  PlantingDate = TO_DATE(:planting_date, 'YYYY-MM-DD'), 
                  HarvestDate = TO_DATE(:harvest_date, 'YYYY-MM-DD'), 
                  Yield = :yield 
              WHERE CropID = :crop_id AND user_id = :user_id";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ':farm_id', $farm_id);
    oci_bind_by_name($stid, ':crop_type_id', $crop_type_id);
    oci_bind_by_name($stid, ':planting_date', $planting_date);
    oci_bind_by_name($stid, ':harvest_date', $harvest_date);
    oci_bind_by_name($stid, ':yield', $yield);
    oci_bind_by_name($stid, ':crop_id', $crop_id);
    oci_bind_by_name($stid, ':user_id', $user_id);

    if (oci_execute($stid)) {
        oci_commit($conn);
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Crop updated successfully!';
        header("Location: crop.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to update the crop. Please try again.';
    }
}

// Handle delete crop
if (isset($_GET['delete_crop'])) {
    $crop_id = $_GET['delete_crop'];

    $query = "DELETE FROM Crop WHERE CropID = :crop_id AND user_id = :user_id";
    $stid = oci_parse($conn, $query);

    oci_bind_by_name($stid, ':crop_id', $crop_id);
    oci_bind_by_name($stid, ':user_id', $user_id);

    if (oci_execute($stid)) {
        oci_commit($conn);
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Crop deleted successfully!';
        header("Location: crop.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to delete the crop. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop</title>
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
<div class="container my-4">
    <h2 class="text-center">Crop Management</h2>

    <!-- Display success or error message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo ($_SESSION['alert_type'] == 'success') ? 'success' : 'danger'; ?> mt-3" role="alert" id="message">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php unset($_SESSION['message']); ?>
        <?php unset($_SESSION['alert_type']); ?>
    <?php endif; ?>

    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCropModal">Add Crop</button>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Farm Name</th>
            <th>Crop Type</th>
            <th>Planting Date</th>
            <th>Harvest Date</th>
            <th>Yield</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
            <?php if (!empty($crops)): ?>
                <?php foreach ($crops as $crop): ?>
                    <tr>
                        <td><?= htmlspecialchars($crop['FARMNAME'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($crop['CROPTYPENAME'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($crop['PLANTINGDATE'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($crop['HARVESTDATE'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($crop['YIELD'] ?? 'N/A') ?></td>
                        <td>
                            <a href="?delete_crop=<?= htmlspecialchars($crop['CROPID']) ?>" class="btn btn-danger btn-sm">Delete</a>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCropModal<?= htmlspecialchars($crop['CROPID']) ?>">Edit</button>
                        </td>
                    </tr>

                    <!-- Edit Crop Modal for each crop -->
                    <div class="modal fade" id="editCropModal<?= htmlspecialchars($crop['CROPID']) ?>" tabindex="-1" aria-labelledby="editCropModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editCropModalLabel">Edit Crop</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="crop_id" value="<?= htmlspecialchars($crop['CROPID']) ?>">
                                        <div class="mb-3">
                                            <label for="farm_id" class="form-label">Select Farm</label>
                                            <select class="form-select" id="farm_id" name="farm_id" required>
                                                <?php
                                                // Fetch list of farms from the database
                                                $query = "SELECT * FROM Farm";
                                                $stid = oci_parse($conn, $query);
                                                oci_execute($stid);
                                                while ($farm = oci_fetch_assoc($stid)) {
                                                    $selected = ($farm['FARMID'] == $crop['FARMID']) ? 'selected' : '';
                                                    echo "<option value='".$farm['FARMID']."' $selected>".$farm['FARMNAME']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="crop_type_id" class="form-label">Select Crop Type</label>
                                            <select class="form-select" id="crop_type_id" name="crop_type_id" required>
                                                <?php
                                                // Fetch list of crop types from the database
                                                $query = "SELECT CropTypeID, CropTypeName FROM CropType";
                                                $stid = oci_parse($conn, $query);
                                                oci_execute($stid);
                                                while ($type = oci_fetch_assoc($stid)) {
                                                    $selected = ($type['CROPTYPEID'] == $crop['CROPTYPEID']) ? 'selected' : '';
                                                    echo "<option value='".$type['CROPTYPEID']."' $selected>".$type['CROPTYPENAME']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="planting_date" class="form-label">Planting Date</label>
                                            <input type="date" class="form-control" id="planting_date" name="planting_date" value="<?= htmlspecialchars($crop['PLANTINGDATE']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="harvest_date" class="form-label">Harvest Date</label>
                                            <input type="date" class="form-control" id="harvest_date" name="harvest_date" value="<?= htmlspecialchars($crop['HARVESTDATE']) ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="yield" class="form-label">Yield</label>
                                            <input type="number" step="0.01" class="form-control" id="yield" name="yield" value="<?= htmlspecialchars($crop['YIELD']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary" name="edit_crop">Save Changes</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No crops found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Crop Modal -->
<div class="modal fade" id="addCropModal" tabindex="-1" aria-labelledby="addCropModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCropModalLabel">Add Crop</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="farm_id" class="form-label">Select Farm</label>
                        <select class="form-select" id="farm_id" name="farm_id" required>
                            <?php
                            // Fetch list of farms from the database
                            $query = "SELECT * FROM Farm";
                            $stid = oci_parse($conn, $query);
                            oci_execute($stid);
                            while ($farm = oci_fetch_assoc($stid)) {
                                echo "<option value='".$farm['FARMID']."'>".$farm['FARMNAME']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="crop_type_id" class="form-label">Select Crop Type</label>
                        <select class="form-select" id="crop_type_id" name="crop_type_id" required>
                            <?php
                            // Fetch list of crop types from the database
                            $query = "SELECT CropTypeID, CropTypeName FROM CropType";
                            $stid = oci_parse($conn, $query);
                            oci_execute($stid);
                            while ($type = oci_fetch_assoc($stid)) {
                                echo "<option value='".$type['CROPTYPEID']."'>".$type['CROPTYPENAME']."</option>";
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
                        <input type="number" step="0.01" class="form-control" id="yield" name="yield" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="add_crop">Add Crop</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="script.js"></script>
<script src="bootstrap.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Automatically hide the alert after 2 seconds
    window.onload = function() {
        const message = document.getElementById('message');
        if (message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 2000);  // 2000 milliseconds = 2 seconds
        }
    };
</script>

</body>
</html>
