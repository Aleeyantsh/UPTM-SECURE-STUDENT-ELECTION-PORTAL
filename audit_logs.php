<?php
session_start();
// Connect to database
$db_file = '../db_config.php'; 

if (file_exists($db_file)) {
    include($db_file);
} else {
    die("Error: Configuration file not found!");
}

// Fetch audit logs (newest at the top)
$query = "SELECT * FROM audit_logs ORDER BY timestamp DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs | UPTM VOTING SYSTEM</title>
    <link rel="stylesheet" href="style.css"> <link rel="stylesheet" href="audit_style.css"> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="section-header">
            <h2>System Audit Logs 📜</h2>
            <p>Monitoring administrative actions and security events.</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Admin User</th>
                        <th>Action Type</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) { 
                            // Determine badge color
                            $action = $row['action'];
                            $badgeClass = 'badge-default';
                            
                            if(strpos($action, 'ADD') !== false || strpos($action, 'UPDATE') !== false) $badgeClass = 'badge-update';
                            elseif(strpos($action, 'LOGIN') !== false) $badgeClass = 'badge-login';
                            elseif(strpos($action, 'DELETE') !== false) $badgeClass = 'badge-delete';
                    ?>
                    <tr>
                        <td style="color: #64748b; font-weight: 500;">
                            <?php echo date('d M Y', strtotime($row['timestamp'])); ?><br>
                            <small><?php echo date('h:i A', strtotime($row['timestamp'])); ?></small>
                        </td>
                        <td>
                            <span style="font-weight: 600;"><?php echo htmlspecialchars($row['user_email']); ?></span>
                        </td>
                        <td>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($action); ?>
                            </span>
                        </td>
                        <td style="line-height: 1.4; color: #475569;">
                            <?php echo htmlspecialchars($row['details']); ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding: 60px; color: #94a3b8;'>No activity logs found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>