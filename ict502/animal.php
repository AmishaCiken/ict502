<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Ensure the database connection is included
$user_id = $_SESSION['user_id'];
include('./conn/conn.php');  

$query = "SELECT Animal.AnimalID, Farm.FarmName, AnimalType.AnimalTypeName, Animal.HealthStatus 
          FROM Animal 
          JOIN Farm ON Animal.FarmID = Farm.FarmID
          JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID
          WHERE Animal.user_id = :user_id";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':user_id', $user_id);
oci_execute($stmt);

$animals = [];
while ($row = oci_fetch_assoc($stmt)) {
    $animals[] = $row;
}

// Check for message in session
$alert_type = isset($_SESSION['alert_type']) ? $_SESSION['alert_type'] : '';
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';

// Clear message after it is shown
unset($_SESSION['alert_type']);
unset($_SESSION['message']);

// Handle add animal
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

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Animal added successfully!';
        header("Location: animal.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to add the animal. Please try again.';
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
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Animal deleted successfully!';
        header("Location: animal.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to delete the animal. Please try again.';
    }
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
    
    <!-- Display success or error message -->
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo ($alert_type == 'success') ? 'success' : 'danger'; ?> mt-3" role="alert" id="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

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
                    <button type="submit" class="btn btn-primary" name="add_animal">Add Animal</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
