<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
include('./conn/conn.php');

// Query to fetch farm and plot information along with soil condition
$query = "
    SELECT f.FARMID, f.FARMNAME, f.FARMLOCATION, f.FARMSIZE, f.WATERSOURCE, 
       p.PLOTSIZE, p.PLOTTYPE, 
       s.SOILNAME, s.SOILCONDITION
    FROM Farm f
    LEFT JOIN Plot p ON f.FARMID = p.FARMID
    LEFT JOIN SoilType s ON p.SOILTYPEID = s.SOILTYPEID
";

$stmt = oci_parse($conn, $query);
if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo "Error fetching farm data: " . htmlspecialchars($e['message']);
    exit();
}

$availableFarms = [];
while ($row = oci_fetch_assoc($stmt)) {
    $availableFarms[] = $row;
}

// Handle Add Farm Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $farmname = htmlspecialchars($_POST['farmname']);
    $farmlocation = htmlspecialchars($_POST['farmlocation']);
    $farmsize = htmlspecialchars($_POST['farmsize']);
    $watersource = htmlspecialchars($_POST['watersource']);
    $plotsize = htmlspecialchars($_POST['plotsize']);
    $plottype = htmlspecialchars($_POST['plottype']);
    $soiltype = htmlspecialchars($_POST['soiltype']);
    $soilcondition = htmlspecialchars($_POST['soilcondition']);

    // Check if the farm already exists
    $checkQuery = "SELECT COUNT(*) FROM Farm WHERE FARMNAME = :farmname AND FARMLOCATION = :farmlocation";
    $checkStmt = oci_parse($conn, $checkQuery);
    oci_bind_by_name($checkStmt, ':farmname', $farmname);
    oci_bind_by_name($checkStmt, ':farmlocation', $farmlocation);
    oci_execute($checkStmt);
    $row = oci_fetch_assoc($checkStmt);

    if ($row['COUNT(*)'] > 0) {
        $_SESSION['add_message'] = "This farm already exists!";
        header("Location: admin_availablefarm.php");
        exit();
    }

    // Insert farm
    $query = "
        INSERT INTO Farm (FARMNAME, FARMLOCATION, FARMSIZE, WATERSOURCE)
        VALUES (:farmname, :farmlocation, :farmsize, :watersource)
        RETURNING FARMID INTO :farm_id
    ";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':farmname', $farmname);
    oci_bind_by_name($stmt, ':farmlocation', $farmlocation);
    oci_bind_by_name($stmt, ':farmsize', $farmsize);
    oci_bind_by_name($stmt, ':watersource', $watersource);
    
    $farm_id = null;
    oci_bind_by_name($stmt, ':farm_id', $farm_id, 32);

    if (oci_execute($stmt)) {
        // Get soil type ID
        $query_soil = "SELECT SOILTYPEID FROM SoilType WHERE SOILNAME = :soil_name";
        $stmt_soil = oci_parse($conn, $query_soil);
        oci_bind_by_name($stmt_soil, ':soil_name', $soiltype);
        oci_execute($stmt_soil);
        $soil_type_id = oci_fetch_assoc($stmt_soil)['SOILTYPEID'];

        // Insert plot (FIXED: Removed duplicate execute)
        $query_plot = "
            INSERT INTO Plot (FARMID, PLOTSIZE, PLOTTYPE, SOILTYPEID, WATERSOURCE)
            VALUES (:farm_id, :plotsize, :plottype, :soil_type_id, :watersource)
        ";

        $stmt_plot = oci_parse($conn, $query_plot);
        oci_bind_by_name($stmt_plot, ':farm_id', $farm_id);
        oci_bind_by_name($stmt_plot, ':plotsize', $plotsize);
        oci_bind_by_name($stmt_plot, ':plottype', $plottype);
        oci_bind_by_name($stmt_plot, ':soil_type_id', $soil_type_id);
        oci_bind_by_name($stmt_plot, ':watersource', $watersource);

        // SINGLE EXECUTION HERE
        if (oci_execute($stmt_plot)) {
            $_SESSION['add_message'] = "Farm added successfully!";
            header("Location: admin_availablefarm.php");
            exit();
        } else {
            $error = oci_error($stmt_plot);
            $_SESSION['add_message'] = "Error adding plot data: " . $error['message'];
        }
    } else {
        $error = oci_error($stmt);
        $_SESSION['add_message'] = "Error adding farm: " . $error['message'];
    }
}

