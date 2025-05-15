<?php
session_start();
// Ensure only VCs can access this page
if ($_SESSION['role'] !== 'vc') {
    header("Location: login.php");
    exit();
}

require 'db.php'; // Include PDO database connection

// Fetch filters from the URL
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$leave_type_filter = isset($_GET['leave_type']) ? $_GET['leave_type'] : '';

// Check if PDF download is requested
if (isset($_GET['download_pdf'])) {
    require_once __DIR__ . '/vendor/autoload.php';

    
    
    // Fetch data for PDF
    $report_sql = "SELECT department, 
                          COUNT(*) AS total_applications, 
                          SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved, 
                          SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected 
                   FROM leave_applications 
                   GROUP BY department";
    $report_stmt = $conn->prepare($report_sql);
    $report_stmt->execute();
    $department_reports = $report_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('PTU Leave Management System');
    $pdf->SetAuthor('PTU Admin');
    $pdf->SetTitle('Leave Applications Analytics Report');
    $pdf->SetSubject('Analytics Report');
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 15, 'PTU Leave Applications Analytics Report', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Filters info
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Filters Applied:', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Year: ' . ($year_filter ? $year_filter : 'All Years'), 0, 1);
    $pdf->Cell(0, 6, 'Department: ' . ($department_filter ? $department_filter : 'All Departments'), 0, 1);
    $pdf->Cell(0, 6, 'Leave Type: ' . ($leave_type_filter ? $leave_type_filter : 'All Types'), 0, 1);
    $pdf->Ln(10);
    
    // Department-wise statistics
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Department-wise Statistics:', 0, 1);
    
    // Table header
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(60, 7, 'Department', 1, 0, 'C', 1);
    $pdf->Cell(40, 7, 'Total Applications', 1, 0, 'C', 1);
    $pdf->Cell(40, 7, 'Approved', 1, 0, 'C', 1);
    $pdf->Cell(40, 7, 'Rejected', 1, 0, 'C', 1);
    $pdf->Cell(40, 7, 'Approval Rate', 1, 1, 'C', 1);
    
    // Table data
    $pdf->SetFont('helvetica', '', 10);
    foreach ($department_reports as $report) {
        $approval_rate = ($report['total_applications'] > 0) ? 
                         round(($report['approved'] / $report['total_applications']) * 100, 2) : 0;
        
        $pdf->Cell(60, 6, $report['department'], 1);
        $pdf->Cell(40, 6, $report['total_applications'], 1, 0, 'C');
        $pdf->Cell(40, 6, $report['approved'], 1, 0, 'C');
        $pdf->Cell(40, 6, $report['rejected'], 1, 0, 'C');
        $pdf->Cell(40, 6, $approval_rate . '%', 1, 1, 'C');
    }
    
    // Summary statistics
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Summary Statistics:', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    $total_applications = array_sum(array_column($department_reports, 'total_applications'));
    $total_approved = array_sum(array_column($department_reports, 'approved'));
    $total_rejected = array_sum(array_column($department_reports, 'rejected'));
    $total_pending = $total_applications - $total_approved - $total_rejected;
    $overall_approval_rate = ($total_applications > 0) ? round(($total_approved / $total_applications) * 100, 2) : 0;
    
    $pdf->Cell(0, 6, 'Total Applications: ' . $total_applications, 0, 1);
    $pdf->Cell(0, 6, 'Approved Applications: ' . $total_approved, 0, 1);
    $pdf->Cell(0, 6, 'Rejected Applications: ' . $total_rejected, 0, 1);
    $pdf->Cell(0, 6, 'Pending Applications: ' . $total_pending, 0, 1);
    $pdf->Cell(0, 6, 'Overall Approval Rate: ' . $overall_approval_rate . '%', 0, 1);
    
    // Output PDF
    $pdf->Output('leave_analytics_report.pdf', 'D');
    exit();
}

