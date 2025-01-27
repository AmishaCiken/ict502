<?php
// Start the session and include the database connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// database connection
=======
>>>>>>> f44c63c5e7dcd208369cfa0aea496bf6efc6c67a
include('./conn/conn.php');

// Fetch all animals from the database
$query = "SELECT Animal.AnimalID, Farm.FarmName, AnimalType.AnimalTypeName, Animal.HealthStatus, Animal.USER_ID 
          FROM Animal
          JOIN Farm ON Animal.FarmID = Farm.FarmID
          JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID";

$stmt = oci_parse($conn, $query);

if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo "Error fetching animals: " . htmlspecialchars($e['message']);
    exit();
}

// Store results in an array
$animals = [];
while ($row = oci_fetch_assoc($stmt)) {
    $animals[] = $row;
}

// Handle adding a new animal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_animal'])) {
    $farm_id = $_POST['farm_id'] ?? null;
    $animal_type_id = $_POST['animal_type_id'] ?? null;
    $health_status = $_POST['health_status'] ?? null;
    $user_id = $_POST['user_id'] ?? null;

    if ($farm_id && $animal_type_id && $health_status && $user_id) {
        $query = "INSERT INTO Animal (FarmID, AnimalTypeID, HealthStatus, USER_ID) 
                  VALUES (:farm_id, :animal_type_id, :health_status, :user_id)";

        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':farm_id', $farm_id);
        oci_bind_by_name($stmt, ':animal_type_id', $animal_type_id);
        oci_bind_by_name($stmt, ':health_status', $health_status);
        oci_bind_by_name($stmt, ':user_id', $user_id);

        if (oci_execute($stmt)) {
            oci_commit($conn);
            header("Location: admin_animal.php");
            exit();
        } else {
            $e = oci_error($stmt);
            echo "Error adding animal: " . htmlspecialchars($e['message']);
        }
    } else {
        echo "All fields are required.";
    }
}

// Handle deleting an animal
if (isset($_GET['delete_animal'])) {
    $animal_id = $_GET['delete_animal'];

    // Check if the animal has associated produce
    $checkProduceQuery = "SELECT COUNT(*) FROM AnimalProduce WHERE AnimalID = :animal_id";
    $checkStmt = oci_parse($conn, $checkProduceQuery);
    oci_bind_by_name($checkStmt, ':animal_id', $animal_id);
    oci_execute($checkStmt);
    $row = oci_fetch_assoc($checkStmt);
    $produce_count = $row['COUNT(*)'];

    if ($produce_count > 0) {
        $_SESSION['delete_message'] = "This animal has associated animal produce and cannot be deleted.";
        $_SESSION['delete_message_class'] = "alert-danger";
        header("Location: admin_animal.php");
        exit();
    } else {
        $query = "DELETE FROM Animal WHERE AnimalID = :animal_id";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':animal_id', $animal_id);

        if (oci_execute($stmt)) {
            oci_commit($conn);
            $_SESSION['delete_message'] = "Animal deleted successfully.";
            $_SESSION['delete_message_class'] = "alert-success";
            header("Location: admin_animal.php");
            exit();
        } else {
            $e = oci_error($stmt);
            echo "Error deleting animal: " . htmlspecialchars($e['message']);
        }
    }
}

// Fetch animal details for editing
if (isset($_GET['editAnimalID'])) {
    $editAnimalID = $_GET['editAnimalID'];
    $query = "SELECT * FROM Animal WHERE AnimalID = :animal_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animal_id', $editAnimalID);

    if (oci_execute($stmt)) {
        $animalToEdit = oci_fetch_assoc($stmt);
    } else {
        $e = oci_error($stmt);
        echo "Error fetching animal details: " . htmlspecialchars($e['message']);
        exit();
    }
}

// Handle the form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_animal'])) {
    $animal_id = $_POST['animal_id'];
    $farm_id = $_POST['farm_id'];
    $animal_type_id = $_POST['animal_type_id'];
    $health_status = $_POST['health_status'];
    $user_id = $_POST['user_id'];

    $query = "UPDATE Animal
              SET FarmID = :farm_id, AnimalTypeID = :animal_type_id, HealthStatus = :health_status, USER_ID = :user_id
              WHERE AnimalID = :animal_id";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':farm_id', $farm_id);
    oci_bind_by_name($stmt, ':animal_type_id', $animal_type_id);
    oci_bind_by_name($stmt, ':health_status', $health_status);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':animal_id', $animal_id);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        header("Location: admin_animal.php");
        exit();
    } else {
        $e = oci_error($stmt);
        echo "Error updating animal: " . htmlspecialchars($e['message']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Animal Management</title>
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style3.css">
</head>
<body class="bg-content">
    <main class="dashboard d-flex">
        <!-- Sidebar -->
        <?php include "admin_sidebar.php"; ?>

        <!-- Content -->
        <div class="container my-4">
            <h2 class="text-center">Animal Management</h2>
            
            <!-- Success Message -->
            <?php if (isset($_SESSION['delete_message'])): ?>
                <div class="alert <?= htmlspecialchars($_SESSION['delete_message_class']) ?>" role="alert">
                    <?= htmlspecialchars($_SESSION['delete_message']) ?>
                </div>
                <?php unset($_SESSION['delete_message']); ?>
            <?php endif; ?>

            <!-- Add Animal Button -->
            <div class="text-end mb-3">
                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAnimalModal">Add Animal</button>
            </div>

            <!-- Edit Animal Modal -->
<?php if (isset($animalToEdit)): ?>
<div class="modal fade" id="editAnimalModal" tabindex="-1" aria-labelledby="editAnimalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="admin_animal.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAnimalModalLabel">Edit Animal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="animal_id" value="<?= htmlspecialchars($animalToEdit['ANIMALID']) ?>">
                    <div class="mb-3">
                        <label for="farm_id" class="form-label">Farm ID</label>
                        <input type="text" name="farm_id" id="farm_id" class="form-control"
                               value="<?= htmlspecialchars($animalToEdit['FARMID']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="animal_type_id" class="form-label">Animal Type ID</label>
                        <input type="text" name="animal_type_id" id="animal_type_id" class="form-control"
                               value="<?= htmlspecialchars($animalToEdit['ANIMALTYPEID']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="health_status" class="form-label">Health Status</label>
                        <input type="text" name="health_status" id="health_status" class="form-control"
                               value="<?= htmlspecialchars($animalToEdit['HEALTHSTATUS']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User ID</label>
                        <input type="text" name="user_id" id="user_id" class="form-control"
                               value="<?= htmlspecialchars($animalToEdit['USER_ID']) ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_animal" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Automatically open the edit modal if $animalToEdit is set
    document.addEventListener("DOMContentLoaded", function () {
        new bootstrap.Modal(document.getElementById('editAnimalModal')).show();
    });
</script>
<?php endif; ?>

            <!-- Animal Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User ID</th>
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
                                <td><?= htmlspecialchars($animal['USER_ID']) ?></td>
                                <td><?= htmlspecialchars($animal['FARMNAME']) ?></td>
                                <td><?= htmlspecialchars($animal['ANIMALTYPENAME']) ?></td>
                                <td><?= htmlspecialchars($animal['HEALTHSTATUS']) ?></td>
                                <td>
                                <a href="admin_animal.php?editAnimalID=<?= htmlspecialchars($animal['ANIMALID']) ?>" 
   class="btn btn-primary btn-sm">Edit</a>

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
    </main>
</body>
</html>
