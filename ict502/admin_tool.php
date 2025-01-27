<?php
session_start();

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


// Handle CRUD operations (Approve/Reject/Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_request'])) {
        $requestId = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
        $approvalDate = filter_input(INPUT_POST, 'approval_date', FILTER_SANITIZE_STRING);

        if ($requestId && $approvalDate) {
            $updateQuery = "UPDATE farmingSys.ToolBorrowRequest 
                            SET Status = 'Approved', ApprovalDate = TO_DATE(:approval_date, 'YYYY-MM-DD') 
                            WHERE ToolBorrowRequestID = :request_id AND Status = 'Pending';";
            $stmt = oci_parse($conn, $updateQuery);
            oci_bind_by_name($stmt, ':approval_date', $approvalDate);
            oci_bind_by_name($stmt, ':request_id', $requestId);

            if (oci_execute($stmt)) {
                oci_commit($conn);
                $_SESSION['message'] = "Request approved successfully.";
            } else {
                $_SESSION['message'] = "Error approving request.";
            }
        }
    }

    if (isset($_POST['reject_request'])) {
        $requestId = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);

        if ($requestId) {
            $updateQuery = "UPDATE farmingSys.ToolBorrowRequest 
                            SET Status = 'Rejected' 
                            WHERE ToolBorrowRequestID = :request_id AND Status = 'Pending';";
            $stmt = oci_parse($conn, $updateQuery);
            oci_bind_by_name($stmt, ':request_id', $requestId);

            if (oci_execute($stmt)) {
                oci_commit($conn);
                $_SESSION['message'] = "Request rejected successfully.";
            } else {
                $_SESSION['message'] = "Error rejecting request.";
            }
        }
    }
}

// Handle delete tool request
if (isset($_GET['delete_tool'])) {
    $tool_id = filter_input(INPUT_GET, 'delete_tool', FILTER_SANITIZE_NUMBER_INT);

    if ($tool_id) {
        $query = "DELETE FROM tools WHERE tool_id = :tool_id";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':tool_id', $tool_id);

        if (oci_execute($stmt)) {
            oci_commit($conn);
            $_SESSION['message'] = "Tool deleted successfully.";
        } else {
            $error = oci_error($stmt);
            $_SESSION['message'] = "Error deleting tool: " . $error['message'];
        }
    } else {
        $_SESSION['message'] = "Invalid tool ID.";
    }

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
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style3.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" crossorigin="anonymous" />
</head>
<body class="bg-content">
    <main class="dashboard d-flex">
        <?php include "admin_sidebar.php"; ?>
        <div class="container-fluid px">
            <?php include "header.php"; ?>

            <div class="container my-4">
                <h2 class="text-center">Admin Tool Management</h2>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success" id="message">
                        <?= htmlspecialchars($_SESSION['message']) ?>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="text-end mb-3">
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
                                    <td><?= $tool['APPROVAL_DATE'] ? htmlspecialchars($tool['APPROVAL_DATE']) : 'Pending' ?></td>
                                    <td class="text-<?= $tool['STATUS'] == 'Approved' ? 'success' : ($tool['STATUS'] == 'Rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($tool['STATUS']) ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editToolModal"
                                                data-toolid="<?= htmlspecialchars($tool['TOOL_ID']) ?>"
                                                data-toolname="<?= htmlspecialchars($tool['TOOL_NAME']) ?>"
                                                data-tooltype="<?= htmlspecialchars($tool['TOOL_TYPE']) ?>">Edit</button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                data-toolid="<?= htmlspecialchars($tool['TOOL_ID']) ?>">Delete</button>
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
                            <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
                        </div>
                    </div>
                </div>
            </div>

            <script src="bootstrap.bundle.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const deleteModal = document.getElementById('deleteModal');
                    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

                    deleteModal.addEventListener('show.bs.modal', function (event) {
                        const button = event.relatedTarget;
                        const toolId = button.getAttribute('data-toolid');
                        confirmDeleteBtn.setAttribute('href', 'admin_tool.php?delete_tool=' + toolId);
                    });
                });
            </script>
        </div>
    </main>
</body>
</html>
