<?php
session_start();
include('../db_config.php');

// --- 1. SESSION TIMEOUT LOGIC (15 MINUTES) ---
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?reason=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// --- 2. ADMIN ACCESS CHECK ---
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Set Malaysia timezone to ensure 'now' time is accurate
date_default_timezone_set('Asia/Kuala_Lumpur');

// Fetch election schedule data
$query = "SELECT * FROM elections ORDER BY start_date ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Schedule | UPTM VOTING SYSTEM</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .table-container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 15px; color: #475569; border-bottom: 2px solid #e2e8f0; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
        .btn-add { background: #3b82f6; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .btn-add:hover { background: #2563eb; }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: white; display: inline-block; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="section-header">
                <h2 style="color: #1e293b;">VOTING SCHEDULE 📅</h2>
                <p style="color: #64748b;">Automatically manage the start and end dates of election sessions.</p>
            </div>

            <div class="action-bar" style="margin-bottom: 25px; text-align: right;">
                <a href="add_schedule.php" class="btn-add">+ Set New Schedule</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Election Name</th>
                            <th>Start Date & Time</th>
                            <th>End Date & Time</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) { 
                                
                                // --- AUTOMATIC STATUS LOGIC ---
                                $now = time(); 
                                $start = strtotime($row['start_date']); 
                                $end = strtotime($row['end_date']); 

                                if ($now < $start) {
                                    $current_status = "Upcoming";
                                    $status_color = "blue"; // Amber/Yellow
                                } elseif ($now >= $start && $now <= $end) {
                                    $current_status = "Live/Active";
                                    $status_color = "#10b981"; // Green
                                } else {
                                    $current_status = "Ended/Closed";
                                    $status_color = "blue"; 
                                }
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['election_name']); ?></strong></td>
                            <td><?php echo date('d M Y, h:i A', $start); ?></td>
                            <td><?php echo date('d M Y, h:i A', $end); ?></td>
                            <td>
                                <span class="status-badge" style="background: <?php echo $status_color; ?>;">
                                    <?php echo $current_status; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_schedule.php?id=<?php echo $row['id']; ?>" style="color: #3b82f6; text-decoration: none; font-weight: 600;">UPDATE</a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 50px; color: #94a3b8;'>No election schedules found in the system.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>