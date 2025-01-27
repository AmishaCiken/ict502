<?php
// Database connection
session_start();

include('./conn/conn.php'); 

// Handle the form submission to update farm data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_farm'])) {
    $farm_id = $_POST['farm_id'];
    $farm_name = $_POST['farm_name'];
    $farm_location = $_POST['farm_location'];
    $farm_size = $_POST['farm_size'];
    $water_source = $_POST['water_source'];

    $update_query = "UPDATE Farm SET FARMNAME = :farm_name, FARMLOCATION = :farm_location, FARMSIZE = :farm_size, WATERSOURCE = :water_source WHERE FARMID = :farm_id";
    $stmt = oci_parse($conn, $update_query);
    oci_bind_by_name($stmt, ':farm_id', $farm_id);
    oci_bind_by_name($stmt, ':farm_name', $farm_name);
    oci_bind_by_name($stmt, ':farm_location', $farm_location);
    oci_bind_by_name($stmt, ':farm_size', $farm_size);
    oci_bind_by_name($stmt, ':water_source', $water_source);

    if (oci_execute($stmt)) {
        echo "<script>alert('Farm updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating farm!');</script>";
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_farm'])) {
    $farm_id = $_POST['farm_id'];

    $delete_query = "DELETE FROM Farm WHERE FARMID = :farm_id";
    $stmt = oci_parse($conn, $delete_query);
    oci_bind_by_name($stmt, ':farm_id', $farm_id);

    if (oci_execute($stmt)) {
        echo "<script>alert('Farm deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting farm!');</script>";
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
    <div class="container mt-5">
        <h1 class="mb-4">Available Farms</h1>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Farm ID</th>
                    <th>Farm Name</th>
                    <th>Location</th>
                    <th>Size (ha)</th>
                    <th>Water Source</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch farm data
                $query = "SELECT * FROM Farm";
                $stmt = oci_parse($conn, $query);
                oci_execute($stmt);
                while ($farm = oci_fetch_assoc($stmt)) {
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($farm['FARMID']) ?></td>
                        <td><?= htmlspecialchars($farm['FARMNAME']) ?></td>
                        <td><?= htmlspecialchars($farm['FARMLOCATION']) ?></td>
                        <td><?= htmlspecialchars($farm['FARMSIZE']) ?></td>
                        <td><?= htmlspecialchars($farm['WATERSOURCE']) ?></td>
                        <td>
                            <!-- Edit Button -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editFarmModal"
                                data-farmid="<?= htmlspecialchars($farm['FARMID']) ?>"
                                data-farmname="<?= htmlspecialchars($farm['FARMNAME']) ?>"
                                data-farmlocation="<?= htmlspecialchars($farm['FARMLOCATION']) ?>"
                                data-farmsize="<?= htmlspecialchars($farm['FARMSIZE']) ?>"
                                data-watersource="<?= htmlspecialchars($farm['WATERSOURCE']) ?>">
                                Edit
                            </button>
                            <!-- Delete Button -->
                            <form method="POST" action="" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this farm?');">
                                <input type="hidden" name="farm_id" value="<?= htmlspecialchars($farm['FARMID']) ?>">
                                <button type="submit" name="delete_farm" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Farm Modal -->
    <div class="modal fade" id="editFarmModal" tabindex="-1" aria-labelledby="editFarmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFarmModalLabel">Edit Farm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFarmForm" method="POST" action="">
                        <input type="hidden" name="farm_id" id="farm_id">
                        <input type="hidden" name="update_farm" value="1">
                        <div class="mb-3">
                            <label for="farm_name" class="form-label">Farm Name</label>
                            <input type="text" class="form-control" name="farm_name" id="farm_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="farm_location" class="form-label">Location</label>
                            <input type="text" class="form-control" name="farm_location" id="farm_location" required>
                        </div>
                        <div class="mb-3">
                            <label for="farm_size" class="form-label">Size (ha)</label>
                            <input type="number" class="form-control" name="farm_size" id="farm_size" required>
                        </div>
                        <div class="mb-3">
                            <label for="water_source" class="form-label">Water Source</label>
                            <input type="text" class="form-control" name="water_source" id="water_source" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Populate modal fields with farm data
        const editFarmModal = document.getElementById('editFarmModal');
        editFarmModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            document.getElementById('farm_id').value = button.getAttribute('data-farmid');
            document.getElementById('farm_name').value = button.getAttribute('data-farmname');
            document.getElementById('farm_location').value = button.getAttribute('data-farmlocation');
            document.getElementById('farm_size').value = button.getAttribute('data-farmsize');
            document.getElementById('water_source').value = button.getAttribute('data-watersource');
        });
    </script>
</body>
</html>
