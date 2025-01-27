<?php
include('conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email_address = trim($_POST['email_address'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate inputs
    $errors = [];
    if (empty($first_name)) $errors[] = 'First Name';
    if (empty($last_name)) $errors[] = 'Last Name';
    if (empty($email_address)) $errors[] = 'Email Address';
    if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email Address';
    if (empty($phone_number)) $errors[] = 'Phone Number';
    if (empty($password)) $errors[] = 'Password';

    if (!empty($errors)) {
        $missing_fields = implode(', ', $errors);
        echo "<script>
                alert('The following fields are required: $missing_fields');
                window.history.back();
              </script>";
        exit;
    }

    try {
        // Check if the email is already registered
        $checkQuery = "SELECT Email_Address FROM tbl_user WHERE Email_Address = :email_address";
        $stmt = oci_parse($conn, $checkQuery);
        oci_bind_by_name($stmt, ":email_address", $email_address);
        oci_execute($stmt);

        if (oci_fetch_assoc($stmt)) {
            echo "<script>
                    alert('Email Already Registered!');
                    window.location.href = 'signup.php';
                  </script>";
            exit;
        }

        // Insert new user data
        $insertQuery = "INSERT INTO tbl_user (tbl_user_id, First_Name, Last_Name, Phone_Number, Email_Address, password) 
                        VALUES (seq_tbl_user_id.nextval, :first_name, :last_name, :phone_number, :email_address, :password)";
        $insertStmt = oci_parse($conn, $insertQuery);
        oci_bind_by_name($insertStmt, ":first_name", $first_name);
        oci_bind_by_name($insertStmt, ":last_name", $last_name);
        oci_bind_by_name($insertStmt, ":phone_number", $phone_number);
        oci_bind_by_name($insertStmt, ":email_address", $email_address);
        oci_bind_by_name($insertStmt, ":password", $password);

        // Execute the query
        if (!oci_execute($insertStmt, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($insertStmt);
            throw new Exception("SQL Insert Error: " . $e['message']);
        }

        // Commit the transaction
        oci_commit($conn);

        echo "<script>
                alert('Registration Successful!');
                window.location.href = 'index.php';
              </script>";
    } catch (Exception $e) {
        // Rollback transaction on error
        oci_rollback($conn);
        echo "<script>
                alert('Error: " . htmlentities($e->getMessage(), ENT_QUOTES) . "');
                window.history.back();
              </script>";
    } finally {
        // Free resources
        oci_free_statement($stmt);
        if (isset($insertStmt)) {
            oci_free_statement($insertStmt);
        }
        oci_close($conn);
    }
} else {
    echo "<script>
            alert('Invalid Request Method.');
            window.location.href = 'signup.php';
          </script>";
}
?>
