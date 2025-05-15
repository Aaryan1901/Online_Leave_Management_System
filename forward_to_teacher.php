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
$semester = $_GET['semester'];
$hod_department = $_SESSION['department'];

// Get the application details
$app_sql = "SELECT * FROM leave_applications WHERE id = :id";
$app_stmt = $conn->prepare($app_sql);
$app_stmt->execute(['id' => $id]);
$application = $app_stmt->fetch(PDO::FETCH_ASSOC);

// Get all teachers for this semester and department
$teachers_sql = "SELECT st.teacher_email, u.name as teacher_name 
                FROM semester_teachers st
                JOIN users u ON st.teacher_email = u.email
                WHERE st.department = :department AND st.semester = :semester";
$teachers_stmt = $conn->prepare($teachers_sql);
$teachers_stmt->execute(['department' => $hod_department, 'semester' => $semester]);
$teachers = $teachers_stmt->fetchAll(PDO::FETCH_ASSOC);

// Send email to each teacher
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
    
    foreach ($teachers as $teacher) {
        $mail->addAddress($teacher['teacher_email']);
        
        // Record the forwarding in the database
        $forward_sql = "INSERT INTO od_teacher_forwarding 
                       (application_id, teacher_email, semester, forwarded_at)
                       VALUES (:app_id, :email, :semester, NOW())";
        $forward_stmt = $conn->prepare($forward_sql);
        $forward_stmt->execute([
            'app_id' => $id,
            'email' => $teacher['teacher_email'],
            'semester' => $semester
        ]);
    }
    
    $mail->isHTML(true);
    $mail->Subject = "OD Application Notification - Semester $semester";
    $mail->Body = "Dear Teacher,<br><br>
                  An OD application has been approved and forwarded to you for attendance marking.<br><br>
                  <strong>Student Details:</strong><br>
                  Name: {$application['name']}<br>
                  Enrollment: {$application['enrollment']}<br>
                  Department: {$application['department']}<br>
                  Semester: {$application['year_of_study']}<br>
                  From Date: {$application['from_date']}<br>
                  To Date: {$application['to_date']}<br>
                  Reason: {$application['reason']}<br><br>
                  Please mark attendance accordingly in your records.<br><br>
                  Thank you,<br>
                  PTU OD System";
    
    $mail->send();
    
    // Update application status
    $update_sql = "UPDATE leave_applications 
                  SET status = 'Forwarded to Teachers', 
                  forwarded_semester = :semester
                  WHERE id = :id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->execute(['id' => $id, 'semester' => $semester]);
    
    header("Location: hod_dashboard.php?message=OD+forwarded+to+Semester+$semester+teachers");
    exit();
} catch (Exception $e) {
    header("Location: hod_dashboard.php?error=Failed+to+send+email");
    exit();
}
?>