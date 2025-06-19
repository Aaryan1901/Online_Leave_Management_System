<?php
session_start();
if (!isset($_SESSION['registration_number'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Form Submission Successful</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      background-color: #f5f5f5;
      text-align: center;
    }
    .container {
      max-width: 600px;
      margin: 50px auto;
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .success-icon {
      color: #4CAF50;
      font-size: 50px;
      margin-bottom: 20px;
    }
    h1 {
      color: #c40d0d;
    }
    .btn {
      display: inline-block;
      background-color: #c40d0d;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 4px;
      margin-top: 20px;
    }
    .btn:hover {
      background-color: #660000;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="success-icon">✓</div>
    <h1>Form Submitted Successfully!</h1>
    <p>Your career guidance form has been submitted successfully.</p>
    <p>You will receive a confirmation email shortly.</p>
    <a href="student_dashboard.php" class="btn">Return to Dashboard</a>
  </div>
</body>
</html>