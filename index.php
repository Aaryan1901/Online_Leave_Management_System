<?php
// Header Content
$university_name = "PUDUCHERRY TECHNOLOGICAL UNIVERSITY";
$system_name = "PTU ADVANCED PORTAL";

// Notice Board Content
$notice_title = "NOTIFICATION";
$welcome_message = "WELCOME TO PTU Advanced Portal";
$notice_details = "Issues related to OD Form, Career Guidance Form";
$contact_info = "TEAM IT";
$email_contact = "aaryan.m299@pec.edu.in";

// Circular Content
$circular_title = "CIRCULAR";
$circular_message = "Important Circulars for Students and Staff";
$circular_details = "1. Academic Calendar for 2023-2024 is now available.<br>2. Last date for submitting OD forms is 30th November 2023.";

// Events Content
$events_title = "EVENTS";
$events_message = "Upcoming Events at PTU";
$events_details = "1. Tech Fest 2023 - 15th December 2023.<br>2. Alumni Meet - 20th December 2023.";
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
            width: 80%;
        }

        /* Information Center */
        .info-center {
            width: 40%;
            background: #1a1a2e;
            padding: 20px;
            border-radius: 10px;
        }

        .info-center h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .login-form input, .login-form select {
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .login-form button {
            background: #2c2c3a;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .login-form button:hover {
            background: #3d3d50;
        }

        /* Notice Board */
        .notice-board {
            width: 50%;
            background: #1a1a2e;
            padding: 20px;
            border-radius: 10px;
        }

        .notice-board h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .tabs span {
            cursor: pointer;
            font-size: 14px;
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
            padding: 15px;
            border-radius: 8px;
        }

        .notice h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .notice p {
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
    <script>
        // Function to update the input label based on role
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
            <form class="login-form" method="POST" action="login.php">
                <label for="role">Role:</label>
                <select name="role" id="role" required onchange="updateLabel()">
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="hod">HOD</option>
                    <option value="dean">Dean</option>
                    <option value="vc">VC</option>
                </select>

                <label id="input-label" for="registration_number">Registration Number:</label>
                <input type="text" name="registration_number" id="registration_number" placeholder="Enter your registration number" required>

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