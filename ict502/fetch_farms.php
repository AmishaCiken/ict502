<?php
// Include the database connection
include('./conn/conn.php');

// Get the query parameter from the request
$query = isset($_GET['query']) ? $_GET['query'] : '';

if (!empty($query)) {
    // Prepare the SQL query to search farms by name
    $sql = "SELECT FarmID, FarmName FROM Farm WHERE LOWER(FarmName) LIKE LOWER(:query)";
    $stmt = oci_parse($conn, $sql);
    
    // Bind the search term with wildcards
    $searchTerm = '%' . $query . '%';
    oci_bind_by_name($stmt, ':query', $searchTerm);

    // Execute the query
    oci_execute($stmt);

    // Initialize an array to store the results
    $farms = [];

    // Fetch the results and populate the array
    while (($row = oci_fetch_assoc($stmt)) != false) {
        $farms[] = [
            'FarmID' => $row['FARMID'],
            'FarmName' => $row['FARMNAME']
        ];
    }

    // Return the results as JSON
    echo json_encode($farms);
} else {
    // If no query is provided, return an empty array
    echo json_encode([]);
}
?>
