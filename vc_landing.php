<?php
session_start();
// Ensure only HODs can access this page
if ($_SESSION['role'] !== 'vc') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>HOD Landing Page</title>
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
        .landing-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .landing-container h1 {
            margin-bottom: 20px;
        }
        .landing-container button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #c40d0d;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .landing-container button:hover {
            background-color: #660000;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <h1>Welcome, Vice Chancellor!</h1>
        <button onclick="window.location.href='vc_dashboard.php'">View OD Status</button>
    </div>
</body>
</html>