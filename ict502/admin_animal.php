<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Include the database connection
include('./conn/conn.php');

// Fetch the user's animals from the database
$query = "SELECT Animal.AnimalID, Farm.FarmName, AnimalType.AnimalTypeName, Animal.HealthStatus 
          FROM Animal 
          JOIN Farm ON Animal.FarmID = Farm.FarmID
          JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID
          WHERE Animal.user_id = :user_id";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':user_id', $user_id);

if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo "Error fetching animals: " . htmlspecialchars($e['message']);
    exit();
}

$animals = [];
while ($row = oci_fetch_assoc($stmt)) {
    $animals[] = $row;
}

// Handle add animal (insert into the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_animal'])) {
    $farm_id = $_POST['farm_id'] ?? null;
    $animal_type_id = $_POST['animal_type_id'] ?? null;
    $health_status = $_POST['health_status'] ?? null;
    $user_id = $_SESSION['user_id'];  // Assuming user is logged in and user_id is set in session.

    // Ensure all fields are filled
    if ($farm_id && $animal_type_id && $health_status && $user_id) {
        // Add animal to the database
        $query = "INSERT INTO Animal (FarmID, AnimalTypeID, HealthStatus, USER_ID) 
                  VALUES (:farm_id, :animal_type_id, :health_status, :user_id)";

        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':farm_id', $farm_id);
        oci_bind_by_name($stmt, ':animal_type_id', $animal_type_id);
        oci_bind_by_name($stmt, ':health_status', $health_status);
        oci_bind_by_name($stmt, ':user_id', $user_id);

        if (oci_execute($stmt)) {
            oci_commit($conn);  // Ensure data is committed to the database
            header("Location: admin_animal.php");  // Redirect after adding the animal
            exit();
        } else {
            $e = oci_error($stmt);
            echo "Error adding animal: " . htmlspecialchars($e['message']);
        }
    } else {
        echo "All fields are required.";
    }
}

// Handle delete animal
if (isset($_GET['delete_animal'])) {
    $animal_id = $_GET['delete_animal'];

    $query = "DELETE FROM Animal WHERE AnimalID = :animal_id AND user_id = :user_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animal_id', $animal_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Animal deleted successfully.";  // Set success message
        header("Location: admin_animal.php");  // Redirect to show the success message
        exit();
    } else {
        $e = oci_error($stmt);
        echo "Error deleting animal: " . htmlspecialchars($e['message']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Management</title>
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
        function confirmDelete(animalId) {
            // Set the delete animal ID
            document.getElementById('confirmDeleteBtn').onclick = function() {
                window.location.href = "?delete_animal=" + animalId;  // Trigger deletion
            };

            // Show the modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            deleteModal.show();
        }
    </script>
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Admin Animal Management</h2>
    
    <!-- Display Success Message -->
    <?php
    if (isset($_SESSION['delete_message'])) {
        echo '<div id="successMessage" class="alert alert-success">' . $_SESSION['delete_message'] . '</div>';
        unset($_SESSION['delete_message']);  // Clear message after display
    }
    ?>

    <!-- Add Animal Button -->
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAnimalModal">Add Animal</button>
    </div>

    <!-- Animals Table -->
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Farm Name</th>
				<th>Animal Type</th>
				<th>Health Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($animals)): ?>
				<?php foreach ($animals as $animal): ?>
					<tr>
						<td><?= htmlspecialchars($animal['FARMNAME'] ?? 'N/A') ?></td>
						<td><?= htmlspecialchars($animal['ANIMALTYPENAME'] ?? 'N/A') ?></td>
						<td><?= htmlspecialchars($animal['HEALTHSTATUS'] ?? 'N/A') ?></td>
						<td>
							<!-- Delete button with confirmation -->
							<a href="javascript:void(0);" onclick="confirmDelete(<?= htmlspecialchars($animal['ANIMALID']) ?>)" class="btn btn-danger btn-sm">Delete</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="4" class="text-center">No animals found.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>


</div>

<!-- Add Animal Modal -->
<div class="modal fade" id="addAnimalModal" tabindex="-1" aria-labelledby="addAnimalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAnimalModalLabel">Add Animal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="farm_id" class="form-label">Select Farm</label>
                        <select class="form-select" id="farm_id" name="farm_id" required>
                            <?php
                            $query = "SELECT * FROM Farm";
                            $stid = oci_parse($conn, $query);
                            oci_execute($stid);
                            while ($farm = oci_fetch_assoc($stid)) {
                                echo "<option value='".htmlspecialchars($farm['FARMID'])."'>".htmlspecialchars($farm['FARMNAME'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="animal_type_id" class="form-label">Select Animal Type</label>
                        <select class="form-select" id="animal_type_id" name="animal_type_id" required>
                            <?php
                            $query = "SELECT * FROM AnimalType";
                            $stid = oci_parse($conn, $query);
                            oci_execute($stid);
                            while ($type = oci_fetch_assoc($stid)) {
                                echo "<option value='".htmlspecialchars($type['ANIMALTYPEID'])."'>".htmlspecialchars($type['ANIMALTYPENAME'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="health_status" class="form-label">Health Status</label>
                        <input type="text" class="form-control" id="health_status" name="health_status" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="add_animal">Add Animal</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Delete Animal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this animal? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
