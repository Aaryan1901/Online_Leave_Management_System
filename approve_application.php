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

// Fetch the application to ensure it belongs to the HOD's department and is pending
$sql = "SELECT * FROM leave_applications WHERE id = :id AND department = :department AND status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id, 'department' => $_SESSION['department']]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if ($application) {
    // Check if the student has exceeded the leave quota
    if ($application['days_availed'] >= $application['leave_quota']) {
        header("Location: hod_dashboard.php?error=Leave+quota+exceeded.+Cannot+approve+application.");
        exit();
    }

    // Update the application status to "Approved"
    $sql = "UPDATE leave_applications SET status = 'Approved' WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    // Update the number of days availed by the student
    $new_days_availed = $application['days_availed'] + $application['days_availed'];
    $sql = "UPDATE leave_applications SET days_availed = :days_availed WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['days_availed' => $new_days_availed, 'id' => $id]);

    // Send approval email to the applicant
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
        $mail->addAddress($application['email']); // Fetch applicant's email from the database
        $mail->isHTML(true);
        $mail->Subject = 'Your OD Application has been Approved';
        $mail->Body = "Dear " . $application['name'] . ",<br><br>"
                     . "Your OD application has been <b>approved</b>.<br>"
                     . "Details:<br>"
                     . "From Date: " . $application['from_date'] . "<br>"
                     . "To Date: " . $application['to_date'] . "<br>"
                     . "Reason: " . $application['reason'] . "<br><br>"
                     . "Thank you,<br>"
                     . "Online OD System";

        $mail->send();
        header("Location: hod_dashboard.php?message=Application+Approved+Successfully");
        exit();
    } catch (Exception $e) {
        header("Location: hod_dashboard.php?error=Failed+to+send+approval+email");
        exit();
    }
} else {
    header("Location: hod_dashboard.php?error=You+are+not+authorized+to+approve+this+application+or+it+is+not+pending");
    exit();
}
?>