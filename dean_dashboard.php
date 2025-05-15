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
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

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
if (!empty($status_filter)) {
    $sql .= " AND status = :status";
    $params['status'] = $status_filter;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch department-wise statistics
$report_sql = "SELECT department, 
                      COUNT(*) AS total_applications, 
                      SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved, 
                      SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected,
                      SUM(CASE WHEN status = 'Forwarded to HOD' THEN 1 ELSE 0 END) AS forwarded,
                      SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending
               FROM leave_applications 
               GROUP BY department";
$report_stmt = $conn->prepare($report_sql);
$report_stmt->execute();
$department_reports = $report_stmt->fetchAll(PDO::FETCH_ASSOC);
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
            max-width: 100%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-grow: 1;
            overflow-x: auto;
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
        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .forward {
            background-color: #007bff;
            color: #fff;
        }
        .approve {
            background-color: #28a745;
            color: #fff;
        }
        .reject {
            background-color: #dc3545;
            color: #fff;
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
        }
        .filter-section label {
            font-weight: bold;
            margin-right: 10px;
        }
        .filter-section select {
            padding: 5px;
            border-radius: 4px;
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
        .report-section {
            margin-top: 40px;
        }
        .report-section h2 {
            margin-bottom: 20px;
        }
        .chart-container {
            width: 80%;
            margin: 0 auto;
        }
        /* Highlight animation */
        .highlight {
            background-color: #d4edda; /* Light green background */
            transition: background-color 0.5s ease;
        }
        .bulk-forward-btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px 0;
            transition: background-color 0.3s;
        }
        .bulk-forward-btn:hover {
            background-color: #0056b3;
        }
    </style>
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <!-- Bulk Forward Button -->
        <a href="dean_bulk_forwarding.php" class="bulk-forward-btn">Bulk Forward to HODs</a>

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
            
            <label for="status">Filter by Status:</label>
            <select id="status" onchange="filterApplications()">
                <option value="">All Statuses</option>
                <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Forwarded to HOD" <?php echo ($status_filter === 'Forwarded to HOD') ? 'selected' : ''; ?>>Forwarded to HOD</option>
                <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Name</th>
                    <th style="width: 7%;">Enrollment</th>
                    <th style="width: 7%;">Department</th>
                    <th style="width: 7%;">Year of Study</th>
                    <th style="width: 8%;">Leave Type</th>
                    <th style="width: 7%;">From Date</th>
                    <th style="width: 7%;">To Date</th>
                    <th style="width: 10%;">Reason</th>
                    <th style="width: 6%;">Parent Sign</th>
                    <th style="width: 6%;">Student Sign</th>
                    <th style="width: 6%;">Advisor Letter</th>
                    <th style="width: 6%;">HOD Letter</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                <tr id="row-<?php echo $application['id']; ?>">
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
                    <td class="action-buttons">
                        <?php if ($application['status'] === 'Pending'): ?>
                            <button class="forward" onclick="forwardToHOD(<?php echo $application['id']; ?>)">Forward to HOD</button>
                        <?php else: ?>
                            <span>No actions available</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Department-wise Reports -->
        <div class="report-section">
            <h2>Department-wise Reports</h2>
            <div class="chart-container">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>

    <div class="footer">
        <button class="back-btn" onclick="window.location.href='dean_landing.php'">Back to Dean Landing</button>
    </div>

    <script>
        function filterApplications() {
            const year = document.getElementById("year").value;
            const department = document.getElementById("department").value;
            const leave_type = document.getElementById("leave_type").value;
            const status = document.getElementById("status").value;
            window.location.href = `dean_dashboard.php?year=${year}&department=${department}&leave_type=${leave_type}&status=${status}`;
        }
        
        function forwardToHOD(id) {
            if (confirm("Are you sure you want to forward this application to the respective HOD?")) {
                // Highlight the row
                const row = document.getElementById(`row-${id}`);
                row.classList.add('highlight');

                // Disable the button
                const button = row.querySelector('.forward');
                button.disabled = true;
                button.textContent = 'Forwarding...';

                // Redirect to forward_to_hod.php
                setTimeout(() => {
                    window.location.href = `forward_to_hod.php?id=${id}`;
                }, 1000); // Delay for animation
            }
        }

        // Chart.js for Department-wise Reports
        const departmentReports = <?php echo json_encode($department_reports); ?>;
        const departments = departmentReports.map(report => report.department);
        const totalApplications = departmentReports.map(report => report.total_applications);
        const approvedApplications = departmentReports.map(report => report.approved);
        const rejectedApplications = departmentReports.map(report => report.rejected);
        const forwardedApplications = departmentReports.map(report => report.forwarded);
        const pendingApplications = departmentReports.map(report => report.pending);

        const ctx = document.getElementById('departmentChart').getContext('2d');
        const departmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: departments,
                datasets: [
                    {
                        label: 'Total Applications',
                        data: totalApplications,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pending Applications',
                        data: pendingApplications,
                        backgroundColor: 'rgba(255, 206, 86, 0.6)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Forwarded Applications',
                        data: forwardedApplications,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Approved Applications',
                        data: approvedApplications,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Rejected Applications',
                        data: rejectedApplications,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>