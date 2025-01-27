<?php
session_start();

include('./conn/conn.php');

$query = "SELECT 
            t.tool_id AS TOOL_ID,
            t.tool_name AS TOOL_NAME,
            t.tool_type AS TOOL_TYPE,
            TO_CHAR(t.request_date, 'YYYY-MM-DD') AS REQUEST_DATE,
            TO_CHAR(t.approval_date, 'YYYY-MM-DD') AS APPROVAL_DATE,
            t.status AS STATUS,
            u.first_name AS FIRST_NAME,
            u.last_name AS LAST_NAME,
            u.phone_number AS PHONE_NUMBER,
            u.email_address AS EMAIL_ADDRESS
          FROM tools t
          LEFT JOIN tbl_user u ON t.user_id = u.tbl_user_id";

$stmt = oci_parse($conn, $query);

if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    die("<div class='alert alert-danger'>Database error: " . htmlspecialchars($e['message']) . "</div>");
}

$tools = [];
while ($row = oci_fetch_assoc($stmt)) {
    $tools[] = $row;
}

// Handle CRUD operations (Approve/Reject/Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_request'])) {
        $requestId = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
        
        if ($requestId) {
            // Get current date in YYYY-MM-DD format
            $approvalDate = date('Y-m-d');
            
            $updateQuery = "UPDATE tools 
                            SET status = 'Approved', 
                                approval_date = TO_DATE(:approval_date, 'YYYY-MM-DD') 
                            WHERE tool_id = :request_id
                            AND status = 'Pending'";
            
            $stmt = oci_parse($conn, $updateQuery);
            oci_bind_by_name($stmt, ':approval_date', $approvalDate);
            oci_bind_by_name($stmt, ':request_id', $requestId);

            if (oci_execute($stmt)) {
                oci_commit($conn);
                $_SESSION['message'] = "Request approved successfully. Approval date: $approvalDate";
            } else {
                $_SESSION['message'] = "Error approving request.";
            }
            
            header("Location: admin_tool.php");
            exit();
        }
    }

    if (isset($_POST['reject_request'])) {
        $requestId = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);

        if ($requestId) {
            $updateQuery = "UPDATE tools 
                            SET status = 'Rejected', 
                                approval_date = NULL 
                            WHERE tool_id = :request_id";
            
            $stmt = oci_parse($conn, $updateQuery);
            oci_bind_by_name($stmt, ':request_id', $requestId);

            if (oci_execute($stmt)) {
                oci_commit($conn);
                $_SESSION['message'] = "Request rejected successfully.";
            } else {
                $_SESSION['message'] = "Error rejecting request.";
            }
            
            header("Location: admin_tool.php");
            exit();
        }
    }
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
                                        <?php if ($tool['STATUS'] == 'Pending'): ?>
                                            <form method="POST" style="display: inline-block;">
                                                <input type="hidden" name="request_id" value="<?= $tool['TOOL_ID'] ?>">
                                                <button type="submit" name="approve_request" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form method="POST" style="display: inline-block;">
                                                <input type="hidden" name="request_id" value="<?= $tool['TOOL_ID'] ?>">
                                                <button type="submit" name="reject_request" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">No actions available</span>
                                        <?php endif; ?>
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
