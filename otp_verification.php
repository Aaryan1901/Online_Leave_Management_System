<?php
session_start();

$error_message = ''; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        // Redirect based on role
        $role = $_SESSION['role'];
        if ($role === 'hod') {
            header("Location: hod_landing.php");
        } elseif ($role === 'dean') {
            header("Location: dean_landing.php");
        } elseif ($role === 'vc') {
            header("Location: vc_landing.php");
        } 
        elseif ($role === 'teacher') {
            header("Location: teacher_dashboard.php");
        } 
        elseif ($role === 'admin') {
            header("Location: admin_profile.php");
        } 
        else {
            header("Location: student_dashboard.php");
        }
        exit();
    } else {
        $error_message = "Invalid OTP. Please try again."; // Set error message
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
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
        .otp-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .otp-container h1 {
            margin-bottom: 20px;
        }
        .otp-container input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .otp-container button {
            width: 100%;
            padding: 10px;
            background-color: #c40d0d;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .otp-container button:hover {
            background-color: #660000;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <h1>OTP Verification</h1>

        <!-- Display error message if OTP is incorrect -->
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="otp">Enter OTP:</label>
            <input type="text" name="otp" id="otp" required><br><br>

            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>