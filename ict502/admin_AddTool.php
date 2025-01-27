<?php
session_start();

include('./conn/conn.php');  // Ensure the database connection is included

// Handle form submission to add a new tool
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $tool_name = $_POST['tool_name'];
    $tool_type = $_POST['tool_type'];

    // Prepare the insert query
    $query = "INSERT INTO tools (tool_name, tool_type, user_id) VALUES (:tool_name, :tool_type, :user_id)";
    
    // Prepare and execute the query
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":tool_name", $tool_name);
    oci_bind_by_name($stmt, ":tool_type", $tool_type);
    oci_bind_by_name($stmt, ":user_id", $user_id);
    
    if (oci_execute($stmt)) {
        oci_commit($conn);  // Commit the transaction
        $success_message = "Tool added successfully!";
    } else {
        $error = oci_error($stmt);
        $error_message = "Error adding tool: " . $error['message'];
    }
}

// Fetch the tools from the database
$query = "SELECT tool_id, tool_name, tool_type FROM tools";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

// Fetching the results
$tools = [];
while ($row = oci_fetch_assoc($stmt)) {
    $tools[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
<div class="container my-4">
    <h2 class="text-center">Admin Add Tool</h2>

    <!-- Display Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div id="successMessage" class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- Form to add a new tool -->
    <form action="admin_AddTool.php" method="POST">
        <div class="mb-3">
            <label for="tool_name" class="form-label">Tool Name</label>
            <input type="text" class="form-control" id="tool_name" name="tool_name" required>
        </div>
        <div class="mb-3">
            <label for="tool_type" class="form-label">Tool Type</label>
            <input type="text" class="form-control" id="tool_type" name="tool_type" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Tool</button>
    </form>

    <hr>

    <!-- Display Existing Tools in a Table -->
    <h3>Existing Tools</h3>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Tool ID</th>
            <th>Tool Name</th>
            <th>Tool Type</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($tools)): ?>
            <?php foreach ($tools as $tool): ?>
                <tr>
                    <td><?= htmlspecialchars($tool['TOOL_ID']) ?></td>
                    <td><?= htmlspecialchars($tool['TOOL_NAME']) ?></td>
                    <td><?= htmlspecialchars($tool['TOOL_TYPE']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" class="text-center">No tools found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Back Button Below the Table -->
    <a href="admin_tool.php" class="btn btn-secondary">Back to Tool Management</a>
</div>
<script src="script.js"></script>
<script src="bootstrap.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
