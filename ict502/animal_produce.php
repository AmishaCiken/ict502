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

    $query = "INSERT INTO AnimalProduce (user_id, AnimalID, Quantity, AnimalProduceDate, AnimalStorageLocation, ProduceType) 
              VALUES (:user_id, :animal_id, :quantity, SYSDATE, :storage_location, :produce_type)";
              
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':animal_id', $animal_id);
    oci_bind_by_name($stmt, ':quantity', $quantity);
    oci_bind_by_name($stmt, ':storage_location', $storage_location);
    oci_bind_by_name($stmt, ':produce_type', $produce_type);

    oci_execute($stmt);
    oci_commit($conn);  // Ensure data is committed

    header("Location: animal_produce.php");
    exit();
}

// Handle delete animal produce
if (isset($_GET['delete_animalproduce'])) {
    $animalproduce_id = $_GET['delete_animalproduce'];
    
    $query = "DELETE FROM AnimalProduce WHERE AnimalProduceID = :animalproduce_id AND user_id = :user_id";
    $stmt = oci_parse($conn, $query);
    
    oci_bind_by_name($stmt, ':animalproduce_id', $animalproduce_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_execute($stmt);
    oci_commit($conn);

    header("Location: animal_produce.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Produce Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Animal Produce Management</h2>
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
                        <a href="?delete_animalproduce=<?= $produce['ANIMALPRODUCEID'] ?>" class="btn btn-danger btn-sm">Delete</a>
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
                    <button type="submit" class="btn btn-primary" name="add_animalproduce">Add Animal Produce</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
