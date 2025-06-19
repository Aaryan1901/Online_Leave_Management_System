<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure only dean can access this page
if ($_SESSION['role'] !== 'dean') {
    header("Location: login.php");
    exit();
}

// Email Configuration
$EMAIL_ADDRESS = "aaryan.m299@ptuniv.edu.in";
$EMAIL_PASSWORD = "pglx fhtx vgvt obkb";

// Get current datetime for min attribute
$current_datetime = date('Y-m-d\TH:i');

// Process filters
$department_filter = $_GET['department'] ?? '';
$year_filter = $_GET['year'] ?? '';
$interest_filter = $_GET['interest'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build base query
$sql = "SELECT * FROM career_guidance_applications WHERE 1=1";
$params = [];

// Add filters
if ($department_filter) {
    $sql .= " AND department = :department";
    $params[':department'] = $department_filter;
}

if ($year_filter) {
    $sql .= " AND year_of_study = :year";
    $params[':year'] = $year_filter;
}

if ($interest_filter) {
    $sql .= " AND interests LIKE :interest";
    $params[':interest'] = '%"' . $interest_filter . '"%';
}

if ($status_filter) {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}

$sql .= " ORDER BY submitted_at DESC";

// Fetch applications
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique departments, years, and statuses for filters
$dept_stmt = $conn->query("SELECT DISTINCT department FROM career_guidance_applications ORDER BY department");
$departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);

$year_stmt = $conn->query("SELECT DISTINCT year_of_study FROM career_guidance_applications ORDER BY year_of_study");
$years = $year_stmt->fetchAll(PDO::FETCH_COLUMN);

$status_stmt = $conn->query("SELECT DISTINCT status FROM career_guidance_applications ORDER BY status");
$statuses = $status_stmt->fetchAll(PDO::FETCH_COLUMN);

