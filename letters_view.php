<?php
session_start();
require 'db.php';


// Query to get student document counts
$sql = "SELECT 
            registration_number, 
            student_name, 
            COUNT(*) as document_count, 
            MAX(created_at) as last_submission_date
        FROM letter_table 
        GROUP BY registration_number, student_name
        ORDER BY last_submission_date DESC";
$stmt = $conn->query($sql);
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OD Letters Tracking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #c40d0d;
        }
        .ptu-logo {
            height: 80px;
        }
        h2 {
            color: #c40d0d;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #c40d0d;
            color: white;
            position: sticky;
            top: 0;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .view-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .view-btn:hover {
            background-color: #45a049;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .search-box {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .export-btn {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .export-btn:hover {
            background-color: #0b7dda;
        }
        .no-records {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="assets/ptu-logo.png" alt="PTU Logo" class="ptu-logo">
            <h2>OD Letters Tracking</h2>
        </div>

        <div class="search-container">
            <input type="text" id="searchInput" class="search-box" placeholder="Search by name or registration number..." onkeyup="searchTable()">
            <a href="export_letters.php" class="export-btn">Export to Excel</a>
        </div>

        <table id="lettersTable">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Registration Number</th>
                    <th>Number of Documents</th>
                    <th>Last Submission Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($letters) > 0): ?>
                    <?php foreach ($letters as $letter): ?>
                        <tr>
                            <td><?= htmlspecialchars($letter['student_name']) ?></td>
                            <td><?= htmlspecialchars($letter['registration_number']) ?></td>
                            <td><?= $letter['document_count'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($letter['last_submission_date'])) ?></td>
                            <td class="action-buttons">
                                <a href="view_letters.php?reg_no=<?= $letter['registration_number'] ?>" class="view-btn">View Documents</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-records">No OD letters found in the system</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toUpperCase();
            const table = document.getElementById("lettersTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                const tdName = tr[i].getElementsByTagName("td")[0];
                const tdRegNo = tr[i].getElementsByTagName("td")[1];
                
                if (tdName && tdRegNo) {
                    const txtValueName = tdName.textContent || tdName.innerText;
                    const txtValueRegNo = tdRegNo.textContent || tdRegNo.innerText;
                    
                    if (txtValueName.toUpperCase().indexOf(filter) > -1 || 
                        txtValueRegNo.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }
    </script>
</body>
</html>