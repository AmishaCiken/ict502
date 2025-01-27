<?php
<<<<<<< HEAD
session_start();
include('./conn/conn.php');

// Fetch ALL animal produces (no user filter)
=======

include('./conn/conn.php');

// Fetch the user's animal produces from the database
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
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
<<<<<<< HEAD
oci_execute($stmt); // No binding needed
=======
//oci_bind_by_name($stmt, ':user_id', $user_id);
oci_execute($stmt);
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a

$animalProduces = [];
while (($row = oci_fetch_assoc($stmt)) != false) {
    $animalProduces[] = $row;
}

<<<<<<< HEAD
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
=======
// Handle add animal produce (insert into the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_animal_produce'])) {
    $animal_id = $_POST['animal_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $produce_date = $_POST['produce_date'] ?? null;
    $storage_location = $_POST['storage_location'] ?? null;
    $produce_type = $_POST['produce_type'] ?? null;

    if ($animal_id && $quantity && $produce_date && $storage_location && $produce_type) {
        $query = "INSERT INTO AnimalProduce (ANIMALID, QUANTITY, ANIMALPRODUCEDATE, ANIMALSTORAGELOCATION, PRODUCETYPE, USER_ID) 
                  VALUES (:animal_id, :quantity, TO_DATE(:produce_date, 'DD-MON-YY'), :storage_location, :produce_type, :user_id)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':animal_id', $animal_id);
        oci_bind_by_name($stmt, ':quantity', $quantity);
        oci_bind_by_name($stmt, ':produce_date', $produce_date);
        oci_bind_by_name($stmt, ':storage_location', $storage_location);
        oci_bind_by_name($stmt, ':produce_type', $produce_type);
        oci_bind_by_name($stmt, ':user_id', $user_id);

        if (oci_execute($stmt)) {
            oci_commit($conn);
            $_SESSION['delete_message'] = "Animal produce added successfully.";
            $_SESSION['delete_message_class'] = "alert-success";
            header("Location: admin_animalproduce.php");
            exit();
        } else {
            $e = oci_error($stmt);
            $_SESSION['delete_message'] = "Error adding animal produce: " . htmlspecialchars($e['message']);
            $_SESSION['delete_message_class'] = "alert-danger";
        }
    } else {
        $_SESSION['delete_message'] = "All fields are required.";
        $_SESSION['delete_message_class'] = "alert-danger";
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
    }
}

// Handle delete animal produce
if (isset($_GET['delete_animal_produce'])) {
    $animal_produce_id = $_GET['delete_animal_produce'];

<<<<<<< HEAD
    // Corrected query (no user_id check)
    $query = "DELETE FROM AnimalProduce WHERE AnimalProduceID = :animalproduce_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animalproduce_id', $animalproduce_id);
=======
    $deleteQuery = "DELETE FROM AnimalProduce WHERE ANIMALPRODUCEID = :animal_produce_id";
    $stmt = oci_parse($conn, $deleteQuery);
    oci_bind_by_name($stmt, ':animal_produce_id', $animal_produce_id);
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Animal produce deleted successfully.";
<<<<<<< HEAD
        header("Location: admin_animalproduce.php");
        exit();
=======
        $_SESSION['delete_message_class'] = "alert-success";
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
    } else {
        $e = oci_error($stmt);
        $_SESSION['delete_message'] = "Error deleting animal produce: " . htmlspecialchars($e['message']);
        $_SESSION['delete_message_class'] = "alert-danger";
    }

    // Redirect back after deletion
    header("Location: admin_animalproduce.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Animal Produce Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
=======
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
        function confirmDelete(animalProduceId) {
            // Set the delete animal produce ID
            document.getElementById('confirmDeleteBtn').onclick = function() {
                window.location.href = "?delete_animal_produce=" + animalProduceId;  // Trigger deletion
            };

            // Show the modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            deleteModal.show();
        }
    </script>
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Admin Animal Produce Management</h2>

<<<<<<< HEAD
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
=======
    <!-- Display Success or Error Message -->
    <?php
    if (isset($_SESSION['delete_message'])) {
        echo '<div id="successMessage" class="alert ' . $_SESSION['delete_message_class'] . '">' . $_SESSION['delete_message'] . '</div>';
        unset($_SESSION['delete_message']);  // Clear message after display
    }
    ?>

    <!-- Add Animal Produce Button -->
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAnimalProduceModal">Add Animal Produce</button>
    </div>

<<<<<<< HEAD
    <!-- Table (unchanged) -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>User ID</th>  
                <th>Animal ID</th>
                <th>Animal Type</th>
=======
    <!-- Animal Produce Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Animal ID</th>
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
                <th>Quantity</th>
                <th>Produce Date</th>
                <th>Storage Location</th>
                <th>Produce Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
<<<<<<< HEAD
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
                    <!-- Delete button with confirmation -->
                    <a href="javascript:void(0);" onclick="confirmDelete(<?= htmlspecialchars($produce['ANIMALPRODUCEID']) ?>)" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" class="text-center">No animal produce records found.</td>
        </tr>
    <?php endif; ?>
</tbody>

    
=======
            <?php if (!empty($animalProduces)): ?>
                <?php foreach ($animalProduces as $produce): ?>
                    <tr>
                        <td><?= htmlspecialchars($produce['ANIMALID']) ?></td>
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
                    <td colspan="6" class="text-center">No animal produce found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
    </table>
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
<<<<<<< HEAD
                    <!-- Animal Selection -->
=======
                    <!-- Select Animal ID -->
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
                    <div class="mb-3">
                        <label for="animal_id" class="form-label">Select Animal</label>
                        <select class="form-select" id="animal_id" name="animal_id" required>
                            <?php
<<<<<<< HEAD
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
=======
                            // Fetch all animals from the database
                            $query = "SELECT AnimalID, AnimalTypeName FROM AnimalType";
                            $stmt = oci_parse($conn, $query);
                            oci_execute($stmt);
                            while ($animal = oci_fetch_assoc($stmt)) {
                                echo "<option value='".htmlspecialchars($animal['AnimalID'])."'>".htmlspecialchars($animal['AnimalTypeName'])."</option>";
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
                            }
                            ?>
                        </select>
                    </div>

<<<<<<< HEAD
                    <!-- Other Fields -->
                    <div class="mb-3">
                        <label for="produce_type" class="form-label">Produce Type</label>
                        <input type="text" class="form-control" id="produce_type" name="produce_type" required>
                    </div>

=======
                    <!-- Quantity -->
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>

<<<<<<< HEAD
=======
                    <!-- Produce Date -->
                    <div class="mb-3">
                        <label for="produce_date" class="form-label">Produce Date</label>
                        <input type="date" class="form-control" id="produce_date" name="produce_date" required>
                    </div>

                    <!-- Storage Location -->
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
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
<<<<<<< HEAD
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function confirmDelete(animalproduce_id) {
        if (confirm("Are you sure you want to delete this animal produce record?")) {
            window.location.href = "admin_animalproduce.php?delete_animalproduce=" + animalproduce_id;
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
=======

<!-- Bootstrap JS -->
<script src="script.js"></script>
<script src="bootstrap.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
</body>
</html>