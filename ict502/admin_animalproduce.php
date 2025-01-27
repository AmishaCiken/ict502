<?php
session_start();
include('./conn/conn.php');

// Fetch ALL animal produces (no user filter)
$query = "SELECT AnimalProduce.AnimalProduceID, 
                 Animal.AnimalID, 
                 AnimalType.AnimalTypeName, 
                 AnimalProduce.Quantity, 
                 AnimalProduce.AnimalProduceDate, 
                 AnimalProduce.AnimalStorageLocation, 
                 AnimalProduce.ProduceType,
                 AnimalProduce.user_id
          FROM AnimalProduce 
          JOIN Animal ON AnimalProduce.AnimalID = Animal.AnimalID
          JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID";

$stmt = oci_parse($conn, $query);
oci_execute($stmt); // No binding needed

$animalProduces = [];
while (($row = oci_fetch_assoc($stmt)) != false) {
    $animalProduces[] = $row;
}

// Handle add animal produce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_animalproduce'])) {
    $animal_id = $_POST['animal_id'];
    $quantity = $_POST['quantity'];
    $storage_location = $_POST['storage_location'];
    $produce_type = $_POST['produce_type'];

    // Fetch user_id from Animal table using AnimalID
    $query = "SELECT USER_ID FROM Animal WHERE AnimalID = :animal_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animal_id', $animal_id);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);

    if (!$row || !isset($row['USER_ID'])) {
        $_SESSION['error_message'] = "Invalid animal selected.";
        header("Location: admin_animalproduce.php");
        exit();
    }

    $user_id = $row['USER_ID']; // Use the user_id from the selected animal

    // Generate new AnimalProduceID using sequence
    $query = "SELECT AnimalProduceSeq.NEXTVAL AS new_id FROM dual";
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    $new_animalproduce_id = $row['NEW_ID'];

    // Insert into AnimalProduce
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
        oci_commit($conn);
        $_SESSION['success_message'] = "Animal produce added successfully.";
        header("Location: admin_animalproduce.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error adding animal produce.";
    }
}

// Handle delete animal produce - CORRECTED VERSION
if (isset($_GET['delete_animal_produce'])) {
    $animal_produce_id = $_GET['delete_animal_produce']; // Correct variable name

    $query = "DELETE FROM AnimalProduce WHERE AnimalProduceID = :animalproduce_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animalproduce_id', $animal_produce_id); // Correct variable binding

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Animal produce deleted successfully.";
    } else {
        $e = oci_error($stmt);
        $_SESSION['delete_message'] = "Error deleting animal produce: " . htmlspecialchars($e['message']);
    }
    header("Location: admin_animalproduce.php");
    exit();
}

// Handle edit animal produce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_animalproduce'])) {
    $animalproduce_id = $_POST['animalproduce_id'];
    $animal_id = $_POST['animal_id'];
    $quantity = $_POST['quantity'];
    $storage_location = $_POST['storage_location'];
    $produce_type = $_POST['produce_type'];

    // Get user_id from selected animal
    $query = "SELECT USER_ID FROM Animal WHERE AnimalID = :animal_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animal_id', $animal_id);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);

    if (!$row || !isset($row['USER_ID'])) {
        $_SESSION['error_message'] = "Invalid animal selected.";
        header("Location: admin_animalproduce.php");
        exit();
    }
    $user_id = $row['USER_ID'];

    $query = "UPDATE AnimalProduce SET 
                AnimalID = :animal_id,
                user_id = :user_id,
                Quantity = :quantity,
                AnimalStorageLocation = :storage_location,
                ProduceType = :produce_type
              WHERE AnimalProduceID = :animalproduce_id";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animalproduce_id', $animalproduce_id);
    oci_bind_by_name($stmt, ':animal_id', $animal_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':quantity', $quantity);
    oci_bind_by_name($stmt, ':storage_location', $storage_location);
    oci_bind_by_name($stmt, ':produce_type', $produce_type);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['success_message'] = "Animal produce updated successfully.";
    } else {
        $e = oci_error($stmt);
        $_SESSION['error_message'] = "Error updating produce: " . htmlspecialchars($e['message']);
    }
    header("Location: admin_animalproduce.php");
    exit();
}

