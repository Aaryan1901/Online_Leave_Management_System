<?php
session_start();
// Ensure only students can access this page
if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

require 'db.php'; // Include PDO database connection

// Fetch student's OD request history
$registration_number = $_SESSION['registration_number'];
$sql = "SELECT * FROM leave_applications WHERE enrollment = :enrollment";
$stmt = $conn->prepare($sql);
$stmt->execute(['enrollment' => $registration_number]);
$od_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OD Request History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            text-align: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background-color: #001f3f;
            color: white;
            padding: 15px;
            font-size: 24px;
        }
        .sub-header {
            background-color: maroon;
            color: white;
            padding: 10px;
            font-weight: bold;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            padding: 20px;
            flex-grow: 1;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #c40d0d;
            color: #fff;
        }
        .footer {
            background-color: #001f3f;
            color: white;
            padding: 10px;
            text-align: center;
            position: relative;
            bottom: 0;
            width: 100%;
        }
        .back-button {
            background-color: #c40d0d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #660000;
        }
    </style>
</head>
<body>
    <div class="header">PTU Student's Portal - Student<br>Puducherry Technological University</div>
    <div class="sub-header">OD Request History</div>
    
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($od_requests as $request): ?>
                <tr>
                    <td><?php echo $request['from_date']; ?></td>
                    <td><?php echo $request['to_date']; ?></td>
                    <td><?php echo $request['reason']; ?></td>
                    <td><?php echo $request['status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Back Button -->
    <button class="back-button" onclick="window.location.href='od_page.php'">Back to OD Page</button>
    
    <div class="footer">
        Maintained by Students of PTU<br>
        Puducherry Technological University, Puducherry - 605014
    </div>
</body>
</html>