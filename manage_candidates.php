<?php
session_start();

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
    header("Location: manage_candidates");
    exit();
}

// Check Database File Connection
$db_file = '../db_config.php'; 
if (file_exists($db_file)) {
    include($db_file);
} else {
    die("<div style='color:red; padding:20px;'>Error: Configuration file not found!</div>");
}

// --- 3. DELETE PROCESS LOGIC ---
if (isset($_GET['delete_id'])) {
    $id_to_delete = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // 1. Fetch data dulu untuk check ada ke tak
    $query_info = mysqli_query($conn, "SELECT name, image FROM candidates WHERE id = '$id_to_delete'");
    $candidate_data = mysqli_fetch_assoc($query_info);

    if ($candidate_data) { // Pastikan data dijumpai sebelum akses array
        $candidate_name = $candidate_data['name'];
        $image_name = $candidate_data['image'];

        // 2. Jalankan DELETE SQL
        $sql_delete = "DELETE FROM candidates WHERE id = '$id_to_delete'";
        
        if (mysqli_query($conn, $sql_delete)) {
            
           // --- 3. DELETE PROCESS LOGIC ---
if (isset($_GET['delete_id'])) {
    $id_to_delete = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // Ambil info calon untuk tujuan log & padam gambar
    $query_info = mysqli_query($conn, "SELECT name, image FROM candidates WHERE id = '$id_to_delete'");
    $candidate_data = mysqli_fetch_assoc($query_info);

    if ($candidate_data) {
        $candidate_name = $candidate_data['name'];
        $image_name = $candidate_data['image'];

        // Mulakan proses DELETE
        $sql_delete = "DELETE FROM candidates WHERE id = '$id_to_delete'";
        
        if (mysqli_query($conn, $sql_delete)) {
            // Padam gambar jika bukan default
            if (!empty($image_name) && $image_name != 'default.png') {
                $file_path = "../assets/img/" . $image_name;
                if (is_file($file_path)) { unlink($file_path); }
            }

            // --- 4. AUDIT LOG (VERSI SELAMAT - ELAK ERROR) ---
            $admin_email = $_SESSION['admin_email'] ?? ''; 
            $log_details = "Deleted candidate: $candidate_name (ID: $id_to_delete)";

            // Kita semak dulu: Adakah emel admin ini wujud dalam jadual users?
            $check_user = mysqli_query($conn, "SELECT user_email FROM users WHERE user_email = '$admin_email'");
            
            if (!empty($admin_email) && mysqli_num_rows($check_user) > 0) {
                // Jika emel SAH dan wujud, baru masuk log
                $log_sql = "INSERT INTO audit_logs (user_email, action, details) VALUES ('$admin_email', 'DELETE_CANDIDATE', '$log_details')";
                mysqli_query($conn, $log_sql);
            } else {
                // Jika emel tak sah, kita tulis log ke file error_log PHP sahaja (tak ganggu database)
                error_log("Audit log skipped: Admin email '$admin_email' not found in users table.");
            }

            echo "<script>alert('Candidate successfully deleted!'); window.location='manage_candidates.php';</script>";
            exit();
        }
    }
}
        }
    }
}
    

// 5. FETCH ALL CANDIDATES
$query = "SELECT * FROM candidates ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates | UPTM VOTING SYSTEM</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .candidate-img { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 2px solid #e2e8f0; }
        .table-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; color: #475569; text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; }
        .btn-edit { color: #3b82f6; text-decoration: none; font-weight: 600; }
        .btn-del { color: blue; text-decoration: none; font-weight: 600; }
        .btn-add { background: #3b82f6; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        .btn-add:hover { background: #2563eb; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="section-header">
                <h2 style="color: #1e293b;">Manage Candidates 🗳️</h2>
                <p style="color: #64748b;">View, edit, or remove candidates from the election list.</p>
            </div>

            <div class="action-bar" style="text-align: right; margin-bottom: 25px;">
                <a href="add_candidate.php" class="btn-add">+ Add New Candidate</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Candidate Information</th>
                            <th>Manifesto</th> 
                            <th>Student ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <img src="../assets/img/<?php echo !empty($row['image']) ? $row['image'] : 'default.png'; ?>" class="candidate-img">
                                </td>
                                
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                    <span style="color: #3b82f6; font-size: 12px; font-weight: 500;"><?php echo htmlspecialchars($row['faculty']); ?></span>
                                </td>
                                
                                <td style="color: #475569; max-width: 250px; font-size: 13px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($row['manifesto'] ?? 'No Manifesto Provided'); ?>
                                </td>
                                
                                <td style="font-family: 'Courier New', monospace; font-weight: bold; color: #1e293b;">
                                    <?php echo htmlspecialchars($row['student_id'] ?? 'N/A'); ?>
                                </td>
                                
                                <td>
                                    <a href="edit_candidate.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                    <span style="color: #cbd5e1; margin: 0 5px;">|</span>
                                    <a href="manage_candidates.php?delete_id=<?php echo $row['id']; ?>" 
                                       class="btn-del" 
                                       onclick="return confirm('Are you sure you want to delete this candidate? This action cannot be undone.')">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" align="center" style="padding: 40px; color: #94a3b8;">
                                    No candidate data found in the system.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>