<?php
session_start();
include('./conn/conn.php');  // Ensure the database connection is included

// Fetch available farms
$query = "
    SELECT f.FarmID, f.FarmName, f.FarmLocation, f.FarmSize, f.WaterSource, 
           p.PlotSize, p.PlotType, s.SoilName, s.SoilCondition
    FROM Farm f
    LEFT JOIN Plot p ON f.FarmID = p.FarmID
    LEFT JOIN SoilType s ON p.SoilTypeID = s.SoilTypeID
    WHERE f.FarmID NOT IN (
        SELECT FarmID FROM FarmBooking
        WHERE Status IN ('Approved', 'Pending')
    )
";

$stid = oci_parse($conn, $query);
oci_execute($stid);

// Fetching the results
$availableFarms = [];
while ($row = oci_fetch_assoc($stid)) {
    $availableFarms[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Farms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .content-box {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        th {
            background-color: #f8f9fa;
        }
        h2 {
            font-size: 24px;
            font-weight: 600;
        }
        h4 {
            font-size: 18px;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="container my-4">
        <div class="content-box">
            <h2 class="text-center">Available Farms</h2>
            <h4>Farm List</h4>
            <div class="table-wrapper">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Farm Name</th>
                            <th>Location</th>
                            <th>Size</th>
                            <th>Water Source</th>
                            <th>Plot Size</th>
                            <th>Plot Type</th>
                            <th>Soil Type</th>
                            <th>Soil Condition</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($availableFarms as $farm): ?>
                        <tr>
                            <td><?= htmlspecialchars($farm['FARMNAME']) ?></td>
                            <td><?= htmlspecialchars($farm['FARMLOCATION']) ?></td>
                            <td><?= htmlspecialchars($farm['FARMSIZE']) ?> ha</td>
                            <td><?= htmlspecialchars($farm['WATERSOURCE']) ?></td>
                            <td><?= htmlspecialchars($farm['PLOTSIZE']) ?> ha</td>
                            <td><?= htmlspecialchars($farm['PLOTTYPE']) ?></td>
                            <td><?= htmlspecialchars($farm['SOILNAME']) ?></td>
                            <td><?= htmlspecialchars($farm['SOILCONDITION']) ?></td>
                            <td>
                                <a href="book_farm.php?farmID=<?= $farm['FARMID'] ?>" class="btn btn-success">Book Now</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
