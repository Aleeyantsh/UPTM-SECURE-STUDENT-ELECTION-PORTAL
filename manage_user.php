<?php
session_start();

// 1. Session Timeout Logic (15 Minutes)
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?reason=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// 2. Security Check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../db_config.php';

// --- DELETE PROCESS ---
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Ambil info admin dari session
    $admin_email = $_SESSION['admin_email'] ?? '';

    // --- PROSES DELETE ---
    // Kita jalankan delete dulu
    if ($conn->query("DELETE FROM users WHERE id = '$id'")) {
        
        // --- PROSES AUDIT LOG (SAFE VERSION) ---
        // Semak jika email admin wujud dalam table users untuk elak ralat Foreign Key
        if (!empty($admin_email)) {
            $check_user = $conn->query("SELECT user_email FROM users WHERE user_email = '$admin_email'");
            
            if ($check_user && $check_user->num_rows > 0) {
                $log_details = "Deleted voter with ID: $id";
                $stmt_log = $conn->prepare("INSERT INTO audit_logs (user_email, action, details) VALUES (?, 'DELETE_VOTER', ?)");
                $stmt_log->bind_param("ss", $admin_email, $log_details);
                $stmt_log->execute();
            }
        }

        header("Location: manage_user.php?status=deleted");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch data
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voters | UPTM VOTING SYSTEM</title>
    <link rel="stylesheet" href="style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container" style="padding: 40px;">
            <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px;">
                <div>
                    <h1>Voter Management 👥</h1>
                    <p style="color: #94a3b8;">List of registered voters in the system</p>
                </div>
                <a href="add_voter.php" class="btn-add" style="background:blue; padding:12px 24px; border-radius:8px; text-decoration:none; color:white; font-weight:bold;">
                    + Add New Voter
                </a>
            </div>

            <table style="width:100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <thead style="background: #f8fafc; text-align: left;">
                    <tr style="color: #64748b; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em;">
                        <th style="padding: 15px;">Student ID</th>
                        <th style="padding: 15px;">Full Name</th>
                        <th style="padding: 15px;">Status</th>
                        <th style="padding: 15px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr style="border-top: 1px solid #f1f5f9;">
                            <td style="padding: 15px; color: #1e293b;">
                                <?php 
                                    $display_id = !empty($row['student_id']) ? $row['student_id'] : ($row['email'] ?? 'No ID');
                                    echo htmlspecialchars($display_id); 
                                ?>
                            </td>
                            <td style="padding: 15px; font-weight: 500; color: #1e293b;">
                                <?php echo htmlspecialchars($row['full_name'] ?? 'No Name'); ?>
                            </td>
                            <td class="status-cell" style="padding: 15px;">
                                <?php 
                                    // FIXED LOGIC: Checks for the string 'Voted'
                                    if (trim($row['status']) === 'Voted'): 
                                ?>
                                    <span class="badge" style="background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                        VOTED
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: #f1f5f9; color: #64748b; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                        NOT VOTED
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <a href="manage_user.php?delete=<?php echo $row['id']; ?>" 
                                   style="color:#ef4444; text-decoration:none; font-size: 14px; font-weight: 600;"
                                   onclick="return confirm('Are you sure you want to delete this voter?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 60px; color: #94a3b8;">
                                No voter data found in the system.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>