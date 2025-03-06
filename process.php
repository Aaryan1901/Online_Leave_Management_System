<?php
// Database connection details
$host = "localhost"; // Replace with your database host
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$database = "online_od"; // Database name

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $enrollment = $_POST['enrollment'];
    $department = $_POST['department'];
    $year_of_study = $_POST['year_of_study'];
    $programme = $_POST['programme'];
    $branch = $_POST['branch'];
    $class = $_POST['class'];
    $leave_type = $_POST['leave_type'];
    $from_date = $_POST[$leave_type . '_from_date'];
    $to_date = $_POST[$leave_type . '_to_date'];
    $reason = $_POST[$leave_type . '_reason'];
    $days_availed = $_POST[$leave_type . '_days_availed'];

    // Handle file uploads
    $upload_dir = "uploads/"; // Directory to store uploaded files
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
    }

    $parent_sign = $upload_dir . basename($_FILES['parent_sign']['name']);
    $student_sign = $upload_dir . basename($_FILES['student_sign']['name']);
    $advisor_letter = $upload_dir . basename($_FILES['advisor_letter']['name']);
    $hod_letter = $upload_dir . basename($_FILES['hod_letter']['name']);

    // Move uploaded files to the upload directory
    move_uploaded_file($_FILES['parent_sign']['tmp_name'], $parent_sign);
    move_uploaded_file($_FILES['student_sign']['tmp_name'], $student_sign);
    move_uploaded_file($_FILES['advisor_letter']['tmp_name'], $advisor_letter);
    move_uploaded_file($_FILES['hod_letter']['tmp_name'], $hod_letter);

    // Insert data into the database
    $sql = "INSERT INTO leave_applications (
        name, enrollment, department, year_of_study, programme, branch, class,
        leave_type, from_date, to_date, reason, days_availed,
        parent_sign, student_sign, advisor_letter, hod_letter
    ) VALUES (
        '$name', '$enrollment', '$department', '$year_of_study', '$programme', '$branch', '$class',
        '$leave_type', '$from_date', '$to_date', '$reason', '$days_availed',
        '$parent_sign', '$student_sign', '$advisor_letter', '$hod_letter'
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Leave application submitted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>