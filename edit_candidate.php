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

$message = "";

// Check if Candidate ID exists in URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    header("Location: manage_candidates.php");
    exit();
}

// Update Logic
if (isset($_POST['update_candidate'])) {
    $name = strtoupper(mysqli_real_escape_string($conn, $_POST['name'])); 
    $manifesto = mysqli_real_escape_string($conn, $_POST['manifesto']);
    
    // Fetch old image reference from database
    $get_old = mysqli_query($conn, "SELECT image FROM candidates WHERE id = '$id'");
    $old_data = mysqli_fetch_assoc($get_old);
    $old_image = $old_data['image'];

    $image_query = ""; 

    // Check for new image upload
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "../assets/img/"; 
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_query = ", image = '$file_name'";
            
            // Delete old image file if it exists
            if (!empty($old_image) && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }
        } else {
            $message = "<div class='error'>Failed to upload image to assets/img folder.</div>";
        }
    }

    if (empty($message)) {
        $sql = "UPDATE candidates SET name = '$name', manifesto = '$manifesto' $image_query WHERE id = '$id'";
        
        if (mysqli_query($conn, $sql)) {
            // --- AUDIT LOG (ENGLISH) ---
            $log_details = "Updated candidate profile: $name (ID: $id)";
            write_log($conn, "UPDATE_CANDIDATE", $log_details);

            header("Location: manage_candidates.php?status=updated");
            exit();
        } else {
            $message = "<div class='error'>SQL Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Fetch current data for the form
$result = mysqli_query($conn, "SELECT * FROM candidates WHERE id = '$id'");
$candidate = mysqli_fetch_assoc($result);

if (!$candidate) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2>Error: Candidate ID #$id not found!</h2>
            <a href='manage_candidates.php'>Return to List</a>
         </div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Candidate | UPTM Voting</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="edit-box">
    <h2>Edit Profile ✏️</h2>
    <p class="subtitle">Updating information for Candidate #<?php echo $id; ?></p>
    
    <?php echo $message; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Full Name (CAPSLOCK)</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($candidate['name']); ?>" required>
        
        <label>Manifesto / Vision</label>
        <textarea name="manifesto" rows="4" required><?php echo htmlspecialchars($candidate['manifesto']); ?></textarea>
        
        <label>Current Profile Photo</label>
        <div class="current-img-container">
            <?php if(!empty($candidate['image'])): ?>
                <img src="../assets/img/<?php echo $candidate['image']; ?>?t=<?php echo time(); ?>" class="current-img" id="img-preview">
            <?php else: ?>
                <p style="color: #94a3b8; font-size: 13px;">No image uploaded</p>
            <?php endif; ?>
        </div>
        
        <label>Replace Photo (Optional)</label>
        <input type="file" name="image" id="image-input" accept="image/*" style="border: none; padding: 0; margin-bottom: 10px;" onchange="previewImage(event)">
        <small style="color: #64748b; display: block; margin-bottom: 25px;">Keep empty if you don't want to change the photo.</small>
        
        <button type="submit" name="update_candidate" class="btn-update">Save All Changes</button>
    </form>
    
    <a href="manage_candidates.php" class="back-link">← Cancel & Return</a>
</div>

<script>
// Logic to show the new image instantly before saving
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('img-preview');
        output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>
</html>