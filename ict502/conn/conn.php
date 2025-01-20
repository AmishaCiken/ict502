<?php
// Oracle Database Connection using OCI8
$servername = "localhost/XE"; // Adjust SID if needed
$username = "hr";
$password = "hr";

$conn = oci_connect($username, $password, $servername);
if (!$conn) {
    $e = oci_error();
    throw new Exception(htmlentities($e['message'], ENT_QUOTES));
}
?>
