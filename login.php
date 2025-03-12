<?php
session_start();
require 'db.php'; // Include PDO database connection

// --- PHPMailer Library Import ---
require 'vendor/autoload.php'; // Ensure Composer autoload is included

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Email Configuration ---
$EMAIL_ADDRESS = "aaryan.m299@ptuniv.edu.in"; // Replace with your Gmail address
$EMAIL_PASSWORD = "pglx fhtx vgvt obkb"; // Replace with your app-specific password

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $registration_number = $_POST['registration_number'];
    $role = $_POST['role'];

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE registration_number = ? AND role = ?");
    $stmt->execute([$registration_number, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['registration_number'] = $registration_number;
        $_SESSION['role'] = $role;
        $_SESSION['department'] = $user['department']; // Store the department in the session

        // Send OTP via Email
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $EMAIL_ADDRESS; // Use your Gmail address
            $mail->Password = $EMAIL_PASSWORD; // Use your app-specific password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom($EMAIL_ADDRESS, 'Online OD System');
            $mail->addAddress($user['email']); // Fetch recipient email from the database
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Login';
            $mail->Body = "Your OTP is: <b>$otp</b>";

            $mail->send();
            header("Location: otp_verification.php");
            exit();
        } catch (Exception $e) {
            echo "OTP could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        // Set an error message to be displayed
        $_SESSION['error'] = "Invalid registration number or role.";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h1 {
            margin-bottom: 20px;
            color: #c40d0d;
        }
        .login-container label {
            display: block;
            margin-bottom: 5px;
            color: #660000;
            font-weight: bold;
        }
        .login-container input, .login-container select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #c40d0d;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #660000;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .warning {
            color: #ff9800;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
    <script>
        function updateLabel() {
            const role = document.getElementById("role").value;
            const label = document.getElementById("input-label");
            const input = document.getElementById("registration_number");

            if (role === "student") {
                label.textContent = "Registration Number:";
                input.placeholder = "Enter your registration number";
            } else if (role === "hod" || role === "dean" || role === "vc") {
                label.textContent = "Staff ID:";
                input.placeholder = "Enter your staff ID";
            } else {
                label.textContent = "Registration Number / Staff ID:";
                input.placeholder = "Enter your registration number or staff ID";
            }
        }

        function validateInput() {
            const input = document.getElementById("registration_number").value;
            const role = document.getElementById("role").value;
            const warning = document.getElementById("warning");

            if (input.trim() === "") {
                warning.textContent = "Please enter a valid registration number or staff ID.";
                return false;
            } else if (role === "") {
                warning.textContent = "Please select a role.";
                return false;
            } else {
                warning.textContent = "";
                return true;
            }
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <form method="POST" onsubmit="return validateInput()">
            <label for="role">Role:</label>
            <select name="role" id="role" required onchange="updateLabel()">
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="hod">HOD</option>
                <option value="dean">Dean</option>
                <option value="vc">VC</option>
            </select><br><br>

            <label id="input-label" for="registration_number">Registration Number:</label>
            <input type="text" name="registration_number" id="registration_number" placeholder="Enter your registration number" required><br><br>

            <div id="warning" class="warning"></div>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>