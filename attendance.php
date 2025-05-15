<?php
session_start();
require 'db.php';

// Redirect if not logged in as teacher
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Validate GET parameter
if (!isset($_GET['app_id']) || empty($_GET['app_id'])) {
    header("Location: teacher_dashboard.php");
    exit();
}

$app_id = intval($_GET['app_id']);

// Fetch leave application details
$stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = :id");
$stmt->execute(['id' => $app_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    header("Location: teacher_dashboard.php");
    exit();
}

// Handle form submission to mark attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If you want to handle a selected date from calendar, you can retrieve it here
    // $attendance_date = $_POST['attendance_date'] ?? null;

    // Mark attendance in leave_applications table (or better: insert into attendance table)
    $update = $conn->prepare("UPDATE leave_applications SET attendance_marked = 1 WHERE id = :id");
    $update->execute(['id' => $app_id]);

    $_SESSION['message'] = "Attendance marked successfully for " . htmlspecialchars($application['name']);
    header("Location: teacher_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Mark Attendance - <?php echo htmlspecialchars($application['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f8ff;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        h1 {
            color: #003366;
            margin-bottom: 10px;
            text-align: center;
        }
        .application-info {
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            width: 100%;
            max-width: 700px;
        }
        #calendar {
            max-width: 700px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            padding: 15px;
            margin-bottom: 15px;
        }
        .mark-btn {
            margin-top: 10px;
            display: block;
            width: 100%;
            max-width: 700px;
            background-color: #28a745;
            color: white;
            border: none;
            padding: 15px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
            transition: background-color 0.3s ease;
        }
        .mark-btn:hover {
            background-color: #218838;
        }
        .back-link {
            margin-top: 20px;
            font-size: 16px;
            text-decoration: none;
            color: #003366;
            align-self: flex-start;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h1>Mark Attendance for <?php echo htmlspecialchars($application['name']); ?></h1>

    <div class="application-info">
        <p><strong>Enrollment:</strong> <?php echo htmlspecialchars($application['enrollment']); ?></p>
        <p><strong>Year of Study:</strong> <?php echo htmlspecialchars($application['year_of_study']); ?></p>
        <p><strong>Leave From:</strong> <?php echo htmlspecialchars($application['from_date']); ?></p>
        <p><strong>Leave To:</strong> <?php echo htmlspecialchars($application['to_date']); ?></p>
        <p><strong>Reason:</strong> <?php echo htmlspecialchars($application['reason']); ?></p>
    </div>

    <div id="calendar"></div>

    <form method="POST" onsubmit="return confirm('Are you sure you want to mark attendance?');">
        <!-- Optional hidden input if you want to send the selected date -->
        <!-- <input type="hidden" name="attendance_date" id="attendance_date" /> -->
        <button type="submit" class="mark-btn">Mark Attendance</button>
    </form>

    <a href="teacher_dashboard.php" class="back-link">← Back to Teacher Dashboard</a>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                selectMirror: true,
                validRange: {
                    start: '<?php echo $application['from_date']; ?>',
                    end: '<?php echo $application['to_date']; ?>'
                },
                initialDate: '<?php echo $application['from_date']; ?>',
                dayMaxEvents: true,
                height: 'auto',
                events: [
                    {
                        title: 'Leave Period',
                        start: '<?php echo $application['from_date']; ?>',
                        end: '<?php echo date('Y-m-d', strtotime($application['to_date'] . ' +1 day')); ?>',
                        display: 'background',
                        color: '#ff9f89'
                    }
                ],
                select: function(info) {
                    alert('You selected date: ' + info.startStr);
                    // If you want to store selected date in hidden input:
                    // document.getElementById('attendance_date').value = info.startStr;
                }
            });

            calendar.render();
        });
    </script>
</body>
</html>
