<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];  // Critical missing line!

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
          WHERE CropProduce.user_id = :user_id";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':user_id', $user_id);
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

    $query = "INSERT INTO CropProduce (user_id, CropID, Quantity, CropProduceDate, CropStorageLocation)
              VALUES (:user_id, :crop_id, :quantity, SYSDATE, :crop_storage_location)";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':crop_id', $crop_id);
    oci_bind_by_name($stmt, ':quantity', $quantity);
    oci_bind_by_name($stmt, ':crop_storage_location', $crop_storage_location);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Crop produce added successfully!';
        header("Location: crop_produce.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to add the produce. Please try again.';
    }
}

// Handle edit crop produce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_cropproduce'])) {
    $cropproduce_id = $_POST['cropproduce_id'];
    $crop_id = $_POST['crop_id'];
    $quantity = $_POST['quantity'];
    $crop_storage_location = $_POST['crop_storage_location'];

    $query = "UPDATE CropProduce 
              SET CropID = :crop_id, 
                  Quantity = :quantity, 
                  CropStorageLocation = :crop_storage_location 
              WHERE CropProduceID = :cropproduce_id AND user_id = :user_id";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':crop_id', $crop_id);
    oci_bind_by_name($stmt, ':quantity', $quantity);
    oci_bind_by_name($stmt, ':crop_storage_location', $crop_storage_location);
    oci_bind_by_name($stmt, ':cropproduce_id', $cropproduce_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Crop produce updated successfully!';
        header("Location: crop_produce.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to update the produce. Please try again.';
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
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Crop produce deleted successfully!';
        header("Location: crop_produce.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to delete the produce. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Produce</title>
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
    <h2 class="text-center">Crop Produce Management</h2>

    <!-- Display success or error message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo ($_SESSION['alert_type'] == 'success') ? 'success' : 'danger'; ?> mt-3" role="alert" id="message">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php unset($_SESSION['message']); ?>
        <?php unset($_SESSION['alert_type']); ?>
    <?php endif; ?>

    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCropProduceModal">Add Crop Produce</button>
    </div>
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
                        <a href="?delete_cropproduce=<?= $produce['CROPPRODUCEID'] ?>" class="btn btn-danger btn-sm">Delete</a>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCropProduceModal<?= $produce['CROPPRODUCEID'] ?>">Edit</button>
                    </td>
                </tr>

                <!-- Edit Crop Produce Modal for each produce -->
                <div class="modal fade" id="editCropProduceModal<?= $produce['CROPPRODUCEID'] ?>" tabindex="-1" aria-labelledby="editCropProduceModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editCropProduceModalLabel">Edit Crop Produce</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="cropproduce_id" value="<?= $produce['CROPPRODUCEID'] ?>">
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
                                                $selected = ($crop['CROPID'] == $produce['CROPID']) ? 'selected' : '';
                                                echo "<option value='" . htmlspecialchars($crop['CROPID']) . "' $selected>" . htmlspecialchars($crop['CROPID']) . " - " . htmlspecialchars($crop['CROPTYPENAME']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" value="<?= htmlspecialchars($produce['QUANTITY']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="crop_storage_location" class="form-label">Storage Location</label>
                                        <input type="text" class="form-control" id="crop_storage_location" name="crop_storage_location" value="<?= htmlspecialchars($produce['CROPSTORAGELOCATION']) ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary" name="edit_cropproduce">Save Changes</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
                    <button type="submit" class="btn btn-primary" name="add_cropproduce">Add Crop Produce</button>
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
