<?php

include('./conn/conn.php');

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
          JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID";

$stmt = oci_parse($conn, $query);
//oci_bind_by_name($stmt, ':user_id', $user_id);
oci_execute($stmt);

// Fetching the results
$animalProduces = [];
while (($row = oci_fetch_assoc($stmt)) != false) {
    $animalProduces[] = $row;
}

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
    }
}

// Handle delete animal produce
if (isset($_GET['delete_animal_produce'])) {
    $animal_produce_id = $_GET['delete_animal_produce'];

    $deleteQuery = "DELETE FROM AnimalProduce WHERE ANIMALPRODUCEID = :animal_produce_id";
    $stmt = oci_parse($conn, $deleteQuery);
    oci_bind_by_name($stmt, ':animal_produce_id', $animal_produce_id);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['delete_message'] = "Animal produce deleted successfully.";
        $_SESSION['delete_message_class'] = "alert-success";
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
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Admin Animal Produce Management</h2>

    <!-- Display Success or Error Message -->
    <?php
    if (isset($_SESSION['delete_message'])) {
        echo '<div id="successMessage" class="alert ' . $_SESSION['delete_message_class'] . '">' . $_SESSION['delete_message'] . '</div>';
        unset($_SESSION['delete_message']);  // Clear message after display
    }
    ?>

    <!-- Add Animal Produce Button -->
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAnimalProduceModal">Add Animal Produce</button>
    </div>

    <!-- Animal Produce Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Animal ID</th>
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
                    <!-- Select Animal ID -->
                    <div class="mb-3">
                        <label for="animal_id" class="form-label">Select Animal</label>
                        <select class="form-select" id="animal_id" name="animal_id" required>
                            <?php
                            // Fetch all animals from the database
                            $query = "SELECT AnimalID, AnimalTypeName FROM AnimalType";
                            $stmt = oci_parse($conn, $query);
                            oci_execute($stmt);
                            while ($animal = oci_fetch_assoc($stmt)) {
                                echo "<option value='".htmlspecialchars($animal['AnimalID'])."'>".htmlspecialchars($animal['AnimalTypeName'])."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>

                    <!-- Produce Date -->
                    <div class="mb-3">
                        <label for="produce_date" class="form-label">Produce Date</label>
                        <input type="date" class="form-control" id="produce_date" name="produce_date" required>
                    </div>

                    <!-- Storage Location -->
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

<!-- Delete Confirmation Modal -->
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

<!-- Bootstrap JS -->
<script src="script.js"></script>
<script src="bootstrap.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
