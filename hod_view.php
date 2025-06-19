<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure only HOD can access this page
if ($_SESSION['role'] !== 'hod') {
    header("Location: login.php");
    exit();
}

// Get HOD's department
$hod_department = $_SESSION['department'] ?? '';
if (empty($hod_department)) {
    die("Department information not found for HOD.");
}

// Email Configuration
$EMAIL_ADDRESS = "aaryan.m299@ptuniv.edu.in";
$EMAIL_PASSWORD = "pglx fhtx vgvt obkb";

// Process filters
$year_filter = $_GET['year'] ?? '';
$interest_filter = $_GET['interest'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build base query - HOD can only see their department's applications
$sql = "SELECT * FROM career_guidance_applications WHERE department = :department";
$params = [':department' => $hod_department];

// Add additional filters
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

// Fetch unique years and statuses for filters (only for HOD's department)
$year_stmt = $conn->prepare("SELECT DISTINCT year_of_study FROM career_guidance_applications WHERE department = :dept ORDER BY year_of_study");
$year_stmt->execute([':dept' => $hod_department]);
$years = $year_stmt->fetchAll(PDO::FETCH_COLUMN);

$status_stmt = $conn->prepare("SELECT DISTINCT status FROM career_guidance_applications WHERE department = :dept ORDER BY status");
$status_stmt->execute([':dept' => $hod_department]);
$statuses = $status_stmt->fetchAll(PDO::FETCH_COLUMN);

// Process status update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $selected_ids = $_POST['selected_applications'] ?? [];
    $new_status = $_POST['new_status'];
    
    if (!empty($selected_ids)) {
        // Update selected applications
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $update_sql = "UPDATE career_guidance_applications 
                      SET status = ?
                      WHERE id IN ($placeholders) AND department = ?";
        
        $update_params = array_merge(
            [$new_status],
            $selected_ids,
            [$hod_department]
        );
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute($update_params);
        
        $_SESSION['status_update_success'] = "Status updated successfully for " . $update_stmt->rowCount() . " applications.";
        
        header("Location: hod_view.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HOD View - Career Guidance Applications</title>
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
    .status-form {
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
    select, textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
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
    .dept-badge {
      background-color: #c40d0d;
      color: white;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.9em;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="assets/ptu-logo.png" alt="PTU Logo">
      <div class="header-text">
        <h2>Career Guidance Applications - HOD View</h2>
        <p>Department: <span class="dept-badge"><?php echo htmlspecialchars($hod_department); ?></span></p>
      </div>
    </div>
    
    <?php if (isset($_SESSION['status_update_success'])): ?>
      <div class="alert alert-success">
        <?php echo $_SESSION['status_update_success']; unset($_SESSION['status_update_success']); ?>
      </div>
    <?php endif; ?>
    
    <div class="filters">
      <form method="get">
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
        <button type="button" onclick="window.location.href='hod_view.php'">Reset</button>
      </form>
    </div>
    
    <form method="post" id="statusForm">
      <table>
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>Reg No</th>
            <th>Name</th>
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
              <td><?php echo htmlspecialchars($app['year_of_study']); ?></td>
              <td><?php echo implode(', ', $interests); ?></td>
              <td><?php echo implode(', ', $support); ?></td>
              <td><?php echo date('d M Y H:i', strtotime($app['submitted_at'])); ?></td>
              <td class="status-<?php echo strtolower($app['status']); ?>"><?php echo $app['status']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <div class="status-form">
        <h3>Update Application Status</h3>
        <div class="form-group">
          <label for="new_status">New Status:</label>
          <select id="new_status" name="new_status" required>
            <option value="">Select Status</option>
            <option value="Pending">Pending</option>
            <option value="Notified">Notified</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
        
        <button type="submit" name="update_status">Update Status</button>
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
    document.getElementById('statusForm').addEventListener('submit', function(e) {
      const checkboxes = document.querySelectorAll('input[name="selected_applications[]"]:checked');
      if (checkboxes.length === 0) {
        alert('Please select at least one application to update.');
        e.preventDefault();
      }
    });
  </script>
</body>
</html>