<?php
session_start();
// Ensure only students can access this page
if ($_SESSION['role'] !== 'hod') {
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Update student details in the database
    $sql = "UPDATE users SET name = :name, email = :email WHERE registration_number = :registration_number";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'registration_number' => $registration_number
    ]);

    // Redirect back to the student dashboard with a success message
    header("Location: hod_landing.php?message=Profile+Updated+Successfully");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
            align-items: flex-start;
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
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .button {
            background: red;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        .button:hover {
            background: darkred;
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
    </style>
</head>
<body>

    <div class="header">
        <img src="image.png" alt="PTU Logo">
        <h1>PTU Student's Portal<br>Puducherry Technological University</h1>
    </div>

    <div class="sub-header">
        Edit Your Profile
    </div>

    <div class="main-container">
        <div class="profile-container">
            <div class="profile-card">
                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Profile">
                <h3>Edit Profile</h3>
                <form action="editprofilehod.php" method="POST">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>
                    <button type="submit" class="button">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        Maintained by Students of PTU <br>
        Puducherry Technological University, Puducherry - 605014
    </div>

</body>
</html>