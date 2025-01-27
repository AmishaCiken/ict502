<?php
session_start(); // Start the session
include('conn/conn.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["USERNAME"]) && isset($_POST["PASSWORD"])) {
        $USERNAME = $_POST["USERNAME"];
        $PASSWORD = $_POST["PASSWORD"];

        // Prepare the SELECT query using the TBL_USER table
        $sql = "SELECT * FROM ADMIN WHERE USERNAME = :USERNAME AND PASSWORD = :PASSWORD";
        $stmt = oci_parse($conn, $sql);

        // Bind parameters
        oci_bind_by_name($stmt, ":USERNAME", $USERNAME);
        oci_bind_by_name($stmt, ":PASSWORD", $PASSWORD);

        // Execute the query
        if (oci_execute($stmt)) {
            $row = oci_fetch_assoc($stmt);
            if ($row) { // If a matching row is found
                $_SESSION['USERNAME'] = $USERNAME; // Set session variable
                header("Location: admin_availablefarm.php"); // Redirect to dashboard
                exit; // Stop script execution
            } else {
                echo "<script>alert('You have entered a wrong username or password!'); window.location='admin_login.php';</script>";
            }
        } else {
            $e = oci_error($stmt);
            echo "<script>alert('Database error occurred. Please try again later.'); console.log(" . htmlentities($e['message'], ENT_QUOTES) . ");</script>";
        }
        oci_free_statement($stmt);
    } else {
        echo "<script>alert('Please fill in both email and password fields.'); window.location='admin_login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Home Page</title>
    <link rel="stylesheet" href="style1.css">
</head>

<body>
    <div class="main">
        <div id="navbar"></div>
        <div class="content">
            <h1><span>Community Farming <br> System (CFS)</span></h1>
            <h2><br>Farming Form Digitalization</h2>
            <p class="par">A system where a group of people work together to grow crops or raise animals on shared land. <br>
                Everyone contributes by helping with tasks like planting, watering, and harvesting. <br>
                The food produced is usually shared among the group or sold to benefit the community. <br>
                It’s a way to work as a team, use resources wisely, and support everyone involved.</p>

            <div class="form">
                <h2>Farmer Login</h2>
                <form method="post" action="admin_login.php">
                    <input type="text" name="USERNAME" placeholder="Enter Username Here" required>
                    <input type="password" name="PASSWORD" placeholder="Enter Password Here" required>
                    <button class="btnn" type="submit">Login</button>
                </form>
                
                <div class="button">
                    <button class="btnn"><a href="index.php">Back</a></button>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Load navbar dynamically -->
    <script>
        fetch('navbar.php')
            .then(response => response.text())
            .then(data => document.getElementById('navbar').innerHTML = data)
            .catch(error => console.error('Error loading navbar:', error));
    </script>
</body>

</html>
