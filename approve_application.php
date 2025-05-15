<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SESSION['role'] !== 'hod') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

// Fetch the application
$sql = "SELECT * FROM leave_applications WHERE id = :id AND department = :department";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id, 'department' => $_SESSION['department']]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if ($application) {
    // Update status to Approved
    $update_sql = "UPDATE leave_applications SET status = 'Approved' WHERE id = :id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->execute(['id' => $id]);
    
    // Send approval email to student
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aaryan.m299@ptuniv.edu.in';
        $mail->Password = 'pglx fhtx vgvt obkb';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        $mail->setFrom('aaryan.m299@ptuniv.edu.in', 'PTU OD System');
        $mail->addAddress($application['email']);
        $mail->isHTML(true);
        $mail->Subject = 'Your OD Application has been Approved';
        $mail->Body = "Dear {$application['name']},<br><br>
                      Your OD application has been <b>approved</b> by the HOD.<br>
                      Details:<br>
                      From Date: {$application['from_date']}<br>
                      To Date: {$application['to_date']}<br>
                      Reason: {$application['reason']}<br><br>
                      Thank you,<br>
                      PTU OD System";
        
        $mail->send();
        
        // Redirect to semester selection
        header("Location: select_semester.php?id=$id");
        exit();
    } catch (Exception $e) {
        header("Location: hod_dashboard.php?error=Failed+to+send+approval+email");
        exit();
    }
} else {
    header("Location: hod_dashboard.php?error=Application+not+found");
    exit();
}
?>