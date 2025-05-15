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

// Initialize error message
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    
    // Different handling based on role
    if ($role === 'student') {
        $registration_number = $_POST['registration_number'];
        
        // Check if the student exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE registration_number = ? AND role = ?");
        $stmt->execute([$registration_number, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($role === 'teacher') {
        $staff_id = $_POST['staff_id'];
        
        // Check if the teacher exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE staff_id = ? AND role = ?");
        $stmt->execute([$staff_id, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // For HOD, dean, and VC, use email
        $email = $_POST['email'];
        
        // Check if the user exists with the given email and role
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($user) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['role'] = $role;
        $_SESSION['department'] = $user['department']; // Store the department in the session
        
        // Store the appropriate identifier based on role
        if ($role === 'student') {
            $_SESSION['registration_number'] = $user['registration_number'];
        } elseif ($role === 'teacher') {
            $_SESSION['staff_id'] = $user['staff_id'];
        } else {
            $_SESSION['email'] = $user['email'];
        }

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
            
            // Store the target page for redirection after OTP verification
            if ($role === 'teacher') {
                $_SESSION['redirect_after_otp'] = 'teacher_dashboard.php';
            } else if ($role === 'hod') {
                $_SESSION['redirect_after_otp'] = 'hod_dashboard.php';
            } 
            else if ($role === 'admin') {
                $_SESSION['redirect_after_otp'] = 'admin_profile.php';
            } else if ($role === 'dean') {
                $_SESSION['redirect_after_otp'] = 'dean_dashboard.php';
            } else if ($role === 'vc') {
                $_SESSION['redirect_after_otp'] = 'vc_dashboard.php';
            } else {
                $_SESSION['redirect_after_otp'] = 'student_dashboard.php';
            }
            
            header("Location: otp_verification.php");
            exit();
        } catch (Exception $e) {
            $error_message = "OTP could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        $error_message = "Invalid credentials. Please try again.";
    }
}

// Header Content
$university_name = "PUDUCHERRY TECHNOLOGICAL UNIVERSITY";
$system_name = "PTU ADVANCED PORTAL";

// Notice Board Content
$notice_title = "NOTIFICATION";
$welcome_message = "WELCOME TO PTU Advanced Portal";
$notice_details = "Issues related to OD Form, Career Guidance Form";
$contact_info = "TEAM IT";
$email_contact = "aaryan.m299@ptuniv.edu.in";

// Circular Content
$circular_title = "CIRCULAR";
$circular_message = "Important Circulars for Students and Staff";
$circular_details = "1. Academic Calendar for 2023-2024 is now available.<br>2. Last date for submitting OD forms is 15th May 2025.";

// Events Content
$events_title = "EVENTS";
$events_message = "Upcoming Events at PTU";
$events_details = "1. Gigyasa - 10,11 April 2025.<br>2. Alumni Meet - 20th December 2025.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PTU Information System</title>
    <style>
        /* General Styling */
        body {
            background-color: #0f0f17;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 20px;
        }

        /* Header */
        header {
            padding: 20px;
        }

        .logo {
            width: 120px;
            display: block;
            margin: 0 auto;
        }

        h1 {
            font-size: 24px;
            margin-top: 10px;
        }

        h3 {
            font-size: 18px;
            color: #aaaaaa;
        }

        /* Main Layout */
        main {
            display: flex;
            justify-content: space-around;
            margin: 20px auto;
            width: 90%; /* Increased width to utilize more space */
            gap: 20px; /* Added gap between the two sections */
        }

        /* Information Center */
        .info-center {
            width: 45%; /* Increased width */
            background: #1a1a2e;
            padding: 25px; /* Increased padding */
            border-radius: 10px;
            height: 400px; /* Fixed height to make the box larger */
            display: flex;
            flex-direction: column;
            justify-content: center; /* Center content vertically */
        }

        .info-center h2 {
            font-size: 20px; /* Increased font size */
            margin-bottom: 20px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px; /* Increased gap between form elements */
        }

        .login-form input, .login-form select {
            padding: 12px; /* Increased padding */
            border: none;
            border-radius: 6px;
            font-size: 16px; /* Increased font size */
        }

        .login-form button {
            background: #2c2c3a;
            color: white;
            border: none;
            padding: 14px; /* Increased padding */
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px; /* Increased font size */
        }

        .login-form button:hover {
            background: #3d3d50;
        }

        /* Hide input fields initially */
        .input-field {
            display: none;
        }

        /* Notice Board */
        .notice-board {
            width: 50%; /* Increased width */
            background: #1a1a2e;
            padding: 25px; /* Increased padding */
            border-radius: 10px;
            height: 400px; /* Fixed height to make the box larger */
            display: flex;
            flex-direction: column;
            justify-content: center; /* Center content vertically */
        }

        .notice-board h2 {
            font-size: 20px; /* Increased font size */
            margin-bottom: 15px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            justify-content: center;
            gap: 20px; /* Increased gap between tabs */
            margin-bottom: 20px; /* Increased margin */
        }

        .tabs span {
            cursor: pointer;
            font-size: 16px; /* Increased font size */
            color: #6ec6d9;
        }

        .tabs .active {
            color: white;
            border-bottom: 2px solid #6ec6d9;
        }

        /* Notice Content */
        .notice {
            text-align: left;
            background: #22223b;
            padding: 20px; /* Increased padding */
            border-radius: 8px;
            height: 280px; /* Fixed height to make the content area larger */
            overflow-y: auto; /* Add scrollbar if content overflows */
        }

        .notice h3 {
            font-size: 18px; /* Increased font size */
            margin-bottom: 15px;
        }

        .notice p {
            font-size: 16px; /* Increased font size */
            line-height: 1.6; /* Increased line height for better readability */
        }

        /* Error Message */
        .error {
            color: red;
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px; /* Increased font size */
        }
    </style>
    <script>
        // Function to update the input field based on role
        function updateInputField() {
            const role = document.getElementById("role").value;
            
            // Hide all input fields first
            document.getElementById("reg_field").style.display = "none";
            document.getElementById("staff_field").style.display = "none";
            document.getElementById("email_field").style.display = "none";
            
            // Show the appropriate input field based on role
            if (role === "student") {
                document.getElementById("reg_field").style.display = "block";
            } else if (role === "teacher") {
                document.getElementById("staff_field").style.display = "block";
            } else if (role === "hod" || role === "dean" || role === "vc" || role === "admin") {
                document.getElementById("email_field").style.display = "block";
            }
        }

        // Function to switch tabs
        function switchTab(tabName) {
            // Hide all notice content
            const noticeContents = document.querySelectorAll('.notice-content');
            noticeContents.forEach(content => {
                content.style.display = 'none';
            });

            // Show the selected tab content
            document.getElementById(tabName).style.display = 'block';

            // Update active tab
            const tabs = document.querySelectorAll('.tabs span');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`.tabs span[data-tab="${tabName}"]`).classList.add('active');
        }
        
        // Function to validate the form before submission
        function validateForm() {
            const role = document.getElementById("role").value;
            
            if (role === "") {
                alert("Please select a role");
                return false;
            }
            
            if (role === "student") {
                const regNo = document.getElementById("registration_number").value;
                if (regNo === "") {
                    alert("Please enter your registration number");
                    return false;
                }
            } else if (role === "teacher") {
                const staffId = document.getElementById("staff_id").value;
                if (staffId === "") {
                    alert("Please enter your staff ID");
                    return false;
                }
            } else {
                const email = document.getElementById("email").value;
                if (email === "") {
                    alert("Please enter your email ID");
                    return false;
                }
            }
            
            return true;
        }
    </script>
</head>
<body>
    <header>
        <img src="image.png" alt="PTU Logo" class="logo">
        <h1><?php echo $university_name; ?></h1>
        <h3><?php echo $system_name; ?></h3>
    </header>

    <main>
        <section class="info-center">
            <h2>PTU INFORMATION CENTER</h2>
            <form class="login-form" method="POST" onsubmit="return validateForm()">
                <label for="role">Role:</label>
                <select name="role" id="role" required onchange="updateInputField()">
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="hod">HOD</option>
                    <option value="dean">Dean</option>
                    <option value="vc">VC</option>
                    <option value="admin">admin</option>
                </select>

                <!-- Registration Number Field (for Students) -->
                <div id="reg_field" class="input-field">
                    <label for="registration_number">Registration Number:</label>
                    <input type="text" name="registration_number" id="registration_number" placeholder="Enter your registration number">
                </div>

                <!-- Staff ID Field (for Teachers) -->
                <div id="staff_field" class="input-field">
                    <label for="staff_id">Staff ID:</label>
                    <input type="text" name="staff_id" id="staff_id" placeholder="Enter your staff ID">
                </div>

                <!-- Email Field (for HOD, Dean, VC) -->
                <div id="email_field" class="input-field">
                    <label for="email">Email ID:</label>
                    <input type="email" name="email" id="email" placeholder="Enter your email ID">
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <button type="submit">Login</button>
            </form>
        </section>

        <section class="notice-board">
            <h2>NOTICE BOARD</h2>
            <div class="tabs">
                <span data-tab="notification" class="active" onclick="switchTab('notification')">NOTIFICATION</span>
                <span data-tab="circular" onclick="switchTab('circular')">CIRCULAR</span>
                <span data-tab="events" onclick="switchTab('events')">EVENTS</span>
            </div>

            <!-- Notification Content -->
            <div id="notification" class="notice-content notice" style="display: block;">
                <h3><?php echo $notice_title; ?></h3>
                <p><strong><?php echo $welcome_message; ?></strong></p>
                <p><?php echo $notice_details; ?></p>
                <p><strong>CONTACT:</strong> <b><?php echo $contact_info; ?></b></p>
                <p>Mail to <strong><?php echo $email_contact; ?></strong> with Register No., Name, and Department regarding the issue. Send only one mail and wait for at least 2 days for a reply.</p>
            </div>

            <!-- Circular Content -->
            <div id="circular" class="notice-content notice" style="display: none;">
                <h3><?php echo $circular_title; ?></h3>
                <p><strong><?php echo $circular_message; ?></strong></p>
                <p><?php echo $circular_details; ?></p>
            </div>

            <!-- Events Content -->
            <div id="events" class="notice-content notice" style="display: none;">
                <h3><?php echo $events_title; ?></h3>
                <p><strong><?php echo $events_message; ?></strong></p>
                <p><?php echo $events_details; ?></p>
            </div>
        </section>
    </main>
</body>
</html>