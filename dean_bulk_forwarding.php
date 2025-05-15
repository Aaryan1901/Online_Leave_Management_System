<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'dean') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department = $_POST['department'];
    $year = $_POST['year'];
    
    // Update all pending applications for the selected department/year
    $sql = "UPDATE leave_applications SET status = 'Forwarded to HOD' 
            WHERE department = :department AND year_of_study = :year AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['department' => $department, 'year' => $year]);
    
    header("Location: dean_dashboard.php?message=Bulk+forward+successful");
    exit();
}

// Fetch distinct departments and years
$departments = $conn->query("SELECT DISTINCT department FROM leave_applications")->fetchAll(PDO::FETCH_COLUMN);
$years = $conn->query("SELECT DISTINCT year_of_study FROM leave_applications")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bulk Forward to HOD</title>
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
        form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, button {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #c40d0d;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #a00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bulk Forward to HOD</h1>
        <form method="POST">
            <div class="form-group">
                <label for="department">Department</label>
                <select id="department" name="department" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept ?>"><?= $dept ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="year">Year of Study</label>
                <select id="year" name="year" required>
                    <option value="">Select Year</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year ?>"><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit">Forward All to HOD</button>
        </form>
    </div>
</body>
</html>