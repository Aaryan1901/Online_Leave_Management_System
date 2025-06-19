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

// Check if the name exists
if (!$student || !isset($student['name'])) {
    die("Student details not found. Please contact the administrator.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PTU Student's Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #ffe6e6;
            text-align: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background-color: #001f3f;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .header img {
            height: 60px;
        }
        .header h1 {
            font-size: 24px;
        }
        .sub-header {
            background-color: darkred;
            color: yellow;
            font-weight: bold;
            padding: 10px;
        }
        .main-container {
            display: flex;
            flex: 1;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 40px;
            padding: 20px;
        }
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 300px;
        }
        .profile-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
        }
        .profile-card img {
            height: 60px;
        }
        .edit-btn {
            background: red;
            color: white;
            padding: 15px;
            border: none;
            cursor: pointer;
            margin-top: 15px;
            border-radius: 5px;
            width: 100%;
            font-size: 18px;
            font-weight: bold;
        }
        .edit-btn:hover {
            background: darkred;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            font-size: 22px;
            transition: 0.3s;
        }
        .card:hover {
            background: #ddd;
            transform: scale(1.05);
        }
        .footer {
            background-color: #001f3f;
            color: white;
            padding: 15px;
            margin-top: auto;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }
        .logout-button {
            background-color: #c40d0d;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .logout-button:hover {
            background-color: #660000;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="image.png" alt="PTU Logo">
        <h1>PTU Student's Portal<br>Puducherry Technological University</h1>
    </div>

    <div class="sub-header">
        Warm Welcome to the Student Portal !!!
    </div>

    <div class="main-container">
        <div class="profile-container">
            <div class="profile-card">
                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Profile">
                <h3>Welcome, <?php echo htmlspecialchars($student['name']); ?></h3>
                <p><strong>Reg No:</strong> <?php echo htmlspecialchars($student['registration_number']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <button class="edit-btn" onclick="window.location.href='edit_profile.php'">Edit Profile</button>
            </div>
        </div>
        
        <div class="button-container">
            <div class="card">Student Monitoring</div>
            <div class="card">Student Counselling</div>
            <div class="card" onclick="window.location.href='od_page.php'">Online OD Request</div>
            <div class="card" onclick="window.location.href='career_guidance_form.php'">Career Guidance</div>
        </div>
    </div>

    <button class="logout-button" onclick="window.location.href='index.php'">Logout</button>

    <div class="footer">
        Maintained by Students of PTU <br>
        Puducherry Technological University, Puducherry - 605014
    </div>

</body>
</html>