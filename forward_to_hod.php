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
    $sql = "UPDATE leave_applications SET forwarded_to_hod = TRUE WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    // Redirect back to the Dean dashboard with a success message
    header("Location: dean_dashboard.php?message=Application+Forwarded+to+HOD+Successfully");
    exit();
} else {
    header("Location: dean_dashboard.php?error=Application+not+found");
    exit();
}
?>