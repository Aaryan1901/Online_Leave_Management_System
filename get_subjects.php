<?php
require 'db.php';

$department = $_GET['department'] ?? '';
$semester = $_GET['semester'] ?? '';

if ($department && $semester) {
    $stmt = $conn->prepare("SELECT subject_name FROM semester_teachers 
                           WHERE department = :department AND semester = :semester");
    $stmt->execute(['department' => $department, 'semester' => $semester]);
    $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    header('Content-Type: application/json');
    echo json_encode($subjects);
}