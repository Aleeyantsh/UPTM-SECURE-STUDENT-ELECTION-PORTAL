<?php
session_start();
include '../db_config.php';

// --- 1. SESSION TIMEOUT LOGIC (15 MINUTES) ---
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?reason=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// --- 2. ADMIN LOGIN VERIFICATION ---
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 3. Fetch candidate info (name and image) before deletion for logging and cleanup
    $res = mysqli_query($conn, "SELECT name, image FROM candidates WHERE id = '$id'");
    $row = mysqli_fetch_assoc($res);
    
    if ($row) {
        $candidate_name = $row['name'];
        $image_file = $row['image'];

        // 4. Delete record from database
        $sql = "DELETE FROM candidates WHERE id = '$id'";
        
        if (mysqli_query($conn, $sql)) {
            // Delete image file from folder if it's not the default image
            if ($image_file != 'default.png' && !empty($image_file) && file_exists("../assets/img/" . $image_file)) {
                unlink("../assets/img/" . $image_file);
            }

            // --- 5. AUDIT LOG TRACKING ---
            // Recording who deleted which candidate and when
            $admin_user = $_SESSION['admin_user'] ?? 'Unknown Admin';
            $log_details = "Admin ($admin_user) deleted candidate: $candidate_name (ID: $id)";
            write_log($conn, "DELETE_CANDIDATE", $log_details);

            echo "<script>alert('Candidate successfully deleted!'); window.location='manage_candidates.php';</script>";
        } else {
            echo "<script>alert('Error during deletion: " . mysqli_error($conn) . "'); window.location='manage_candidates.php';</script>";
        }
    } else {
        echo "<script>alert('Candidate not found.'); window.location='manage_candidates.php';</script>";
    }
} else {
    header("Location: manage_candidates.php");
}
?>