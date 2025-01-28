<?php
session_start();


// database connection
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

// Handle add animal (insert into the database)
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

// Handle delete animal
if (isset($_GET['delete_animal'])) {
    $animal_id = $_GET['delete_animal'];

    // Check if the animal has associated animal produce
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
    $query = "SELECT Animal.*, Farm.FarmName, AnimalType.AnimalTypeName 
              FROM Animal
              JOIN Farm ON Animal.FarmID = Farm.FarmID
              JOIN AnimalType ON Animal.AnimalTypeID = AnimalType.AnimalTypeID
              WHERE Animal.AnimalID = :animal_id";
    
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':animal_id', $editAnimalID);
    oci_execute($stmt);
    $animalToEdit = oci_fetch_assoc($stmt);
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_animal'])) {
    $animal_id = $_POST['animal_id'];
    $health_status = $_POST['health_status'];

    // Update only health status
    $query = "UPDATE Animal SET HealthStatus = :health_status
              WHERE AnimalID = :animal_id";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':health_status', $health_status);
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Management</title>
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style3.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css">

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
<body class="bg-content">
    <main class="dashboard d-flex">
        <?php include "admin_sidebar.php"; ?>
        <div class="container-fluid px">
            <?php include "header.php"; ?>
<div class="container my-4">
    <h2 class="text-center">Admin Animal Management</h2>
    
    <!-- Display Success Message -->
    <?php if (isset($_SESSION['delete_message'])): ?>
        <div id="successMessage" class="alert <?= $_SESSION['delete_message_class'] ?>">
            <?= $_SESSION['delete_message'] ?>
        </div>
        <?php 
        unset($_SESSION['delete_message']);
        unset($_SESSION['delete_message_class']);
        ?>
    <?php endif; ?>

    <!-- Add Animal Button -->
    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAnimalModal">Add Animal</button>
    </div>

    <!-- Animals Table -->
    <table class="table table-bordered table-striped">
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
                        <td><?= htmlspecialchars($animal['USER_ID'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($animal['FARMNAME'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($animal['ANIMALTYPENAME'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($animal['HEALTHSTATUS'] ?? 'N/A') ?></td>
                        <td>
                            <!-- Add Edit Button Here -->
                            <a href="?editAnimalID=<?= htmlspecialchars($animal['ANIMALID']) ?>" 
                               class="btn btn-primary btn-sm">Edit</a>
                            <a href="javascript:void(0);" 
                               onclick="confirmDelete(<?= htmlspecialchars($animal['ANIMALID']) ?>)" 
                               class="btn btn-danger btn-sm">Delete</a>
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
                    <!-- Select User -->
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select User</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <?php
                            // Fetch all users from the database
                            $query = "SELECT tbl_user_id, first_name FROM tbl_user";
                            $stid = oci_parse($conn, $query);
                            oci_execute($stid);
                            while ($user = oci_fetch_assoc($stid)) {
                                echo "<option value='".htmlspecialchars($user['TBL_USER_ID'])."'>".htmlspecialchars($user['TBL_USER_ID'] . '-' . $user['FIRST_NAME'])."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Select Farm -->
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

                    <!-- Select Animal Type -->
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

                    
                    <!-- Health Status -->
                    <div class="mb-3">
                        <label for="health_status" class="form-label">Health Status</label>
                        <select class="form-select" id="health_status" name="health_status" required>
                            <option value="Healthy">Healthy</option>
                            <option value="Sick">Sick</option>
                        </select>
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

        <!-- Edit Animal Modal -->
        <?php if (isset($animalToEdit)): ?>
        <div class="modal fade" id="editAnimalModal" tabindex="-1" aria-labelledby="editAnimalModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="admin_animal.php">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Animal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="animal_id" value="<?= htmlspecialchars($animalToEdit['ANIMALID']) ?>">
                                <div class="mb-3">
                                    <label class="form-label">Farm Name</label>
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($animalToEdit['FARMNAME']) ?>" 
                                           readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Animal Type</label>
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($animalToEdit['ANIMALTYPENAME']) ?>" 
                                           readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Health Status</label>
                                    <select name="health_status" class="form-select" required>
                                        <option value="Healthy" <?= $animalToEdit['HEALTHSTATUS'] == 'Healthy' ? 'selected' : '' ?>>Healthy</option>
                                        <option value="Sick" <?= $animalToEdit['HEALTHSTATUS'] == 'Sick' ? 'selected' : '' ?>>Sick</option>
                                        <option value="Recovering" <?= $animalToEdit['HEALTHSTATUS'] == 'Recovering' ? 'selected' : '' ?>>Recovering</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">User ID</label>
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($animalToEdit['USER_ID']) ?>" 
                                           readonly>
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
<script src="script.js"></script>
    <script src="bootstrap.bundle.js"></script>
<script>
    // Show the edit modal when it exists
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('editAnimalModal')) {
            new bootstrap.Modal(document.getElementById('editAnimalModal')).show();
        }
    });
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>