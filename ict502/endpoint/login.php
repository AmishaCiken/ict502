<?php
include ('../conn/conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_address = $_POST['email_address'];
    $password = $_POST['password'];

    // Prepare the query for selecting user based on email address
    $sql = "SELECT password, tbl_user_id FROM tbl_user WHERE Email_Address = :email_address";
    $stmt = oci_parse($conn, $sql); // Ensure OCI8 connection is used

    // Bind parameter
    oci_bind_by_name($stmt, ":email_address", $email_address);

    // Execute query
    oci_execute($stmt);

    // Check if user exists
    if ($row = oci_fetch_assoc($stmt)) {
        $stored_password = $row['PASSWORD'];
        $user_id = $row['TBL_USER_ID'];

        // Compare passwords
        if ($password === $stored_password) {
            session_start();
            $_SESSION['user_id'] = $user_id;

            echo "
            <script>
                alert('Login Successfully!');
                window.location.href = 'http://localhost/ict502/animal.php';
            </script>
            ";
        } else {
            echo "
            <script>
                alert('Login Failed, Incorrect Password!');
                window.location.href = 'http://localhost/ict502/';
            </script>
            ";
        }

    } else {
        echo "
            <script>
                alert('Login Failed, User Not Found!');
                window.location.href = 'http://localhost/ict502/';
            </script>
        ";
    }

    // Free the statement variable
    oci_free_statement($stmt);
}
?>
