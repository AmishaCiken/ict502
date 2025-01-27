<?php
session_start(); // Start the session
include('conn/conn.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["EMAIL_ADDRESS"]) && isset($_POST["PASSWORD"])) {
        $EMAIL_ADDRESS = $_POST["EMAIL_ADDRESS"];
        $PASSWORD = $_POST["PASSWORD"];

        // Prepare the SELECT query using the TBL_USER table
        $sql = "SELECT * FROM TBL_USER WHERE EMAIL_ADDRESS = :email_address AND PASSWORD = :PASSWORD";
        $stmt = oci_parse($conn, $sql);

        // Bind parameters
        oci_bind_by_name($stmt, ":EMAIL_ADDRESS", $EMAIL_ADDRESS);
        oci_bind_by_name($stmt, ":PASSWORD", $PASSWORD);

        // Execute the query
        if (oci_execute($stmt)) {
            $row = oci_fetch_assoc($stmt);
            if ($row) { // If a matching row is found
                $_SESSION['EMAIL_ADDRESS'] = $EMAIL_ADDRESS; // Set session variable
                header("Location: available_farm.php"); // Redirect to dashboard
                exit; // Stop script execution
            } else {
                echo "<script>alert('You have entered a wrong username or password!'); window.location='index.php';</script>";
            }
        } else {
            $e = oci_error($stmt);
            echo "<script>alert('Database error occurred. Please try again later.'); console.log(" . htmlentities($e['message'], ENT_QUOTES) . ");</script>";
        }
        oci_free_statement($stmt);
    } else {
        echo "<script>alert('Please fill in both email and password fields.'); window.location='index.php';</script>";
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
                <form method="post" action="index.php">
                    <input type="text" name="EMAIL_ADDRESS" placeholder="Enter Email Here" required>
                    <input type="password" name="PASSWORD" placeholder="Enter Password Here" required>
                    <button class="btnn" type="submit">Login</button>
                </form>
                
                <div class="button">
                    <button class="btnn"><a href="admin_login.php">Admin</a></button>
                </div>
                
                <p class="link">
                    Don't have an account? <br>
                    <a href="signup.php" style="color: #ff7200;">Sign up</a> here.
                </p>
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