// Fetch produce data for editing
$editProduce = null;
if (isset($_GET['edit_id'])) {
    $query = "SELECT * FROM AnimalProduce WHERE AnimalProduceID = :id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':id', $_GET['edit_id']);
    oci_execute($stmt);
    $editProduce = oci_fetch_assoc($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Produce Management</title>
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style3.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css">
</head>
<body class="bg-content">
    <main class="dashboard d-flex">
        <?php include "admin_sidebar.php"; ?>
        <div class="container-fluid px">
            <?php include "header.php"; ?>
<div class="container my-4">
    <h2 class="text-center">Admin Animal Produce Management</h2>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Add Button -->
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAnimalProduceModal">Add Animal Produce</button>
    </div>

    <!-- Table (unchanged) -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>User ID</th>  
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
                        <td><?= htmlspecialchars($produce['USER_ID']) // Ensure this or change to 'user_id' if case mismatch ?></td>  
                        <td><?= htmlspecialchars($produce['ANIMALID']) ?></td>
                        <td><?= htmlspecialchars($produce['ANIMALTYPENAME']) ?></td>
                        <td><?= htmlspecialchars($produce['QUANTITY']) ?></td>
                        <td><?= htmlspecialchars($produce['ANIMALPRODUCEDATE']) ?></td>
                        <td><?= htmlspecialchars($produce['ANIMALSTORAGELOCATION']) ?></td>
                        <td><?= htmlspecialchars($produce['PRODUCETYPE']) ?></td>
                        <td>
                            <a href="?edit_id=<?= htmlspecialchars($produce['ANIMALPRODUCEID']) ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="javascript:void(0);" onclick="confirmDelete(<?= htmlspecialchars($produce['ANIMALPRODUCEID']) ?>)" 
                            class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No animal produce records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!--  edit Animal Produce Modal -->
<div class="modal fade" id="editAnimalProduceModal" tabindex="-1" aria-labelledby="editAnimalProduceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Animal Produce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="animalproduce_id" value="<?= htmlspecialchars($editProduce['ANIMALPRODUCEID'] ?? '') ?>">
                    <!-- Add hidden input for animal_id -->
                    <input type="hidden" name="animal_id" value="<?= htmlspecialchars($editProduce['ANIMALID'] ?? '') ?>">

                    <!-- Animal Selection (disabled for display only) -->
                    <div class="mb-3">
                        <label class="form-label">Animal</label>
                        <select class="form-select" disabled>
                            <?php
                            $queryAnimals = "SELECT Animal.AnimalID, AnimalType.AnimalTypeName, Animal.USER_ID 
                                            FROM Animal 
                                            JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID";
                            $stmtAnimals = oci_parse($conn, $queryAnimals);
                            oci_execute($stmtAnimals);
                            while (($animal = oci_fetch_assoc($stmtAnimals)) != false) {
                                $selected = ($editProduce && $animal['ANIMALID'] == $editProduce['ANIMALID']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($animal['ANIMALID']) . "' $selected>"
                                . htmlspecialchars($animal['USER_ID']) . " - " 
                                . htmlspecialchars($animal['ANIMALTYPENAME']) . " ("
                                . htmlspecialchars($animal['ANIMALID']) . ")</option>";
                            }
                            ?>
                        </select>
                    </div>


                    <!-- Other Fields -->
                    <div class="mb-3">
                        <label for="edit_produce_type" class="form-label">Produce Type</label>
                        <input type="text" class="form-control" id="edit_produce_type" name="produce_type" 
                               value="<?= htmlspecialchars($editProduce['PRODUCETYPE'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="edit_quantity" name="quantity" 
                               value="<?= htmlspecialchars($editProduce['QUANTITY'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_storage_location" class="form-label">Storage Location</label>
                        <input type="text" class="form-control" id="edit_storage_location" name="storage_location" 
                               value="<?= htmlspecialchars($editProduce['ANIMALSTORAGELOCATION'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_animalproduce" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Animal Produce Modal -->
<div class="modal fade" id="addAnimalProduceModal" tabindex="-1" aria-labelledby="addAnimalProduceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Add Animal Produce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Animal Selection -->
                    <div class="mb-3">
                        <label for="animal_id" class="form-label">Select Animal</label>
                        <select class="form-select" id="animal_id" name="animal_id" required>
                            <?php
                            $queryAnimals = "SELECT Animal.AnimalID, AnimalType.AnimalTypeName, Animal.USER_ID 
                                             FROM Animal 
                                             JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID";
                            $stmtAnimals = oci_parse($conn, $queryAnimals);
                            oci_execute($stmtAnimals);
                            while (($animal = oci_fetch_assoc($stmtAnimals)) != false) {
                                echo "<option value='" . htmlspecialchars($animal['ANIMALID']) . "' data-user-id='" . htmlspecialchars($animal['USER_ID']) . "'>"
                                   . htmlspecialchars($animal['USER_ID']) . " - " 
                                   . htmlspecialchars($animal['ANIMALTYPENAME']) . " ("
                                   . htmlspecialchars($animal['ANIMALID']) . ")</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Other Fields -->
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

                    <!-- Produce Type -->
                    <div class="mb-3">
                        <label for="produce_type" class="form-label">Produce Type</label>
                        <input type="text" class="form-control" id="produce_type" name="produce_type" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_animal_produce" class="btn btn-primary">Add Produce</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal (unchanged) -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this animal produce record?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // CORRECTED JavaScript confirmDelete function
function confirmDelete(animalproduce_id) {
    if (confirm("Are you sure you want to delete this animal produce record?")) {
        // Correct parameter name with underscore
        window.location.href = "admin_animalproduce.php?delete_animal_produce=" + animalproduce_id;
    }
}
</script>

<script>
    // Auto-hide success message after 3 seconds
    window.onload = function() {
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) alert.style.display = 'none';
        }, 3000);a
    };
</script>

<!-- Add this script to auto-show the edit modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_GET['edit_id']) && $editProduce): ?>
    new bootstrap.Modal(document.getElementById('editAnimalProduceModal')).show();
    <?php endif; ?>
});
</script>

<!-- Bootstrap JS -->
<script src="script.js"></script>
<script src="bootstrap.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>