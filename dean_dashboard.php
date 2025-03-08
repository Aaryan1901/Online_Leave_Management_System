<?php
session_start();
// Ensure only Deans can access this page
if ($_SESSION['role'] !== 'dean') {
    header("Location: login.php");
    exit();
}

require 'db.php'; // Include PDO database connection

// Fetch filters from the URL
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$leave_type_filter = isset($_GET['leave_type']) ? $_GET['leave_type'] : '';

// Build the SQL query with filters
$sql = "SELECT * FROM leave_applications WHERE 1=1";
$params = [];

if (!empty($year_filter)) {
    $sql .= " AND year_of_study = :year";
    $params['year'] = $year_filter;
}
if (!empty($department_filter)) {
    $sql .= " AND department = :department";
    $params['department'] = $department_filter;
}
if (!empty($leave_type_filter)) {
    $sql .= " AND leave_type = :leave_type";
    $params['leave_type'] = $leave_type_filter;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dean Dashboard</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-grow: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #c40d0d;
            color: #fff;
        }
        .filter-section {
            margin-bottom: 20px;
        }
        .filter-section label {
            font-weight: bold;
            margin-right: 10px;
        }
        .filter-section select {
            padding: 5px;
            border-radius: 4px;
        }
        .file-link {
            color: #007bff;
            text-decoration: none;
        }
        .file-link:hover {
            text-decoration: underline;
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
    </style>
</head>
<body>

    <div class="header">
        <img src="image.png" alt="PTU Logo">
        <h1>PTU Dean Portal<br>Puducherry Technological University</h1>
    </div>

    <div class="sub-header">
        Dean Dashboard
    </div>

    <div class="container">
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

            <label for="department">Filter by Department:</label>
            <select id="department" onchange="filterApplications()">
                <option value="">All Departments</option>
                <option value="CSE" <?php echo ($department_filter === 'CSE') ? 'selected' : ''; ?>>Computer Science Engineering</option>
                <option value="ECE" <?php echo ($department_filter === 'ECE') ? 'selected' : ''; ?>>Electronics and Communication Engineering</option>
                <option value="EEE" <?php echo ($department_filter === 'EEE') ? 'selected' : ''; ?>>Electrical and Electronics Engineering</option>
                <option value="MECH" <?php echo ($department_filter === 'MECH') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                <option value="CIVIL" <?php echo ($department_filter === 'CIVIL') ? 'selected' : ''; ?>>Civil Engineering</option>
                <option value="IT" <?php echo ($department_filter === 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                <option value="CHEM" <?php echo ($department_filter === 'CHEM') ? 'selected' : ''; ?>>Chemical Engineering</option>
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
                    <th>Name</th>
                    <th>Enrollment</th>
                    <th>Department</th>
                    <th>Year of Study</th>
                    <th>Leave Type</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Reason</th>
                    <th>Parent Signature</th>
                    <th>Student Signature</th>
                    <th>Advisor Letter</th>
                    <th>HOD Letter</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                <tr>
                    <td><?php echo $application['name']; ?></td>
                    <td><?php echo $application['enrollment']; ?></td>
                    <td><?php echo $application['department']; ?></td>
                    <td><?php echo $application['year_of_study']; ?></td>
                    <td><?php echo $application['leave_type']; ?></td>
                    <td><?php echo $application['from_date']; ?></td>
                    <td><?php echo $application['to_date']; ?></td>
                    <td><?php echo $application['reason']; ?></td>
                    <td>
                        <?php if ($application['parent_sign']): ?>
                            <a href="<?php echo $application['parent_sign']; ?>" class="file-link" target="_blank">View</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($application['student_sign']): ?>
                            <a href="<?php echo $application['student_sign']; ?>" class="file-link" target="_blank">View</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($application['advisor_letter']): ?>
                            <a href="<?php echo $application['advisor_letter']; ?>" class="file-link" target="_blank">View</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($application['hod_letter']): ?>
                            <a href="<?php echo $application['hod_letter']; ?>" class="file-link" target="_blank">View</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?php echo $application['status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <button class="back-btn" onclick="window.location.href='dean_landing.php'">Back to Dean Landing</button>
    </div>

    <script>
        function filterApplications() {
            const year = document.getElementById("year").value;
            const department = document.getElementById("department").value;
            const leave_type = document.getElementById("leave_type").value;
            window.location.href = `dean_dashboard.php?year=${year}&department=${department}&leave_type=${leave_type}`;
        }
    </script>
</body>
</html>