// Process notification form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notify_students'])) {
    $selected_ids = $_POST['selected_applications'] ?? [];
    $notification_date = $_POST['notification_date'];
    $notification_venue = $_POST['notification_venue'];
    $notification_instructions = $_POST['notification_instructions'];
    
    // Validate date is not in the past
    $selected_datetime = new DateTime($notification_date);
    $current_datetime_obj = new DateTime();
    
    if ($selected_datetime < $current_datetime_obj) {
        $_SESSION['notification_error'] = "Cannot schedule notifications in the past. Please select a future date and time.";
        header("Location: dean_view.php");
        exit();
    }
    
    if (!empty($selected_ids)) {
        // Update selected applications
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $update_sql = "UPDATE career_guidance_applications 
                      SET status = 'Notified', 
                          notification_date = ?, 
                          notification_venue = ?, 
                          notification_instructions = ?
                      WHERE id IN ($placeholders)";
        
        $update_params = array_merge(
            [$notification_date, $notification_venue, $notification_instructions],
            $selected_ids
        );
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute($update_params);
        
        // Send notification emails
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
            $mail->isHTML(true);
            $mail->Subject = 'Career Guidance Session Notification';
            
            // Get emails of selected students
            $email_sql = "SELECT email, name FROM career_guidance_applications WHERE id IN ($placeholders)";
            $email_stmt = $conn->prepare($email_sql);
            $email_stmt->execute($selected_ids);
            $students = $email_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($students as $student) {
                $mail->clearAddresses();
                $mail->addAddress($student['email']);
                
                $mail->Body = "Dear {$student['name']},<br><br>
                              You are invited to attend a career guidance session with the following details:<br><br>
                              <strong>Date:</strong> " . date('F j, Y', strtotime($notification_date)) . "<br>
                              <strong>Time:</strong> " . date('g:i A', strtotime($notification_date)) . "<br>
                              <strong>Venue:</strong> $notification_venue<br>
                              <strong>Instructions:</strong> $notification_instructions<br><br>
                              Please make sure to attend on time.<br><br>
                              Regards,<br>
                              PTU Career Guidance Team";
                
                $mail->send();
            }
            
            $_SESSION['notification_success'] = "Notifications sent successfully to " . count($students) . " students.";
        } catch (Exception $e) {
            $_SESSION['notification_error'] = "Failed to send some notifications: " . $e->getMessage();
        }
        
        header("Location: dean_view.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dean View - Career Guidance Applications</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      background-color: #f5f5f5;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .header {
      background-color: #c40d0d;
      color: #fff;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }
    .header img {
      width: 80px;
      margin-right: 20px;
    }
    .header-text {
      flex-grow: 1;
    }
    .filters {
      background-color: #f0f0f0;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .filter-group {
      display: inline-block;
      margin-right: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #c40d0d;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .notification-form {
      background-color: #f0f0f0;
      padding: 15px;
      border-radius: 5px;
      margin-top: 20px;
    }
    .form-group {
      margin-bottom: 15px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    input[type="text"],
    input[type="date"],
    input[type="datetime-local"],
    textarea,
    select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    textarea {
      height: 100px;
    }
    button {
      background-color: #c40d0d;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background-color: #a00;
    }
    .status-pending {
      color: #ff9800;
      font-weight: bold;
    }
    .status-notified {
      color: #2196F3;
      font-weight: bold;
    }
    .status-completed {
      color: #4CAF50;
      font-weight: bold;
    }
    .alert {
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
    }
    .alert-success {
      background-color: #dff0d8;
      color: #3c763d;
    }
    .alert-error {
      background-color: #f2dede;
      color: #a94442;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="assets/ptu-logo.png" alt="PTU Logo">
      <div class="header-text">
        <h2>Career Guidance Applications</h2>
        <p>Dean View</p>
      </div>
    </div>
    
    <?php if (isset($_SESSION['notification_success'])): ?>
      <div class="alert alert-success">
        <?php echo $_SESSION['notification_success']; unset($_SESSION['notification_success']); ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['notification_error'])): ?>
      <div class="alert alert-error">
        <?php echo $_SESSION['notification_error']; unset($_SESSION['notification_error']); ?>
      </div>
    <?php endif; ?>
    
    <div class="filters">
      <form method="get">
        <div class="filter-group">
          <label for="department">Department:</label>
          <select id="department" name="department">
            <option value="">All Departments</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $department_filter == $dept ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($dept); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="filter-group">
          <label for="year">Year:</label>
          <select id="year" name="year">
            <option value="">All Years</option>
            <?php foreach ($years as $year): ?>
              <option value="<?php echo htmlspecialchars($year); ?>" <?php echo $year_filter == $year ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($year); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="filter-group">
          <label for="interest">Interest:</label>
          <select id="interest" name="interest">
            <option value="">All Interests</option>
            <option value="Higher Education" <?php echo $interest_filter == 'Higher Education' ? 'selected' : ''; ?>>Higher Education</option>
            <option value="Job" <?php echo $interest_filter == 'Job' ? 'selected' : ''; ?>>Job</option>
            <option value="Entrepreneurship" <?php echo $interest_filter == 'Entrepreneurship' ? 'selected' : ''; ?>>Entrepreneurship</option>
            <option value="Start-up" <?php echo $interest_filter == 'Start-up' ? 'selected' : ''; ?>>Start-up</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label for="status">Status:</label>
          <select id="status" name="status">
            <option value="">All Statuses</option>
            <?php foreach ($statuses as $status): ?>
              <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $status_filter == $status ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($status); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <button type="submit">Apply Filters</button>
        <button type="button" onclick="window.location.href='dean_view.php'">Reset</button>
      </form>
    </div>
    
    <form method="post" id="notifyForm">
      <table>
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>Reg No</th>
            <th>Name</th>
            <th>Department</th>
            <th>Year</th>
            <th>Interests</th>
            <th>Support Needed</th>
            <th>Submitted At</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $app): 
            $interests = json_decode($app['interests'], true);
            $support = json_decode($app['support_needed'], true);
          ?>
            <tr>
              <td><input type="checkbox" name="selected_applications[]" value="<?php echo $app['id']; ?>"></td>
              <td><?php echo htmlspecialchars($app['registration_number']); ?></td>
              <td><?php echo htmlspecialchars($app['name']); ?></td>
              <td><?php echo htmlspecialchars($app['department']); ?></td>
              <td><?php echo htmlspecialchars($app['year_of_study']); ?></td>
              <td><?php echo implode(', ', $interests); ?></td>
              <td><?php echo implode(', ', $support); ?></td>
              <td><?php echo date('d M Y H:i', strtotime($app['submitted_at'])); ?></td>
              <td class="status-<?php echo strtolower($app['status']); ?>"><?php echo $app['status']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <div class="notification-form">
        <h3>Notify Selected Students</h3>
        <div class="form-group">
          <label for="notification_date">Session Date & Time:</label>
          <input type="datetime-local" id="notification_date" name="notification_date" 
                 min="<?php echo $current_datetime; ?>" required>
          <small>Please select a future date and time</small>
        </div>
        
        <div class="form-group">
          <label for="notification_venue">Venue:</label>
          <input type="text" id="notification_venue" name="notification_venue" required>
        </div>
        
        <div class="form-group">
          <label for="notification_instructions">Additional Instructions:</label>
          <textarea id="notification_instructions" name="notification_instructions"></textarea>
        </div>
        
        <button type="submit" name="notify_students">Notify Students</button>
      </div>
    </form>
  </div>
  
  <script>
    // Select all checkbox functionality
    document.getElementById('selectAll').addEventListener('change', function() {
      const checkboxes = document.querySelectorAll('input[name="selected_applications[]"]');
      checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
    });
    
    // Form validation
    document.getElementById('notifyForm').addEventListener('submit', function(e) {
      const checkboxes = document.querySelectorAll('input[name="selected_applications[]"]:checked');
      if (checkboxes.length === 0) {
        alert('Please select at least one student to notify.');
        e.preventDefault();
        return;
      }
      
      // Additional client-side date validation
      const notificationDate = new Date(document.getElementById('notification_date').value);
      const now = new Date();
      
      if (notificationDate <= now) {
        alert('Please select a future date and time for the notification.');
        e.preventDefault();
      }
    });
    
    // Set the minimum datetime for the input field
    document.addEventListener('DOMContentLoaded', function() {
      const now = new Date();
      // Adjust for timezone offset
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      document.getElementById('notification_date').min = now.toISOString().slice(0, 16);
    });
  </script>
</body>
</html>