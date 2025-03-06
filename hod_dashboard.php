<?php
session_start();
// Ensure only HODs can access this page
if ($_SESSION['role'] !== 'hod') {
    header("Location: login.php");
    exit();
}

require 'db.php'; // Include PDO database connection

// Fetch the HOD's department
$hod_department = $_SESSION['department'];

// Fetch leave applications for the HOD's department
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';
$sql = "SELECT * FROM leave_applications WHERE department = :department";
if (!empty($year_filter)) {
    $sql .= " AND year_of_study = :year";
}
$stmt = $conn->prepare($sql);
$params = ['department' => $hod_department];
if (!empty($year_filter)) {
    $params['year'] = $year_filter;
}
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>HOD Dashboard - <?php echo $hod_department; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>HOD Dashboard - <?php echo $hod_department; ?></h1>

        <!-- Year Filter -->
        <div class="filter-section">
            <label for="year">Filter by Year:</label>
            <select id="year" onchange="filterApplications()">
                <option value="">All Years</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
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
                    <th>Actions</th>
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
                    <td class="action-buttons">
                        <?php if ($application['status'] === 'Pending'): ?>
                            <button class="approve" onclick="approveApplication(<?php echo $application['id']; ?>)">Approve</button>
                            <button class="reject" onclick="rejectApplication(<?php echo $application['id']; ?>)">Reject</button>
                        <?php else: ?>
                            <span>No actions available</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function filterApplications() {
            const year = document.getElementById("year").value;
            window.location.href = `hod_dashboard.php?year=${year}`;
        }

        function approveApplication(id) {
            if (confirm("Are you sure you want to approve this application?")) {
                window.location.href = `approve_application.php?id=${id}`;
            }
        }

        function rejectApplication(id) {
            if (confirm("Are you sure you want to reject this application?")) {
                window.location.href = `reject_application.php?id=${id}`;
            }
        }
    </script>
</body>
</html>