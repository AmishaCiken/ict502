<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
include('./conn/conn.php');  // Ensure the database connection is included

// Fetch the tools from the database
$query = "SELECT t.tool_id, t.tool_name, t.tool_type, t.request_date, t.approval_date, t.status, 
                 u.first_name, u.last_name, u.phone_number, u.email_address
          FROM tools t
          JOIN tbl_user u ON t.user_id = u.tbl_user_id";

$stmt = oci_parse($conn, $query);
oci_execute($stmt);

// Fetching the results
$tools = [];
while ($row = oci_fetch_assoc($stmt)) {
    $tools[] = $row;
}

// Handle delete tool request
if (isset($_GET['delete_tool'])) {
    // Ensure the tool_id is a valid integer
    $tool_id = intval($_GET['delete_tool']); // Sanitize the ID

    // Debug: Check if tool_id is valid
    if ($tool_id > 0) {
        // Prepare the delete query
        $query = "DELETE FROM tools WHERE tool_id = :tool_id";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':tool_id', $tool_id);

        // Execute the delete statement
        if (oci_execute($stmt)) {
            oci_commit($conn);
            $_SESSION['delete_message'] = "Tool deleted successfully.";
        } else {
            $error = oci_error($stmt);
            $_SESSION['delete_message'] = "Error deleting tool: " . $error['message'];
        }
    } else {
        $_SESSION['delete_message'] = "Invalid tool ID.";
    }

    // Redirect to refresh the page
    header("Location: admin_tool.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tool Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // JavaScript to hide success message after 3 seconds
        window.onload = function() {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 3000);  // Hide after 3 seconds
            }
        };
    </script>
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Admin Tool Management</h2>
    
    <!-- Display Success/Error Message -->
	<?php
		if (isset($_SESSION['delete_message'])) {
			// Use 'alert-success' for green color
			echo '<div id="successMessage" class="alert alert-success">' . $_SESSION['delete_message'] . '</div>';
			unset($_SESSION['delete_message']);  // Clear message after display
		}
	?>


    <div class="text-end mb-3">
        <!-- Button to redirect to the Add Tool Page -->
        <a href="admin_AddTool.php" class="btn btn-dark">Add Tool</a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Tool ID</th>
            <th>Tool Name</th>
            <th>Tool Type</th>
            <th>Farmer Name</th>
            <th>Phone Number</th>
            <th>Email Address</th>
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
                    <td><?= htmlspecialchars($tool['TOOL_ID']) ?></td>
                    <td><?= htmlspecialchars($tool['TOOL_NAME']) ?></td>
                    <td><?= htmlspecialchars($tool['TOOL_TYPE']) ?></td>
                    <td><?= htmlspecialchars($tool['FIRST_NAME']) ?> <?= htmlspecialchars($tool['LAST_NAME']) ?></td>
                    <td><?= htmlspecialchars($tool['PHONE_NUMBER']) ?></td>
                    <td><?= htmlspecialchars($tool['EMAIL_ADDRESS']) ?></td>
                    <td><?= htmlspecialchars($tool['REQUEST_DATE']) ?></td>
                    <td><?= !empty($tool['APPROVAL_DATE']) ? htmlspecialchars($tool['APPROVAL_DATE']) : 'Pending Approval' ?></td>
                    <td class="text-<?= $tool['STATUS'] == 'approved' ? 'success' : ($tool['STATUS'] == 'rejected' ? 'danger' : 'warning') ?>">
                        <?= ucfirst($tool['STATUS']) ?>
                    </td>
                    <td>
                        <!-- Edit Button -->
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editToolModal"
                                data-toolid="<?= htmlspecialchars($tool['TOOL_ID']) ?>"
                                data-toolname="<?= htmlspecialchars($tool['TOOL_NAME']) ?>"
                                data-tooltype="<?= htmlspecialchars($tool['TOOL_TYPE']) ?>">Edit</button>

						<!-- Delete Button with Modal Trigger -->
						<button class="btn btn-danger btn-sm" data-bs-toggle="modal" 
								data-bs-target="#deleteModal" 
								data-toolid="<?= htmlspecialchars($tool['TOOL_ID']) ?>">Delete</button>
					</td>

						</td>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="text-center">No tools found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this tool?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <!-- The actual delete button -->
                <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // JavaScript to dynamically set the delete action in the modal
    document.addEventListener("DOMContentLoaded", () => {
        const deleteModal = document.getElementById('deleteModal');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const toolId = button.getAttribute('data-toolid'); // Extract info from data-* attributes

            // Update the delete button link with the tool ID
            confirmDeleteBtn.setAttribute('href', 'admin_tool.php?delete_tool=' + toolId);
        });
    });
</script>


</body>
</html>
