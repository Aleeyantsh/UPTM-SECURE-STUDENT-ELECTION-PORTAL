<?php
// Pastikan db_config.php di-include di atas sekali
include '../db_config.php'; 

// --- 1. SESSION TIMEOUT LOGIC (15 MINUTES) ---
// (Logic timeout sudah ada dalam db_config.php anda yang baru, 
// tapi jika mahu kekalkan di sini pun boleh)
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
    header("Location: login.php"); // Pastikan redirect ke login jika tak sah
    exit();
}

$id = $_GET['id'] ?? die("Error: ID not found in URL.");
$msg = "";

// 3. Fetch data securely (Guna Prepared Statement untuk elak SQL Injection)
$stmt_fetch = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("Error: Record with ID $id does not exist in the database.");
}

// 4. COLUMN LOGIC
$election_name = $row['election_name'] ?? "";
$start_val = $row['start_date'] ?? $row['start_time'] ?? ""; 
$end_val = $row['end_date'] ?? $row['end_time'] ?? "";

// 5. Format dates
$start_date = ($start_val != "") ? date('Y-m-d\TH:i', strtotime($start_val)) : "";
$end_date = ($end_val != "") ? date('Y-m-d\TH:i', strtotime($end_val)) : "";

// 6. Update Process
if (isset($_POST['update_schedule'])) {
    $name = mysqli_real_escape_string($conn, $_POST['election_name']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    // Gunakan Prepared Statement untuk UPDATE
    $sql_update = "UPDATE elections SET election_name=?, start_date=?, end_date=? WHERE id=?";
    $stmt_upd = $conn->prepare($sql_update);
    $stmt_upd->bind_param("sssi", $name, $start, $end, $id);

    if ($stmt_upd->execute()) {
        
        // --- 7. FIX: GUNA FUNGSI write_log UNTUK ELAK FATAL ERROR ---
        // Fungsi ini akan automatik semak session email dan handle Foreign Key
        $log_details = "Updated election schedule: $name (ID: $id)";
        write_log($conn, 'UPDATE_SCHEDULE', $log_details);

        echo "<script>alert('Schedule successfully updated!'); window.location='schedule.php';</script>";
        exit();
    } else {
        $msg = "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule | UPTM SECURE</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="card">
        <h2>Update Schedule 📅</h2>
        <span class="record-id">RECORD ID: #<?php echo htmlspecialchars($id); ?></span>

        <?php if($msg): ?>
            <div class="error-box"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Election Name</label>
                <input type="text" name="election_name" value="<?php echo htmlspecialchars($election_name); ?>" required>
            </div>

            <div class="form-group">
                <label>Start Date & Time</label>
                <input type="datetime-local" name="start_date" value="<?php echo $start_date; ?>" required>
            </div>

            <div class="form-group">
                <label>End Date & Time</label>
                <input type="datetime-local" name="end_date" value="<?php echo $end_date; ?>" required>
            </div>

            <button type="submit" name="update_schedule" class="btn-save">Save Changes</button>
            <a href="schedule.php" class="cancel-link">Cancel and Return</a>
        </form>
    </div>
</body>
</html>