<?php
session_start();
include '../db_config.php';

// 1. Session Timeout Logic
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?reason=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php"); // Betulkan redirect ke login
    exit();
}

$msg = "";

if (isset($_POST['add_voter'])) {
    // Ambil data dan tukar ke Huruf Besar (Uppercase)
    $student_id = strtoupper(mysqli_real_escape_string($conn, $_POST['student_id'])); 
    $full_name = strtoupper(mysqli_real_escape_string($conn, $_POST['full_name']));
    $user_email = mysqli_real_escape_string($conn, $_POST['user_email']); // Ganti 'email' ke 'user_email'
    
    $password_hashed = password_hash("123456", PASSWORD_DEFAULT);
    $status = 'Belum Mengundi'; 

    // --- 2. SEMAK DATA PENDUA (Gunakan user_email) ---
    $check_stmt = $conn->prepare("SELECT student_id, user_email FROM users WHERE student_id = ? OR user_email = ?");
    $check_stmt->bind_param("ss", $student_id, $user_email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $msg = "Ralat: ID Pelajar atau Emel ini sudah didaftarkan dalam sistem.";
    } else {
        // --- 3. JALANKAN INSERT (Gunakan nama kolum database yang betul) ---
        // Pastikan kolum 'user_email' dan 'student_id' wujud di phpMyAdmin
        $query = "INSERT INTO users (student_id, full_name, user_email, password, status, role) 
                  VALUES ('$student_id', '$full_name', '$user_email', '$password_hashed', '$status', 'voter')";

        if (mysqli_query($conn, $query)) {
            // --- 4. AUDIT LOG (SAFE VERSION) ---
            $admin_email = $_SESSION['admin_email'] ?? '';
            
            // Semak jika admin wujud sebelum log untuk elak Foreign Key Error
            $check_admin = $conn->query("SELECT user_email FROM users WHERE user_email = '$admin_email'");
            
            if (!empty($admin_email) && $check_admin->num_rows > 0) {
                $log_details = "Registered new voter: $full_name ($student_id)";
                $log_sql = "INSERT INTO audit_logs (user_email, action, details) VALUES ('$admin_email', 'ADD_VOTER', '$log_details')";
                mysqli_query($conn, $log_sql);
            }

            header("Location: manage_user.php?status=success");
            exit();
        } else {
            $msg = "System Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Voter | UPTM VOTING SYSTEM</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <h2>Add New Voter 👥</h2>
            <p>Register a student into the official voting database.</p>
        </div>

        <?php if ($msg != ""): ?>
            <div class="error-msg"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Student ID / Matrix No</label>
                <input type="text" name="id" placeholder="e.g. AM2408016651" required>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="e.g. NURIN QISTINA" required>
            </div>

            <div class="form-group">
                <label>Institutional Email</label>
                <input type="email" name="email" placeholder="student_id@student.uptm.edu.my" required>
            </div>

            <button type="submit" name="add_voter" class="btn-submit">Register Voter</button>
            <a href="manage_user.php" class="back-link">← Return to Voter List</a>
        </form>
    </div>

</body>
</html>