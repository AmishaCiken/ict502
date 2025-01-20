<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Ensure the database connection is included
include('./conn/conn.php');  

$user_id = $_SESSION['user_id'];

// Fetch the user's crops from the database using OCI8
$query = "
    SELECT Crop.CropID, CropType.CropTypeName, Crop.PlantingDate, Crop.HarvestDate, Crop.Yield
    FROM Crop
    JOIN CropType ON Crop.CropTypeID = CropType.CropTypeID
    WHERE Crop.user_id = :user_id
";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":user_id", $user_id);
oci_execute($stid);

// Fetching the results
$crops = [];
while ($row = oci_fetch_assoc($stid)) {
    $crops[] = $row;
}

// Handle add crop (inserting into the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_crop'])) {
    $crop_type_id = $_POST['crop_type_id'];
    $planting_date = $_POST['planting_date'];
    $harvest_date = $_POST['harvest_date'];
    $yield = $_POST['yield'];

    $insert_query = "
        INSERT INTO Crop (user_id, CropTypeID, PlantingDate, HarvestDate, Yield) 
        VALUES (:user_id, :crop_type_id, :planting_date, :harvest_date, :yield)
    ";
    $insert_stid = oci_parse($conn, $insert_query);
    
    oci_bind_by_name($insert_stid, ":user_id", $user_id);
    oci_bind_by_name($insert_stid, ":crop_type_id", $crop_type_id);
    oci_bind_by_name($insert_stid, ":planting_date", $planting_date);
    oci_bind_by_name($insert_stid, ":harvest_date", $harvest_date);
    oci_bind_by_name($insert_stid, ":yield", $yield);

    oci_execute($insert_stid, OCI_COMMIT_ON_SUCCESS);

    header("Location: crop_management.php");  // Refresh the page to show the added crop
    exit();
}

// Handle delete crop
if (isset($_GET['delete_crop'])) {
    $crop_id = $_GET['delete_crop'];
    $delete_query = "DELETE FROM Crop WHERE CropID = :crop_id AND user_id = :user_id";
    $delete_stid = oci_parse($conn, $delete_query);
    oci_bind_by_name($delete_stid, ":crop_id", $crop_id);
    oci_bind_by_name($delete_stid, ":user_id", $user_id);
    oci_execute($delete_stid, OCI_COMMIT_ON_SUCCESS);
    header("Location: crop_management.php");  // Refresh the page to reflect the deletion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Crop Management</h2>
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCropModal">Add Crop</button>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Crop ID</th>
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
                    <td><?= htmlspecialchars($crop['CROPID']) ?></td>
                    <td><?= htmlspecialchars($crop['CROPTYPENAME']) ?></td>
                    <td><?= htmlspecialchars($crop['PLANTINGDATE']) ?></td>
                    <td><?= htmlspecialchars($crop['HARVESTDATE']) ?></td>
                    <td><?= htmlspecialchars($crop['YIELD']) ?></td>
                    <td>
                        <a href="?delete_crop=<?= $crop['CROPID'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
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
                        <label for="crop_type_id" class="form-label">Select Crop Type</label>
                        <select class="form-select" id="crop_type_id" name="crop_type_id" required>
                            <?php
                            $query = "SELECT * FROM CropType";
                            $stid = oci_parse($conn, $query);
                            oci_execute($stid);
                            while ($type = oci_fetch_assoc($stid)) {
                                echo "<option value='".htmlspecialchars($type['CROPTYPEID'])."'>".htmlspecialchars($type['CROPTYPENAME'])."</option>";
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
                        <input type="date" class="form-control" id="harvest_date" name="harvest_date" required>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