// Build the SQL query with filters for the main page
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

// Fetch department-wise statistics
$report_sql = "SELECT department, 
                      COUNT(*) AS total_applications, 
                      SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved, 
                      SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected 
               FROM leave_applications 
               GROUP BY department";
$report_stmt = $conn->prepare($report_sql);
$report_stmt->execute();
$department_reports = $report_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>VC Dashboard</title>
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
        .logout-btn {
            background: #c40d0d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }
        .logout-btn:hover {
            background: #660000;
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
        .chart-row {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .chart-card {
            width: 48%;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .download-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        .download-btn:hover {
            background: #45a049;
        }
    </style>
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div class="header">
        <img src="image.png" alt="PTU Logo">
        <h1>PTU VC Portal<br>Puducherry Technological University</h1>
    </div>

    <div class="sub-header">
        VC Dashboard
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
        
        <!-- Department-wise Reports -->
        <div class="report-section">
            <h2>Department-wise Reports</h2>
            
            <!-- Download PDF Button -->
            <a href="vc_dashboard.php?<?php echo http_build_query([
                'year' => $year_filter,
                'department' => $department_filter,
                'leave_type' => $leave_type_filter,
                'download_pdf' => true
            ]); ?>" class="download-btn">
                Download Analytics Report (PDF)
            </a>
            
            <!-- Bar Chart for Department Applications -->
            <div class="chart-container">
                <canvas id="departmentChart"></canvas>
            </div>
            
            <!-- Additional charts -->
            <div class="chart-row">
                <div class="chart-card">
                    <h3>Application Status Distribution</h3>
                    <canvas id="statusPieChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <h3>Department Approval Rates (%)</h3>
                    <canvas id="approvalRateChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <button class="logout-btn" onclick="window.location.href='vc_landing.php'">Back</button>

    <!-- Footer -->
    <div class="footer">
        Maintained by Students of PTU<br>
        Puducherry Technological University, Puducherry - 605014
    </div>

    <script>
        function filterApplications() {
            const year = document.getElementById("year").value;
            const department = document.getElementById("department").value;
            const leave_type = document.getElementById("leave_type").value;
            window.location.href = `vc_dashboard.php?year=${year}&department=${department}&leave_type=${leave_type}`;
        }
        
        // Chart.js for Department-wise Reports
        const departmentReports = <?php echo json_encode($department_reports); ?>;
        const departments = departmentReports.map(report => report.department);
        const totalApplications = departmentReports.map(report => report.total_applications);
        const approvedApplications = departmentReports.map(report => report.approved);
        const rejectedApplications = departmentReports.map(report => report.rejected);
        
        // Calculate total numbers for pie chart
        let totalApproved = 0;
        let totalRejected = 0;
        let totalPending = 0;
        
        departmentReports.forEach(report => {
            totalApproved += parseInt(report.approved);
            totalRejected += parseInt(report.rejected);
            const pending = report.total_applications - report.approved - report.rejected;
            totalPending += pending;
        });
        
        // Calculate approval rates for each department
        const approvalRates = departmentReports.map(report => {
            if (report.total_applications > 0) {
                return ((report.approved / report.total_applications) * 100).toFixed(1);
            }
            return 0;
        });

        // Bar Chart for Department Applications
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
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Applications by Department',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });
        
        // Pie Chart for Application Status
        const pieCtx = document.getElementById('statusPieChart').getContext('2d');
        const statusPieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Approved', 'Rejected', 'Pending'],
                datasets: [{
                    data: [totalApproved, totalRejected, totalPending],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 205, 86, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 205, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Approval Rate Chart
        const rateCtx = document.getElementById('approvalRateChart').getContext('2d');
        const approvalRateChart = new Chart(rateCtx, {
            type: 'bar',
            data: {
                labels: departments,
                datasets: [{
                    label: 'Approval Rate (%)',
                    data: approvalRates,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    </script>
</body>
</html>