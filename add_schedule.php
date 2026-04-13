<?php
session_start();
include('../db_config.php');

// --- 1. SESSION TIMEOUT LOGIC ---
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

$message = "";

if (isset($_POST['save_schedule'])) {
    $election_name = mysqli_real_escape_string($conn, $_POST['election_name']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Insert or Update logic
    $sql = "INSERT INTO elections (election_name, start_date, end_date) 
            VALUES ('$election_name', '$start_date', '$end_date')
            ON DUPLICATE KEY UPDATE 
            election_name='$election_name', start_date='$start_date', end_date='$end_date'";

    if (mysqli_query($conn, $sql)) {
        
        // --- 3. SAFE AUDIT LOG (Penyelesaian Ralat Fatal) ---
        $admin_email = $_SESSION['admin_email'] ?? '';
        
        // Semak jika emel admin wujud dalam jadual users untuk elak Foreign Key Error
        $check_admin = mysqli_query($conn, "SELECT user_email FROM users WHERE user_email = '$admin_email'");
        
        if (!empty($admin_email) && mysqli_num_rows($check_admin) > 0) {
            $log_details = "Set schedule for: $election_name";
            $stmt_log = $conn->prepare("INSERT INTO audit_logs (user_email, action, details) VALUES (?, 'SET_SCHEDULE', ?)");
            $stmt_log->bind_param("ss", $admin_email, $log_details);
            $stmt_log->execute();
        } else {
            // Jika admin tiada dalam database, log ke file sistem sahaja (tak ganggu DB)
            error_log("Audit log failed: Admin email '$admin_email' not found in users table.");
        }

        $message = "<div class='alert alert-success'>✅ Election schedule saved successfully!</div>";
    } else {
        $message = "<div class='alert alert-error'>❌ Error: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Election Schedule | UPTM</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="schedule-container">
    <h2>Set Election Schedule 📅</h2>
    <p class="instruction">Define the timeframe when students are allowed to cast their votes.</p>

    <?php echo $message; ?>

    <form method="POST">
        <label>Election Title</label>
        <input type="text" name="election_name" placeholder="e.g. SRC Election 2024" required>

        <label>Start Date & Time</label>
        <input type="datetime-local" name="start_date" required>

        <label>End Date & Time</label>
        <input type="datetime-local" name="end_date" required>

        <button type="submit" name="save_schedule" class="btn-save">Activate Schedule</button>
    </form>

    <a href="dashboard.php" class="back-btn">← Back to Admin Dashboard</a>
</div>

</body>
</html>