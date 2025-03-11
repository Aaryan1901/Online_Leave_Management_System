<?php
session_start();
require 'db.php'; // Include PDO database connection

if ($_SESSION['role'] !== 'dean') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id']; // Get the application ID from the URL
$decision = $_GET['decision']; // Get the Dean's decision (Approved/Rejected)

// Update the Dean's decision in the database
$sql = "UPDATE leave_applications SET dean_decision = :decision WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['decision' => $decision, 'id' => $id]);

// Redirect back to the Dean dashboard with a success message
header("Location: dean_dashboard.php?message=Decision+Updated+Successfully");
exit();
?>