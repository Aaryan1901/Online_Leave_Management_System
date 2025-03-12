<?php
session_start();
// Ensure only HODs can access this page
if ($_SESSION['role'] !== 'hod') {
    header("Location: index.php");
    exit();
}

require 'db.php'; // Include PDO database connection

// Fetch the HOD's department
$hod_department = $_SESSION['department'];

// Fetch filters from the URL
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';
$leave_type_filter = isset($_GET['leave_type']) ? $_GET['leave_type'] : '';

// Build the SQL query with filters
$sql = "SELECT * FROM leave_applications WHERE department = :department AND (status = 'Forwarded to HOD' OR status = 'Approved')";
$params = ['department' => $hod_department];

if (!empty($year_filter)) {
    $sql .= " AND year_of_study = :year";
    $params['year'] = $year_filter;
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
    <title>HOD Dashboard - <?php echo $hod_department; ?></title>
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
            max-width: 100%; /* Allow the container to take full width */
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-grow: 1;
            overflow-x: auto; /* Add horizontal scroll for small screens */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed; /* Ensure table columns have fixed width */
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
            word-wrap: break-word; /* Allow text to wrap within cells */
        }
        th {
            background-color: #c40d0d;
            color: #fff;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center; /* Center align buttons */
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
        .quota-warning {
            color: red;
            font-weight: bold;
        }
        /* Highlight animation */
        .highlight-approve {
            background-color: #d4edda; /* Light green background */
            transition: background-color 0.5s ease;
        }
        .highlight-reject {
            background-color: #f8d7da; /* Light red background */
            transition: background-color 0.5s ease;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="image.png" alt="PTU Logo">
        <h1>PTU HOD Portal<br>Puducherry Technological University</h1>
    </div>

    <div class="sub-header">
        HOD Dashboard - <?php echo $hod_department; ?>
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
                    <th style="width: 10%;">Name</th>
                    <th style="width: 10%;">Enrollment</th>
                    <th style="width: 10%;">Department</th>
                    <th style="width: 10%;">Year of Study</th>
                    <th style="width: 10%;">Leave Type</th>
                    <th style="width: 10%;">From Date</th>
                    <th style="width: 10%;">To Date</th>
                    <th style="width: 10%;">Reason</th>
                    <th style="width: 10%;">Parent Signature</th>
                    <th style="width: 10%;">Student Signature</th>
                    <th style="width: 10%;">Advisor Letter</th>
                    <th style="width: 10%;">HOD Letter</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Leave Quota</th>
                    <th style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                <?php
                // Check if the student has exceeded the leave quota
                $quota_exceeded = isset($application['days_availed']) && isset($application['leave_quota']) && 
                                 ($application['days_availed'] >= $application['leave_quota']);
                ?>
                <tr id="row-<?php echo $application['id']; ?>">
                    <td><?php echo $application['name']; ?></td>
                    <td><?php echo $application['enrollment']; ?></td>
                    <td><?php echo $application['department']; ?></td>
                    <td><?php echo isset($application['year_of_study']) ? $application['year_of_study'] : 'N/A'; ?></td>
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
                    <td><?php echo isset($application['status']) ? $application['status'] : 'Forwarded to HOD'; ?></td>
                    <td>
                        <?php if (isset($application['days_availed']) && isset($application['leave_quota'])): ?>
                            <?php echo $application['days_availed']; ?> / <?php echo $application['leave_quota']; ?>
                            <?php if ($quota_exceeded): ?>
                                <span class="quota-warning">(Quota Exceeded)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td class="action-buttons">
                        <?php if (!isset($application['status']) || $application['status'] === 'Forwarded to HOD'): ?>
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

    <div class="footer">
        <button class="back-btn" onclick="window.location.href='hod_landing.php'">Back to HOD Landing</button>
    </div>

    <script>
        function filterApplications() {
            const year = document.getElementById("year").value;
            const leave_type = document.getElementById("leave_type").value;
            window.location.href = `hod_dashboard.php?year=${year}&leave_type=${leave_type}`;
        }

        function approveApplication(id) {
            if (confirm("Are you sure you want to approve this application?")) {
                // Highlight the row
                const row = document.getElementById(`row-${id}`);
                row.classList.add('highlight-approve');

                // Disable the buttons
                const buttons = row.querySelectorAll('.approve, .reject');
                buttons.forEach(button => {
                    button.disabled = true;
                    button.textContent = 'Processing...';
                });

                // Redirect to approve_application.php
                setTimeout(() => {
                    window.location.href = `approve_application.php?id=${id}`;
                }, 1000); // Delay for animation
            }
        }

        function rejectApplication(id) {
            if (confirm("Are you sure you want to reject this application?")) {
                // Highlight the row
                const row = document.getElementById(`row-${id}`);
                row.classList.add('highlight-reject');

                // Disable the buttons
                const buttons = row.querySelectorAll('.approve, .reject');
                buttons.forEach(button => {
                    button.disabled = true;
                    button.textContent = 'Processing...';
                });

                // Redirect to reject_application.php
                setTimeout(() => {
                    window.location.href = `reject_application.php?id=${id}`;
                }, 1000); // Delay for animation
            }
        }
    </script>
</body>
</html>