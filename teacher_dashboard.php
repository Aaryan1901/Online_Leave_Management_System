<?php
session_start();
require 'db.php';

// Redirect if not logged in as teacher
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Fetch the Teacher's department
$teacher_department = $_SESSION['department'];

// Fetch filters from the URL with sanitization
$year_filter = isset($_GET['year']) ? htmlspecialchars($_GET['year']) : '';
$leave_type_filter = isset($_GET['leave_type']) ? htmlspecialchars($_GET['leave_type']) : '';

// Build the SQL query with filters
$sql = "SELECT * FROM leave_applications WHERE department = :department AND (status = 'Forwarded to Teacher' OR status = 'Attendance Marked')";
$params = ['department' => $teacher_department];

if (!empty($year_filter)) {
    $sql .= " AND year_of_study = :year";
    $params['year'] = $year_filter;
}
if (!empty($leave_type_filter)) {
    $sql .= " AND leave_type = :leave_type";
    $params['leave_type'] = $leave_type_filter;
}

// Add sorting by application date (newest first)
$sql .= " ORDER BY created_at DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    $applications = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard - <?php echo htmlspecialchars($teacher_department); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #ffe6e6;
            text-align: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background-color: #001f3f;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .header img {
            height: 60px;
        }
        .header h1 {
            font-size: 24px;
        }
        .sub-header {
            background-color: darkred;
            color: yellow;
            font-weight: bold;
            padding: 10px;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-grow: 1;
            overflow-x: auto;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
            word-wrap: break-word;
        }
        th {
            background-color: #c40d0d;
            color: #fff;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .mark-attendance-btn {
            padding: 5px 10px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .mark-attendance-btn:hover {
            background: #218838;
        }
        .marked-status {
            color: #28a745;
            font-weight: bold;
        }
        .file-link {
            color: #007bff;
            text-decoration: none;
        }
        .file-link:hover {
            text-decoration: underline;
        }
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .filter-section label {
            font-weight: bold;
            margin-right: 10px;
        }
        .filter-section select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            margin-right: 15px;
        }
        .footer {
            background-color: #001f3f;
            color: white;
            padding: 15px;
            margin-top: auto;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }
        .back-btn {
            background: darkred;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .back-btn:hover {
            background: red;
        }
        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }
        .status-approved {
            color: #4caf50;
            font-weight: bold;
        }
        .status-marked {
            color: #28a745;
            font-weight: bold;
        }
        .highlight-mark {
            background-color: #d4edda;
            transition: background-color 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="image.png" alt="PTU Logo">
        <h1>PTU Teacher Portal<br>Puducherry Technological University</h1>
    </div>

    <div class="sub-header">
        Teacher Dashboard - <?php echo htmlspecialchars($teacher_department); ?>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filter-section">
            <label for="year">Filter by Year:</label>
            <select id="year" onchange="filterApplications()">
                <option value="">All Years</option>
                <option value="1st Year" <?php echo ($year_filter === '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                <option value="2nd Year" <?php echo ($year_filter === '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                <option value="3rd Year" <?php echo ($year_filter === '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                <option value="4th Year" <?php echo ($year_filter === '4th Year') ? 'selected' : ''; ?>>4th Year</option>
            </select>

            <label for="leave_type">Filter by Leave Type:</label>
            <select id="leave_type" onchange="filterApplications()">
                <option value="">All Leave Types</option>
                <option value="personal" <?php echo ($leave_type_filter === 'personal') ? 'selected' : ''; ?>>Leave on Personal/Medical Grounds</option>
                <option value="duty" <?php echo ($leave_type_filter === 'duty') ? 'selected' : ''; ?>>Leave on Duty</option>
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Student Name</th>
                    <th style="width: 12%;">Enrollment</th>
                    <th style="width: 8%;">Year</th>
                    <th style="width: 10%;">From Date</th>
                    <th style="width: 10%;">To Date</th>
                    <th style="width: 20%;">Reason</th>
                    <th style="width: 13%;">Documents</th>
                    <th style="width: 7%;">Status</th>
                    <th style="width: 8%;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No leave applications found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                    <tr id="row-<?php echo $app['id']; ?>">
                        <td><?php echo htmlspecialchars($app['name']); ?></td>
                        <td><?php echo htmlspecialchars($app['enrollment']); ?></td>
                        <td><?php echo htmlspecialchars($app['year_of_study']); ?></td>
                        <td><?php echo htmlspecialchars($app['from_date']); ?></td>
                        <td><?php echo htmlspecialchars($app['to_date']); ?></td>
                        <td><?php echo htmlspecialchars($app['reason']); ?></td>
                        <td>
                            <?php if ($app['student_sign']): ?>
                                <a href="<?php echo htmlspecialchars($app['student_sign']); ?>" class="file-link" target="_blank">Student Sign</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                            |
                            <?php if ($app['advisor_letter']): ?>
                                <a href="<?php echo htmlspecialchars($app['advisor_letter']); ?>" class="file-link" target="_blank">Advisor Letter</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td class="status-<?php echo strtolower(str_replace(' ', '-', $app['status'])); ?>">
                            <?php echo htmlspecialchars($app['status']); ?>
                        </td>
                        <td>
                            <?php if ($app['attendance_marked']): ?>
                                <span class="marked-status">Marked</span>
                            <?php else: ?>
                                <a href="attendance.php?app_id=<?php echo $app['id']; ?>" class="mark-attendance-btn">Mark Attendance</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <button class="back-btn" onclick="window.location.href='index.php'">Log out</button>
    </div>

    <script>
        function filterApplications() {
            const year = document.getElementById("year").value;
            const leave_type = document.getElementById("leave_type").value;
            window.location.href = `teacher_dashboard.php?year=${encodeURIComponent(year)}&leave_type=${encodeURIComponent(leave_type)}`;
        }
    </script>
</body>
</html>