// Handle delete request
if (isset($_GET['delete_farm'])) {
    $farm_id = intval($_GET['delete_farm']); // Sanitize the ID

    if ($farm_id > 0) {
        // Delete query
        $query = "DELETE FROM Farm WHERE FARMID = :farm_id";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':farm_id', $farm_id);

        if (oci_execute($stmt)) {
            oci_commit($conn); // Commit transaction
            $_SESSION['delete_message'] = "Farm deleted successfully.";
        } else {
            $error = oci_error($stmt);
            $_SESSION['delete_message'] = "Error deleting farm: " . $error['message'];
        }
    } else {
        $_SESSION['delete_message'] = "Invalid farm ID.";
    }

    // Redirect to refresh the page
    header("Location: admin_availablefarm.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        function confirmDelete(farmId) {
            // Set the delete animal ID
            document.getElementById('confirmDeleteBtn').onclick = function() {
                window.location.href = "?delete_farm=" + farmId;  // Trigger deletion
            };

            // Show the modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            deleteModal.show();
        }
    </script>
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Available Farms Management</h2>
    <div class="table-wrapper">
	
        <!-- Success/Error Message -->
		<?php if (isset($_SESSION['delete_message'])): ?>
			<div id="successMessage" class="alert alert-success">
				<?= htmlspecialchars($_SESSION['delete_message']) ?>
			</div>
			<?php unset($_SESSION['delete_message']); ?>
		<?php endif; ?>

        <div class="text-end mb-3">
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addFarmModal">Add Farm</button>
        </div>

        <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Name</th>
            <th>Location</th>
            <th>Size</th>
            <th>Water Source</th>
            <th>Plot Size</th>
            <th>Plot Type</th>
            <th>Soil Type</th>
            <th>Soil Condition</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($availableFarms)): ?>
            <?php foreach ($availableFarms as $farm): ?>
                <tr>
                    <td><?= htmlspecialchars($farm['FARMNAME']) ?></td>
                    <td><?= htmlspecialchars($farm['FARMLOCATION']) ?></td>
                    <td><?= htmlspecialchars($farm['FARMSIZE']) ?> ha</td>
                    <td><?= htmlspecialchars($farm['WATERSOURCE']) ?></td>
                    <td><?= htmlspecialchars($farm['PLOTSIZE']) ?> ha</td>
                    <td><?= htmlspecialchars($farm['PLOTTYPE']) ?></td>
                    <td><?= htmlspecialchars($farm['SOILNAME']) ?></td>
                    <td><?= htmlspecialchars($farm['SOILCONDITION']) ?></td>
                    <td>
						<!-- Delete Button that triggers the Modal -->
						<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-farmid="<?= htmlspecialchars($farm['FARMID']) ?>">Delete</button>
					</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="text-center">No farms available.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Farm Modal -->
<div class="modal fade" id="addFarmModal" tabindex="-1" aria-labelledby="addFarmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFarmModalLabel">Add New Farm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="admin_availablefarm.php">
                    <div class="mb-3">
                        <label for="farmname" class="form-label">Farm Name</label>
                        <input type="text" class="form-control" id="farmname" name="farmname" required>
                    </div>
                    <div class="mb-3">
                        <label for="farmlocation" class="form-label">Farm Location</label>
                        <input type="text" class="form-control" id="farmlocation" name="farmlocation" required>
                    </div>
                    <div class="mb-3">
                        <label for="farmsize" class="form-label">Farm Size (ha)</label>
                        <input type="number" class="form-control" id="farmsize" name="farmsize" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="watersource" class="form-label">Water Source</label>
                        <input type="text" class="form-control" id="watersource" name="watersource" required>
                    </div>

                    <!-- Plot Size -->
					<div class="mb-3">
						<label for="plotsize" class="form-label">Plot Size (ha)</label>
						<input type="number" class="form-control" id="plotsize" name="plotsize" required>
					</div>

					<!-- Plot Type -->
					<div class="mb-3">
						<label for="plottype" class="form-label">Plot Type</label>
						<input type="text" class="form-control" id="plottype" name="plottype" required>
					</div>

					<!-- Soil Type -->
					<div class="mb-3">
						<label for="soiltype" class="form-label">Soil Type</label>
						<select name="soiltype" class="form-select">
							<option value="Loamy">Loamy</option>
							<option value="Sandy">Sandy</option>
							<option value="Clay">Clay</option>
						</select>
					</div>

					<!-- Soil Condition -->
					<div class="mb-3">
						<label for="soilcondition" class="form-label">Soil Condition</label>
						<select class="form-control" id="soilcondition" name="soilcondition" required>
							<option value="Good">Good</option>
							<option value="Moderate">Moderate</option>
							<option value="Poor">Poor</option>
						</select>
					</div>


                    <button type="submit" class="btn btn-primary">Add Farm</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this farm?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript to set delete action dynamically
    document.addEventListener("DOMContentLoaded", function () {
        const deleteModal = document.getElementById('deleteModal');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const farmId = button.getAttribute('data-farmid'); // Extract farm ID

            // Update the delete link with the farm ID
            confirmDeleteBtn.setAttribute('href', 'admin_availablefarm.php?delete_farm=' + farmId);
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
