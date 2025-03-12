<?php
session_start();
// Ensure only students can access this page
if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

require 'db.php'; // Include PDO database connection

// Fetch student details
$registration_number = $_SESSION['registration_number'];
$sql = "SELECT * FROM users WHERE registration_number = :registration_number";
$stmt = $conn->prepare($sql);
$stmt->execute(['registration_number' => $registration_number]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online OD Request</title>
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
        .card {
            background: #ffe6e6;
            padding: 20px;
            margin: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 250px;
        }
        .button {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .button:hover {
            background-color: darkred;
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
            border: none;
            padding: 10px 20px;
            margin-top: 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .back-button:hover {
            background-color: #660000;
        }
    </style>
</head>
<body>
    <div class="header">PTU Student's Portal - Student<br>Puducherry Technological University</div>
    <div class="sub-header">Warm Welcome to the Online OD Request Student Portal !!!</div>
    
    <div class="container">
        <div class="card">
            <h2>Welcome, <?php echo $student['name']; ?></h2>
            <p><strong>Reg No:</strong> <?php echo $student['registration_number']; ?></p>
            <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <img src="pencil-icon.png" alt="New OD Request" width="50">
            <h3>New OD Request</h3>
            <p>Submit a new On-Duty Request</p>
            <button class="button" onclick="window.location.href='od_form.php'">Create Request</button>
        </div>
        <div class="card">
            <img src="clipboard-icon.png" alt="OD Request History" width="50">
            <h3>OD Request History</h3>
            <p>View your previous OD requests</p>
            <button class="button" onclick="window.location.href='od_history.php'">View History</button>
        </div>
    </div>

    <button class="back-button" onclick="window.location.href='student_dashboard.php'">Back to Dashboard</button>
    
    <div class="footer">
        Maintained by Students of PTU<br>
        Puducherry Technological University, Puducherry - 605014
    </div>
</body>
</html>