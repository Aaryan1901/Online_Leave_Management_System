<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'vc' && $_SESSION['role'] !== 'dean')) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

// Fetch the application details
$sql = "SELECT * FROM leave_applications WHERE id = $id";
$result = $conn->query($sql);
$application = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .details {
            margin-bottom: 20px;
        }
        .details label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        .details p {
            margin: 5px 0;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Application Details</h1>
        <div class="details">
            <label>Name:</label>
            <p><?php echo $application['name']; ?></p>

            <label>Enrollment Number:</label>
            <p><?php echo $application['enrollment']; ?></p>

            <label>Department:</label>
            <p><?php echo $application['department']; ?></p>

            <label>Leave Type:</label>
            <p><?php echo $application['leave_type']; ?></p>

            <label>From Date:</label>
            <p><?php echo $application['from_date']; ?></p>

            <label>To Date:</label>
            <p><?php echo $application['to_date']; ?></p>

            <label>Reason:</label>
            <p><?php echo $application['reason']; ?></p>

            <label>Status:</label>
            <p><?php echo $application['status']; ?></p>
        </div>
        <a href="<?php echo ($_SESSION['role'] === 'vc') ? 'vc_dashboard.php' : 'dean_dashboard.php'; ?>" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>