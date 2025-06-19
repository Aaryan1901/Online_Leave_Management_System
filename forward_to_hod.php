<?php
session_start();
require 'db.php'; // Include PDO database connection

if ($_SESSION['role'] !== 'dean') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id']; // Get the application ID from the URL

// Fetch the application
$sql = "SELECT * FROM leave_applications WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if ($application) {
    // Update the application to mark it as forwarded to HOD
    $sql = "UPDATE leave_applications SET status = 'Forwarded to HOD' WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    // Email Configuration
    $EMAIL_ADDRESS = "aaryan.m299@ptuniv.edu.in"; // Your Gmail address
    $EMAIL_PASSWORD = "pglx fhtx vgvt obkb"; // Your app-specific password
    
    // Get HOD email based on department from users table
    $hod_email = getHodEmailByDepartment($application['department']);
    
    if ($hod_email) {
        // Send email notification to HOD
        require_once 'vendor/autoload.php'; // Require PHPMailer
        
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $EMAIL_ADDRESS;
            $mail->Password = $EMAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom($EMAIL_ADDRESS, 'PTU Dean Office');
            $mail->addAddress($hod_email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'New Leave Application Forwarded for Approval';
            $mail->Body = "
                <h2>New Leave Application Forwarded</h2>
                <p>Dear HOD,</p>
                <p>A new leave application has been forwarded to you for approval:</p>
                <ul>
                    <li><strong>Student Name:</strong> {$application['name']}</li>
                    <li><strong>Enrollment No:</strong> {$application['enrollment']}</li>
                    <li><strong>Department:</strong> {$application['department']}</li>
                    <li><strong>Year of Study:</strong> {$application['year_of_study']}</li>
                    <li><strong>Leave Type:</strong> {$application['leave_type']}</li>
                    <li><strong>From Date:</strong> {$application['from_date']}</li>
                    <li><strong>To Date:</strong> {$application['to_date']}</li>
                    <li><strong>Reason:</strong> {$application['reason']}</li>
                </ul>
                <p>Please log in to the PTU Leave Management System to review and take action on this application.</p>
                <p>Thank you,</p>
                <p>PTU Dean Office</p>
            ";
            
            $mail->send();
        } catch (Exception $e) {
            // Log error but don't show to user
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    // Redirect back to the Dean dashboard with a success message
    header("Location: dean_dashboard.php?message=Application+Forwarded+to+HOD+Successfully");
    exit();
} else {
    header("Location: dean_dashboard.php?error=Application+not+found");
    exit();
}

// Function to get HOD email by department from users table
function getHodEmailByDepartment($department) {
    global $conn;
    
    $sql = "SELECT email FROM users WHERE role = 'hod' AND department = :department LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['department' => $department]);
    $hod = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $hod ? $hod['email'] : null;
}
?>