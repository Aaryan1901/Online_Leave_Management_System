<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$teacher_email = $_SESSION['email'] ?? null;
if (!$teacher_email) {
    http_response_code(401);
    echo "Unauthorized: no email found.";
    exit();
}

// Process attendance marking if form submitted via POST
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = filter_input(INPUT_POST, 'app_id', FILTER_VALIDATE_INT);
    if (!$app_id) {
        $message = "Invalid Application ID.";
    } else {
        try {
            $check_sql = "SELECT 1 FROM od_attendance WHERE application_id = :app_id AND teacher_email = :email LIMIT 1";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute(['app_id' => $app_id, 'email' => $teacher_email]);

            if ($check_stmt->fetchColumn()) {
                $message = "Attendance already marked for this application.";
            } else {
                $insert_sql = "INSERT INTO od_attendance (application_id, teacher_email, marked_at) VALUES (:app_id, :email, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->execute(['app_id' => $app_id, 'email' => $teacher_email]);
                $message = "Attendance marked successfully.";
            }
        } catch (PDOException $e) {
            $message = "Database error occurred.";
            // error_log($e->getMessage()); // For debugging
        }
    }
}

// Get current month/year
$month = date('m');
$year = date('Y');

// Fetch attendance dates for the teacher for this month
try {
    $stmt = $conn->prepare("SELECT DATE(marked_at) AS marked_date FROM od_attendance WHERE teacher_email = :email AND MONTH(marked_at) = :month AND YEAR(marked_at) = :year");
    $stmt->execute(['email' => $teacher_email, 'month' => $month, 'year' => $year]);
    $marked_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $marked_dates = [];
}

// Function to draw calendar with highlighted attendance days
function draw_calendar($month, $year, $marked_dates) {
    $daysOfWeek = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDayOfMonth);
    $dayOfWeek = date('w', $firstDayOfMonth);
    
    $calendar = "<table border='1' cellpadding='10' cellspacing='0' style='width:100%; text-align:center;'>";
    $calendar .= "<tr><th colspan='7'>" . date('F Y', $firstDayOfMonth) . "</th></tr>";
    $calendar .= "<tr>";
    foreach ($daysOfWeek as $day) {
        $calendar .= "<th>{$day}</th>";
    }
    $calendar .= "</tr><tr>";
    
    // Blank cells before first day
    if ($dayOfWeek > 0) {
        $calendar .= str_repeat('<td></td>', $dayOfWeek);
    }

    for ($day = 1; $day <= $numberDays; $day++) {
        $currentDate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        $style = in_array($currentDate, $marked_dates) 
            ? "background-color: #4CAF50; color: white; font-weight: bold;" 
            : "";

        $calendar .= "<td style='{$style}'>{$day}</td>";

        if ((($day + $dayOfWeek) % 7) == 0) {
            $calendar .= "</tr><tr>";
        }
    }

    // Blank cells after last day
    $remaining = 7 - (($numberDays + $dayOfWeek) % 7);
    if ($remaining < 7) {
        $calendar .= str_repeat('<td></td>', $remaining);
    }

    $calendar .= "</tr></table>";
    return $calendar;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Marking & Calendar</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        table { border-collapse: collapse; margin: 20px auto; }
        th { background-color: #f2f2f2; }
        td { height: 50px; width: 50px; }
        .container { max-width: 600px; margin: 0 auto; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        form { text-align: center; margin-bottom: 40px; }
        input[type="number"] { padding: 8px; width: 150px; }
        input[type="submit"] { padding: 8px 16px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Mark Attendance</h2>
    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="number" name="app_id" placeholder="Application ID" required min="1" />
        <input type="submit" value="Mark Attendance" />
    </form>

    <h2>Attendance Calendar (<?php echo date('F Y'); ?>)</h2>
    <p style="text-align:center;">Green highlighted days show attendance marked.</p>

    <?php echo draw_calendar($month, $year, $marked_dates); ?>
</div>

</body>
</html>
