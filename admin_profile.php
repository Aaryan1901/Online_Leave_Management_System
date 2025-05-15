<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

// Ensure only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Email configuration
$EMAIL_ADDRESS = "aaryan.m299@ptuniv.edu.in";
$EMAIL_PASSWORD = "pglx fhtx vgvt obkb";

function sendEmailNotification($to, $subject, $body) {
    global $EMAIL_ADDRESS, $EMAIL_PASSWORD;
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $EMAIL_ADDRESS;
        $mail->Password   = $EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom($EMAIL_ADDRESS, 'PTU Admin Portal');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    $admin_id = $_SESSION['user_id'];
    
    // Get request details with current email
    $sql = "SELECT pcr.*, u.email as current_email 
            FROM profile_change_requests pcr
            JOIN users u ON pcr.registration_number = u.registration_number
            WHERE pcr.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        $conn->beginTransaction();
        
        try {
            if ($action == 'approve') {
                // Update user profile
                $sql = "UPDATE users SET 
                        name = :name, 
                        email = :email 
                        WHERE registration_number = :reg_no";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'name' => $request['new_name'],
                    'email' => $request['new_email'],
                    'reg_no' => $request['registration_number']
                ]);
                
                // Update request status
                $sql = "UPDATE profile_change_requests SET 
                        status = 'approved', 
                        processed_date = NOW(), 
                        processed_by = :admin_id,
                        admin_notes = :notes
                        WHERE id = :request_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'admin_id' => $admin_id,
                    'notes' => $admin_notes,
                    'request_id' => $request_id
                ]);
                
                $conn->commit();
                $message = "Request approved and profile updated successfully";
                
                // Send approval email
                $subject = "PTU: Profile Change Request Approved";
                $body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #001f3f; color: white; padding: 10px; text-align: center; }
                            .content { padding: 20px; background-color: #f9f9f9; }
                            .footer { margin-top: 20px; font-size: 12px; color: #666; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Puducherry Technological University</h2>
                            </div>
                            <div class='content'>
                                <p>Dear Student,</p>
                                <p>Your profile change request has been <strong>approved</strong> by the administration.</p>
                                <p><strong>Updated Details:</strong></p>
                                <ul>
                                    <li>Name: {$request['new_name']}</li>
                                    <li>Email: {$request['new_email']}</li>
                                </ul>
                                <p><strong>Admin Notes:</strong> {$admin_notes}</p>
                                <p>You can now login with your updated credentials.</p>
                            </div>
                            <div class='footer'>
                                <p>This is an automated message. Please do not reply.</p>
                                <p>© ".date('Y')." PTU Admin Portal</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                sendEmailNotification($request['current_email'], $subject, $body);
                
            } elseif ($action == 'reject') {
                // Update request status
                $sql = "UPDATE profile_change_requests SET 
                        status = 'rejected', 
                        processed_date = NOW(), 
                        processed_by = :admin_id,
                        admin_notes = :notes
                        WHERE id = :request_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'admin_id' => $admin_id,
                    'notes' => $admin_notes,
                    'request_id' => $request_id
                ]);
                
                $conn->commit();
                $message = "Request rejected successfully";
                
                // Send rejection email
                $subject = "PTU: Profile Change Request Rejected";
                $body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #001f3f; color: white; padding: 10px; text-align: center; }
                            .content { padding: 20px; background-color: #f9f9f9; }
                            .footer { margin-top: 20px; font-size: 12px; color: #666; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Puducherry Technological University</h2>
                            </div>
                            <div class='content'>
                                <p>Dear Student,</p>
                                <p>Your profile change request has been <strong>rejected</strong> by the administration.</p>
                                <p><strong>Reason:</strong> {$admin_notes}</p>
                                <p>Your profile remains unchanged with the following details:</p>
                                <ul>
                                    <li>Name: {$request['current_name']}</li>
                                    <li>Email: {$request['current_email']}</li>
                                </ul>
                                <p>If you believe this was a mistake, please contact the admin office.</p>
                            </div>
                            <div class='footer'>
                                <p>This is an automated message. Please do not reply.</p>
                                <p>© ".date('Y')." PTU Admin Portal</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                sendEmailNotification($request['current_email'], $subject, $body);
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error processing request: " . $e->getMessage();
        }
    } else {
        $message = "Request not found";
    }
}

// Get all pending requests
$sql = "SELECT pcr.*, u.name as current_user_name 
        FROM profile_change_requests pcr
        JOIN users u ON pcr.registration_number = u.registration_number
        WHERE pcr.status = 'pending'
        ORDER BY pcr.request_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent processed requests
$sql = "SELECT pcr.*, u.name as current_user_name, 
        admin.name as admin_name
        FROM profile_change_requests pcr
        JOIN users u ON pcr.registration_number = u.registration_number
        LEFT JOIN users admin ON pcr.processed_by = admin.registration_number
        WHERE pcr.status IN ('approved', 'rejected')
        ORDER BY pcr.processed_date DESC
        LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->execute();
$processedRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Profile Change Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #001f3f;
            --secondary-color: #003366;
            --accent-color: #0074D9;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header img {
            height: 60px;
        }
        
        .header h1 {
            font-size: 1.8rem;
            text-align: center;
        }
        
        .sub-header {
            background-color: var(--secondary-color);
            color: white;
            font-weight: bold;
            padding: 12px;
            text-align: center;
            font-size: 1.2rem;
        }
        
        .main-container {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 1rem;
            margin-top: 40px;
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
            background-color: var(--secondary-color);
            color: white;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f1f1f1;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }
        
        .badge-pending {
            background-color: var(--warning-color);
            color: #212529;
        }
        
        .badge-approved {
            background-color: var(--success-color);
        }
        
        .badge-rejected {
            background-color: var(--danger-color);
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 5px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-approve {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-approve:hover {
            background-color: #218838;
        }
        
        .btn-reject {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #c82333;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 25px;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: modalopen 0.4s;
        }
        
        @keyframes modalopen {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
            transition: color 0.3s;
        }
        
        .close:hover {
            color: #333;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .section-title {
            margin-top: 30px;
            margin-bottom: 20px;
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .info-message {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 10px;
                padding: 15px 10px;
            }
            
            .header h1 {
                font-size: 1.4rem;
            }
            
            .modal-content {
                width: 90%;
                margin: 20% auto;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="ptu_logo.png" alt="PTU Logo">
        <h1>PTU Admin Portal<br>Puducherry Technological University</h1>
    </div>

    <div class="sub-header">
        Profile Change Request Management
    </div>

    <div class="main-container">
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error-message' : 'success-message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-clock"></i> Pending Profile Change Requests</h2>
            <?php if (count($pendingRequests) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Reg. No.</th>
                            <th>Current Name</th>
                            <th>Current Email</th>
                            <th>Requested Name</th>
                            <th>Requested Email</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRequests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['registration_number']); ?></td>
                                <td><?php echo htmlspecialchars($request['current_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['current_email']); ?></td>
                                <td><?php echo htmlspecialchars($request['new_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['new_email']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($request['request_date'])); ?></td>
                                <td>
                                    <button class="btn btn-approve" onclick="openApproveModal(<?php echo $request['id']; ?>)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-reject" onclick="openRejectModal(<?php echo $request['id']; ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No pending profile change requests</p>
                </div>
            <?php endif; ?>
        </div>

        <h3 class="section-title"><i class="fas fa-history"></i> Recently Processed Requests</h3>
        <div class="card">
            <?php if (count($processedRequests) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Reg. No.</th>
                            <th>Name Change</th>
                            <th>Email Change</th>
                            <th>Status</th>
                            <th>Processed Date</th>
                            <th>Processed By</th>
                            <th>Admin Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($processedRequests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['registration_number']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($request['current_name']); ?></strong> 
                                    <i class="fas fa-arrow-right"></i> 
                                    <strong><?php echo htmlspecialchars($request['new_name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($request['current_email']); ?> 
                                    <i class="fas fa-arrow-right"></i> 
                                    <?php echo htmlspecialchars($request['new_email']); ?>
                                </td>
                                <td>
                                    <?php if ($request['status'] == 'approved'): ?>
                                        <span class="badge badge-approved">
                                            <i class="fas fa-check-circle"></i> Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-rejected">
                                            <i class="fas fa-times-circle"></i> Rejected
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($request['processed_date'])); ?></td>
                                <td><?php echo htmlspecialchars($request['admin_name'] ?? 'System'); ?></td>
                                <td><?php echo htmlspecialchars($request['admin_notes'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No processed requests found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('approveModal')">&times;</span>
            <h3><i class="fas fa-check-circle"></i> Approve Profile Change Request</h3>
            <p>Are you sure you want to approve this profile change request?</p>
            <form method="POST">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="request_id" id="approve_request_id">
                <label for="approve_notes">Notes (optional):</label>
                <textarea name="admin_notes" id="approve_notes" placeholder="Add any notes about this approval..."></textarea>
                <div class="action-buttons">
                    <button type="button" class="btn btn-reject" onclick="closeModal('approveModal')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-approve">
                        <i class="fas fa-check"></i> Confirm Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            <h3><i class="fas fa-times-circle"></i> Reject Profile Change Request</h3>
            <p>Are you sure you want to reject this profile change request?</p>
            <form method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="request_id" id="reject_request_id">
                <label for="reject_notes">Reason for rejection (required):</label>
                <textarea name="admin_notes" id="reject_notes" placeholder="Please provide a reason for rejecting this request..." required></textarea>
                <div class="action-buttons">
                    <button type="button" class="btn btn-approve" onclick="closeModal('rejectModal')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-reject">
                        <i class="fas fa-ban"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>Administrator Portal - Puducherry Technological University</p>
        <p>Puducherry - 605014 | © <?php echo date('Y'); ?> All Rights Reserved</p>
    </div>

    <script>
        function openApproveModal(requestId) {
            document.getElementById('approve_request_id').value = requestId;
            document.getElementById('approveModal').style.display = 'block';
        }

        function openRejectModal(requestId) {
            document.getElementById('reject_request_id').value = requestId;
            document.getElementById('rejectModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>