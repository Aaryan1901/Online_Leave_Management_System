<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Email Configuration
$EMAIL_ADDRESS = "aaryan.m299@ptuniv.edu.in";
$EMAIL_PASSWORD = "pglx fhtx vgvt obkb";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $reg_no = $_POST['reg_no'];
    $email = $_POST['email'];
    $dept = $_POST['dept'];
    $year = $_POST['year_of_study'];
    $sem = $_POST['sem'];
    
    // Process interests
    $interests = isset($_POST['interest']) ? $_POST['interest'] : [];
    $interests_json = json_encode($interests);
    
    // Process interest details
    $higher_education = [
        'preferred_university' => $_POST['preferred_university'] ?? '',
        'preferred_course' => $_POST['preferred_course'] ?? ''
    ];
    
    $job_details = [
        'job_role' => $_POST['job_role'] ?? '',
        'job_companies' => $_POST['job_companies'] ?? ''
    ];
    
    $entrepreneurship = [
        'idea_stage' => $_POST['idea_stage'] ?? '',
        'sector' => $_POST['entrepreneurship_sector'] ?? ''
    ];
    
    $startup = [
        'cofounders' => $_POST['cofounders'] ?? '',
        'details' => $_POST['startup_details'] ?? ''
    ];
    
    // Process support needed
    $support = isset($_POST['support']) ? $_POST['support'] : [];
    $support_json = json_encode($support);
    $support_others = $_POST['support_others'] ?? '';
    
    // Insert into database
    $sql = "INSERT INTO career_guidance_applications (
        name, registration_number, email, department, year_of_study, semester,
        interests, higher_education_details, job_details, entrepreneurship_details,
        startup_details, support_needed, support_others, status
    ) VALUES (
        :name, :reg_no, :email, :dept, :year, :sem,
        :interests, :higher_education, :job_details, :entrepreneurship,
        :startup, :support, :support_others, 'Pending'
    )";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':reg_no' => $reg_no,
        ':email' => $email,
        ':dept' => $dept,
        ':year' => $year,
        ':sem' => $sem,
        ':interests' => $interests_json,
        ':higher_education' => json_encode($higher_education),
        ':job_details' => json_encode($job_details),
        ':entrepreneurship' => json_encode($entrepreneurship),
        ':startup' => json_encode($startup),
        ':support' => $support_json,
        ':support_others' => $support_others
    ]);
    
    // Send confirmation email to student
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $EMAIL_ADDRESS;
        $mail->Password = $EMAIL_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        $mail->setFrom($EMAIL_ADDRESS, 'PTU Career Guidance');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Career Guidance Form Submitted Successfully';
        
        $mail->Body = "Dear $name,<br><br>
                      Thank you for submitting your career guidance form.<br>
                      We have received your application with the following details:<br><br>
                      <strong>Interests:</strong> " . implode(', ', $interests) . "<br>
                      <strong>Support Needed:</strong> " . implode(', ', $support) . "<br><br>
                      Our career guidance team will review your application and contact you soon.<br><br>
                      Regards,<br>
                      PTU Career Guidance Team";
        
        $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send email to student: {$mail->ErrorInfo}");
    }
    
    // Redirect to success page
    header("Location: career_guidance_success.php");
    exit();
} else {
    header("Location: career_guidance_form.php");
    exit();
}
?>