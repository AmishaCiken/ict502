<?php

// Ensure the database connection is included
include('./conn/conn.php');  

// Fetch the user's animals from the database
$query = "SELECT Animal.AnimalID, Farm.FarmName, AnimalType.AnimalTypeName, Animal.HealthStatus 
          FROM Animal 
          JOIN Farm ON Animal.FarmID = Farm.FarmID
          JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID
          WHERE Animal.user_id = :user_id";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':user_id', $user_id);
oci_execute($stmt);

// Fetching the results
$animals = [];
while ($row = oci_fetch_assoc($stmt)) {
    $animals[] = $row;
}

// Handle add animal (inserting into the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_animal'])) {
    $farm_id = $_POST['farm_id'];
    $animal_type_id = $_POST['animal_type_id'];
    $health_status = $_POST['health_status'];

    $query = "INSERT INTO Animal (user_id, FarmID, AnimalTypeID, HealthStatus) 
              VALUES (:user_id, :farm_id, :animal_type_id, :health_status)";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':farm_id', $farm_id);
    oci_bind_by_name($stmt, ':animal_type_id', $animal_type_id);
    oci_bind_by_name($stmt, ':health_status', $health_status);

    oci_execute($stmt);
    oci_commit($conn);  // Ensure data is committed

    header("Location: animal.php");
    exit();
}

// Handle delete animal
if (isset($_GET['delete_animal'])) {
    $animal_id = $_GET['delete_animal'];
    
    $query = "DELETE FROM Animal WHERE AnimalID = :animal_id AND user_id = :user_id";
    $stmt = oci_parse($conn, $query);
    
    oci_bind_by_name($stmt, ':animal_id', $animal_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_execute($stmt);
    oci_commit($conn);

    header("Location: animal.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Farm</title>
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style3.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />

<body class="bg-content">
    <main class="dashboard d-flex">
        <!-- Sidebar -->
        <?php include "sidebar.php"; ?>
         <!-- Content Page -->
         <div class="container-fluid px">
            <?php include "header.php"; ?>
            
<div class="container my-4">
    <h2 class="text-center">Animal Management</h2>
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAnimalModal">Add Animal</button>
    </div>
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
                        <a href="?delete_animal=<?= htmlspecialchars($animal['ANIMALID']) ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No animals found.</td>
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
                            // Fetch list of farms from the database
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
                            // Fetch list of animal types from the database
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
                    <button type="submit" class="btn btn-primary" name="add_animal">Add Animal</button>\
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


