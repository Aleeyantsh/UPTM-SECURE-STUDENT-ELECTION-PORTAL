<?php
include('../db_config.php'); 

// 1. Session check yang selamat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 2. SESSION TIMEOUT LOGIC ---
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?reason=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// --- 3. ADMIN ACCESS CHECK ---
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['add_candidate'])) {
    // Gunakan Prepared Statements untuk elak SQL Injection
    $name = strtoupper($_POST['name']);
    $faculty = strtoupper($_POST['faculty']);
    $manifesto = $_POST['manifesto'];
    
    $target_dir = "../assets/img/"; 
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        
        // Guna Prepared Statement untuk INSERT Candidate
        $stmt = $conn->prepare("INSERT INTO candidates (name, faculty, image, manifesto, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("ssss", $name, $faculty, $file_name, $manifesto);
        
        if ($stmt->execute()) {
            // --- 4. FIX FOREIGN KEY ERROR (AUDIT LOG) ---
            // Ambil emel dari session, jika tiada, kita TIDAK boleh letak 'Admin'
            // Kita mesti pastikan emel ini wujud dalam table users
            $admin_email = $_SESSION['admin_email'] ?? null;

            if ($admin_email) {
                $log_details = "Registered candidate: $name ($faculty)";
                // Gunakan fungsi write_log yang kita dah buat dalam db_config
                write_log($conn, 'ADD_CANDIDATE', $log_details);
                $message = "<div class='alert alert-success'>✅ Candidate successfully registered!</div>";
            } else {
                // Jika emel session tiada, candidate tetap masuk, tapi log guna sistem amaran
                $message = "<div class='alert alert-success'>✅ Registered, but Session Email missing for Log.</div>";
            }

        } else {
            $message = "<div class='alert alert-error'>❌ Database Error: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-error'>❌ Failed to upload profile image.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Candidate | UPTM SECURE</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .form-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 500px; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 14px; }
        input, textarea { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 14px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        button:hover { background: #2563eb; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add New Candidate 🗳️</h2>
    <p class="subtitle" style="color: #64748b; margin-bottom: 25px;">Assign a new candidate for the upcoming election.</p>
    
    <?php echo $message; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Candidate Full Name</label>
        <input type="text" name="name" placeholder="Full Name" required>

        <label>Faculty</label>
        <input type="text" name="faculty" placeholder="E.g., FCOM" required>
        
        <label>Manifesto / Vision</label>
        <textarea name="manifesto" rows="3" placeholder="Candidate mission..." required></textarea>
        
        <label>Profile Image</label>
        <div id="preview-container" style="margin-bottom: 15px; display: none; text-align: center;">
            <img id="image-preview" src="#" alt="Preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #3b82f6;">
        </div>
        <input type="file" name="image" id="image-input" accept="image/*" required onchange="previewImage(event)">
        
        <button type="submit" name="add_candidate">Register Candidate</button>
    </form>
    
    <a href="manage_candidates.php" class="back-link">← Back to Manage Candidates</a>
</div>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('image-preview');
        var container = document.getElementById('preview-container');
        output.src = reader.result;
        container.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>
</html>