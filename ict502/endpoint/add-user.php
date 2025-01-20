<?php 
include ('../conn/conn.php');

if (isset($_POST['first_name'], $_POST['last_name'], $_POST['email_address'], $_POST['password'], $_POST['phone_number'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email_address = $_POST['email_address'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    try {
        // Query untuk menyemak jika email wujud dalam jadual
        $checkQuery = "SELECT Email_Address FROM tbl_user WHERE Email_Address = :email_address";
        $stmt = oci_parse($conn, $checkQuery);
        oci_bind_by_name($stmt, ":email_address", $email_address);
        oci_execute($stmt);

        $emailExist = oci_fetch_assoc($stmt);

        if (empty($emailExist)) {
            // Query untuk memasukkan data pengguna baru
            $insertQuery = "INSERT INTO tbl_user (tbl_user_id, First_Name, Last_Name, Phone_Number, Email_Address, password) 
                            VALUES (seq_tbl_user_id.nextval, :first_name, :last_name, :phone_number, :email_address, :password)";
            $insertStmt = oci_parse($conn, $insertQuery);
            oci_bind_by_name($insertStmt, ":first_name", $first_name);
            oci_bind_by_name($insertStmt, ":last_name", $last_name);
            oci_bind_by_name($insertStmt, ":phone_number", $phone_number);
            oci_bind_by_name($insertStmt, ":email_address", $email_address);
            oci_bind_by_name($insertStmt, ":password", $password);

            // Jalankan query untuk memasukkan data
            if (!oci_execute($insertStmt, OCI_NO_AUTO_COMMIT)) {
                $e = oci_error($insertStmt);
                throw new Exception("SQL Insert Error: " . $e['message']);
            }

            // Lakukan commit transaksi
            oci_commit($conn);

            echo "
            <script>
                alert('Registered Successfully!');
                window.location.href = 'http://localhost/ict502/';
            </script>
            ";
        } else {
            echo "
            <script>
                alert('Email Already Registered!');
                window.location.href = 'http://localhost/ict502/';
            </script>
            ";
        }
    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        oci_rollback($conn);
        echo "Error: " . $e->getMessage();
    }

    // Bebaskan statement
    oci_free_statement($stmt);
    if (isset($insertStmt)) {
        oci_free_statement($insertStmt);
    }
}
?>
