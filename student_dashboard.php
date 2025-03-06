<?php
session_start();
// Ensure only students can access this page
if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .dashboard {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .dashboard h1 {
            margin-bottom: 20px;
        }
        .dashboard button {
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #007bff;
            color: #fff;
        }
        .dashboard button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome, Student!</h1>
        <button onclick="window.location.href='od_form.php'">OD Form</button>
        <button onclick="window.location.href='career_guidance_form.php'">Career Guidance Form</button>
    </div>
</body>
</html>