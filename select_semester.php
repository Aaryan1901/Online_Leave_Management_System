<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'hod') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$hod_department = $_SESSION['department'];

// Get semesters for the HOD's department
$semesters = $conn->query("SELECT DISTINCT semester FROM semester_teachers WHERE department = '$hod_department'")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Semester</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        h1 {
            color: #c40d0d;
            text-align: center;
        }
        .semester-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        .semester-btn {
            padding: 15px;
            background-color: #c40d0d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }
        .semester-btn:hover {
            background-color: #a00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select Semester to Forward</h1>
        <p>Please select the semester to forward this approved OD to the respective teachers:</p>
        
        <div class="semester-grid">
            <?php foreach ($semesters as $semester): ?>
                <a href="forward_to_teacher.php?id=<?= $id ?>&semester=<?= $semester ?>" class="semester-btn">
                    Semester <?= $semester ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>