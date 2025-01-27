<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Navigation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .button-container button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .button-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Admin Navigation</h1>
    <div class="button-container">
        <button onclick="window.location.href='admin_AddTool.php'">Add Tool</button>
        <button onclick="window.location.href='admin_animal.php'">Animal</button>
        <button onclick="window.location.href='admin_animalproduce.php'">Animal Produce</button>
        <button onclick="window.location.href='admin_availablefarm.php'">Available Farm</button>
        <button onclick="window.location.href='admin_bookingFarm.php'">Booking Farm</button>
        <button onclick="window.location.href='admin_crop_produce.php'">Crop Produce</button>
        <button onclick="window.location.href='admin_crop.php'">Crop</button>
        <button onclick="window.location.href='admin_tool.php'">Tool</button>
    </div>
</body>
</html>