<?php
session_start();
require 'db.php'; // Include PDO database connection
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Email Configuration
$EMAIL_ADDRESS = "aaryan.m299@ptuniv.edu.in"; // Your Gmail address
$EMAIL_PASSWORD = "pglx fhtx vgvt obkb"; // Your app-specific password

if ($_SESSION['role'] !== 'hod') {
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
    // Update the application status to "Approved"
    $sql = "UPDATE leave_applications SET status = 'Approved' WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    // Send approval email to the student
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $EMAIL_ADDRESS;
        $mail->Password = $EMAIL_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($EMAIL_ADDRESS, 'Online OD System');
        $mail->addAddress($application['email']); // Student's email
        $mail->isHTML(true);
        $mail->Subject = 'OD Application Approved';
        $mail->Body = "Dear " . $application['name'] . ",<br><br>"
                     . "Your OD application has been <b>approved</b>.<br>"
                     . "Details:<br>"
                     . "From Date: " . $application['from_date'] . "<br>"
                     . "To Date: " . $application['to_date'] . "<br>"
                     . "Reason: " . $application['reason'] . "<br><br>"
                     . "Thank you,<br>"
                     . "Online OD System";

        $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send approval email: {$mail->ErrorInfo}");
    }

    // Redirect back to the HOD dashboard with a success message
    header("Location: hod_dashboard.php?message=Application+Approved+Successfully");
    exit();
} else {
    header("Location: hod_dashboard.php?error=Application+not+found");
    exit();
}
?>