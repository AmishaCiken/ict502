<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
include('./conn/conn.php');  // Ensure the database connection is included

// Fetch the user's tools from the database
$query = "SELECT tool_id, tool_name, tool_type, request_date, approval_date, status
          FROM tools
          WHERE user_id = :user_id";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':user_id', $user_id);
oci_execute($stmt);

// Fetching the results
$tools = [];
while ($row = oci_fetch_assoc($stmt)) {
    $tools[] = $row;
}

// Handle add tool request (inserting into the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tool'])) {
    $tool_name = $_POST['tool_name'];
    $tool_type = $_POST['tool_type'];

    $query = "INSERT INTO tools (user_id, tool_name, tool_type, request_date, status)
              VALUES (:user_id, :tool_name, :tool_type, SYSDATE, 'Pending')";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    oci_bind_by_name($stmt, ':tool_name', $tool_name);
    oci_bind_by_name($stmt, ':tool_type', $tool_type);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'New Request added successfully!';
        header("Location: tool.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to add the produce. Please try again.';
    }
}

// Handle delete tool request
if (isset($_GET['delete_tool'])) {
    $tool_id = $_GET['delete_tool'];
    
    $query = "DELETE FROM tools WHERE tool_id = :tool_id AND user_id = :user_id";
    $stmt = oci_parse($conn, $query);
    
    oci_bind_by_name($stmt, ':tool_id', $tool_id);
    oci_bind_by_name($stmt, ':user_id', $user_id);
    
    if (oci_execute($stmt)) {  
        oci_commit($conn);
        $_SESSION['alert_type'] = 'success';
        $_SESSION['message'] = 'Request deleted successfully!';
        header("Location: tool.php"); // Redirect to reload the page
        exit();
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['message'] = 'Failed to delete the produce. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Tool Management</h2>

    <!-- Display success or error message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo ($_SESSION['alert_type'] == 'success') ? 'success' : 'danger'; ?> mt-3" role="alert" id="message">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php unset($_SESSION['message']); ?>
        <?php unset($_SESSION['alert_type']); ?>
    <?php endif; ?>

    <div class="text-end mb-3">
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addToolModal">Request Tool</button>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Tool Name</th>
            <th>Tool Type</th>
            <th>Request Date</th>
            <th>Approval Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($tools)): ?>
            <?php foreach ($tools as $tool): ?>
                <tr>
                    <td><?= htmlspecialchars($tool['TOOL_NAME']) ?></td>
                    <td><?= htmlspecialchars($tool['TOOL_TYPE']) ?></td>
                    <td><?= htmlspecialchars($tool['REQUEST_DATE']) ?></td>
                    <td><?= htmlspecialchars($tool['APPROVAL_DATE'] ?? 'Pending Approval') ?></td>
                    <td><?= htmlspecialchars($tool['STATUS']) ?></td>
                    <td>
                        <a href="?delete_tool=<?= htmlspecialchars($tool['TOOL_ID']) ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No tools found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Tool Modal -->
<div class="modal fade" id="addToolModal" tabindex="-1" aria-labelledby="addToolModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addToolModalLabel">Request Tool</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tool_name" class="form-label">Tool Name</label>
                        <input type="text" class="form-control" id="tool_name" name="tool_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="tool_type" class="form-label">Tool Type</label>
                        <input type="text" class="form-control" id="tool_type" name="tool_type" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="add_tool">Request Tool</button>
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
