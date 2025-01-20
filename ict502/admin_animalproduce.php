<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
include('./conn/conn.php');  // Ensure the database connection is included

// Fetch the user's animal produces from the database
$query = "SELECT AnimalProduce.AnimalProduceID, 
                 Animal.AnimalID, 
                 AnimalType.AnimalTypeName, 
                 AnimalProduce.Quantity, 
                 AnimalProduce.AnimalProduceDate, 
                 AnimalProduce.AnimalStorageLocation, 
                 AnimalProduce.ProduceType 
          FROM AnimalProduce 
          JOIN Animal ON AnimalProduce.AnimalID = Animal.AnimalID
          JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID
          WHERE AnimalProduce.user_id = :user_id";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':user_id', $user_id);
oci_execute($stmt);

// Fetching the results
$animalProduces = [];
while (($row = oci_fetch_assoc($stmt)) != false) {
    $animalProduces[] = $row;
}

// Handle add animal produce (inserting into the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_animalproduce'])) {
    $animal_id = $_POST['animal_id'];
    $quantity = $_POST['quantity'];
    $storage_location = $_POST['storage_location'];
    $produce_type = $_POST['produce_type'];

    // Generate a new AnimalProduceID using an Oracle sequence
    $query = "SELECT AnimalProduceSeq.NEXTVAL AS new_id FROM dual";  // Sequence to generate new ID
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    $new_animalproduce_id = $row['NEW_ID'];

    // Insert the data with the generated ID
    $query = "INSERT INTO AnimalProduce (AnimalProduceID, user_id, AnimalID, Quantity, AnimalProduceDate, AnimalStorageLocation, ProduceType)
              VALUES (:animalproduce_id, :user_id, :animal_id, :quantity, SYSDATE, :storage_location, :produce_type)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animalproduce_id', $new_animalproduce_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':animal_id', $animal_id);
    oci_bind_by_name($stmt, ':quantity', $quantity);
    oci_bind_by_name($stmt, ':storage_location', $storage_location);
    oci_bind_by_name($stmt, ':produce_type', $produce_type);

    if (oci_execute($stmt)) {
        oci_commit($conn);  // Ensure data is committed
        $_SESSION['success_message'] = "Animal produce added successfully.";  // Set success message
        header("Location: admin_animalproduce.php");  // Redirect to admin_animalproduce.php to display the updated list
        exit();
    } else {
        $_SESSION['error_message'] = "Error adding animal produce.";  // Set error message
    }
}

// Handle delete animal produce
if (isset($_GET['delete_animalproduce'])) {
    $animalproduce_id = $_GET['delete_animalproduce'];

    $query = "DELETE FROM AnimalProduce WHERE AnimalProduceID = :animalproduce_id AND user_id = :user_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animalproduce_id', $animalproduce_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Animal produce deleted successfully.";  // Set success message
        header("Location: admin_animalproduce.php");  // Redirect to show the success message
        exit();
    } else {
        $e = oci_error($stmt);
        echo "Error deleting animal produce: " . htmlspecialchars($e['message']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Produce Management</title>
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
    <h2 class="text-center">Admin Animal Produce Management</h2>

    <!-- Display Success or Error Message -->
    <?php
    if (isset($_SESSION['delete_message'])) {
        echo '<div id="successMessage" class="alert alert-success">' . $_SESSION['delete_message'] . '</div>';
        unset($_SESSION['delete_message']);  // Clear message after display
    }
    ?>
	
	<!-- Add Animal Button -->
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAnimalProduceModal">Add Animal Produce</button>
    </div>
    
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Animal ID</th>
            <th>Animal Type</th>
            <th>Quantity</th>
            <th>Produce Date</th>
            <th>Storage Location</th>
            <th>Produce Type</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($animalProduces)): ?>
            <?php foreach ($animalProduces as $produce): ?>
                <tr>
                    <td><?= htmlspecialchars($produce['ANIMALID']) ?></td>
                    <td><?= htmlspecialchars($produce['ANIMALTYPENAME']) ?></td>
                    <td><?= htmlspecialchars($produce['QUANTITY']) ?></td>
                    <td><?= htmlspecialchars($produce['ANIMALPRODUCEDATE']) ?></td>
                    <td><?= htmlspecialchars($produce['ANIMALSTORAGELOCATION']) ?></td>
                    <td><?= htmlspecialchars($produce['PRODUCETYPE']) ?></td>
                    <td>
                        <!-- Delete button with confirmation -->
                        <a href="javascript:void(0);" onclick="confirmDelete(<?= htmlspecialchars($produce['ANIMALPRODUCEID']) ?>)" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No animal produce records found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- Add Animal Produce Modal -->
<div class="modal fade" id="addAnimalProduceModal" tabindex="-1" aria-labelledby="addAnimalProduceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAnimalProduceModalLabel">Add Animal Produce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="animal_id" class="form-label">Select Animal</label>
                        <select class="form-select" id="animal_id" name="animal_id" required>
                            <?php
                            // Fetch list of animals with their type from the database
                            $query = "SELECT Animal.AnimalID, AnimalType.AnimalTypeName 
                                      FROM Animal 
                                      JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID 
                                      WHERE Animal.user_id = :user_id";
                            $stmt = oci_parse($conn, $query);
                            oci_bind_by_name($stmt, ':user_id', $user_id);
                            oci_execute($stmt);
                            while (($animal = oci_fetch_assoc($stmt)) != false) {
                                echo "<option value='" . htmlspecialchars($animal['ANIMALID']) . "'>" . htmlspecialchars($animal['ANIMALID']) . " - " . htmlspecialchars($animal['ANIMALTYPENAME']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="produce_type" class="form-label">Produce Type</label>
                        <input type="text" class="form-control" id="produce_type" name="produce_type" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="storage_location" class="form-label">Storage Location</label>
                        <input type="text" class="form-control" id="storage_location" name="storage_location" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_animalproduce" class="btn btn-primary">Add Produce</button>
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
                <p>Are you sure you want to delete this animal produce? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete(animalproduce_id) {
        if (confirm("Are you sure you want to delete this animal produce record?")) {
            window.location.href = "admin_animalproduce.php?delete_animalproduce=" + animalproduce_id;
        }
    }
</script>
</body>
</html>
