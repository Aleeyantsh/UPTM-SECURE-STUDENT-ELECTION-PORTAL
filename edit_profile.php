<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_config.php');

$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?reason=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['candidate_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int) $_SESSION['candidate_id'];

$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$candidate = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$candidate) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['update'])) {

    // CSRF CHECK
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $manifesto = mysqli_real_escape_string($conn, $_POST['manifesto']);

    if (!empty($_FILES['image']['name'])) {

        // FIX: Validate jenis fail - elak upload fail berbahaya
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['flash'] = [
                'type'    => 'error',
                'message' => 'Invalid file type. Only JPG, PNG, and WEBP are allowed.'
            ];
            header("Location: edit_profile.php");
            exit();
        }

        // FIX: Had saiz fail - maksimum 2MB
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $_SESSION['flash'] = [
                'type'    => 'error',
                'message' => 'File size too large. Maximum is 2MB.'
            ];
            header("Location: edit_profile.php");
            exit();
        }

        $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . $id . '.' . strtolower($ext);
        $target     = "../assets/img/" . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Padam gambar lama kalau bukan default
            if (!empty($candidate['image']) && $candidate['image'] !== 'default.png') {
                $old = "../assets/img/" . basename($candidate['image']);
                if (file_exists($old)) unlink($old);
            }

            $sql  = "UPDATE candidates SET manifesto=?, image=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $manifesto, $image_name, $id);
        } else {
            $_SESSION['flash'] = [
                'type'    => 'error',
                'message' => 'Failed to upload image. Please try again.'
            ];
            header("Location: edit_profile.php");
            exit();
        }
    } else {
        $sql  = "UPDATE candidates SET manifesto=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $manifesto, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => 'Profile updated successfully!'
        ];
        header("Location: portal.php");
    } else {
        $_SESSION['flash'] = [
            'type'    => 'error',
            'message' => 'Failed to update profile. Please try again.'
        ];
        header("Location: edit_profile.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Profile | UPTM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #f1f5f9; font-family: 'Inter', sans-serif;
            display: flex; justify-content: center;
            align-items: center; min-height: 100vh;
        }
        .edit-card {
            background: white; width: 100%; max-width: 450px;
            padding: 40px; border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .edit-card h2 { color: #1e293b; font-size: 22px; margin-bottom: 24px; }

        .flash-success {
            background: #f0fdf4; color: #166534;
            border: 1px solid #bbf7d0;
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 20px; font-size: 13px;
        }
        .flash-error {
            background: #fef2f2; color: #991b1b;
            border: 1px solid #fecaca;
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 20px; font-size: 13px;
        }

        .img-preview-container {
            width: 120px; height: 120px;
            margin: 0 auto 24px; border-radius: 50%;
            overflow: hidden; border: 4px solid #3b82f6;
        }
        .img-preview-container img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .form-group { text-align: left; margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: 13px; font-weight: 600;
            color: #475569; margin-bottom: 6px;
        }
        input[type="file"] {
            width: 100%; padding: 10px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 13px; color: #64748b;
        }
        .file-hint {
            font-size: 11px; color: #94a3b8; margin-top: 4px;
        }
        textarea {
            width: 100%; height: 120px; padding: 12px;
            border: 1px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; color: #1e293b; resize: none;
            font-family: 'Inter', sans-serif;
        }
        textarea:focus { border-color: #3b82f6; outline: none; }
        .btn-save {
            background: #3b82f6; color: white; width: 100%;
            padding: 13px; border: none; border-radius: 10px;
            font-weight: 700; font-size: 15px; cursor: pointer;
            margin-top: 8px; transition: background 0.2s;
        }
        .btn-save:hover { background: #2563eb; }
        .btn-cancel {
            display: inline-block; margin-top: 16px;
            color: #64748b; text-decoration: none;
            font-size: 13px; font-weight: 500;
        }
        .btn-cancel:hover { color: #1e293b; }
    </style>
</head>
<body>
<div class="edit-card">
    <h2>Edit My Profile ✏️</h2>

    <!-- FLASH MESSAGE -->
    <?php
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    if ($flash):
    ?>
        <div class="flash-<?php echo htmlspecialchars($flash['type']); ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <!-- CSRF TOKEN -->
        <input type="hidden" name="csrf_token"
               value="<?php echo generate_csrf_token(); ?>">

        <div class="img-preview-container">
            <img src="../assets/img/<?php echo htmlspecialchars(basename($candidate['image'])); ?>"
                 alt="Profile Photo" id="img-preview">
        </div>

        <div class="form-group">
            <label>Update Profile Photo (Optional)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                   onchange="previewImage(event)">
            <p class="file-hint">JPG, PNG, WEBP only. Max 2MB.</p>
        </div>

        <div class="form-group">
            <label>My Manifesto & Vision</label>
            <textarea name="manifesto" placeholder="What is your promise to voters?"
                      required><?php echo htmlspecialchars($candidate['manifesto']); ?></textarea>
        </div>

        <button type="submit" name="update" class="btn-save">SAVE CHANGES</button>
    </form>

    <a href="portal.php" class="btn-cancel">← Cancel and go back</a>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('img-preview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</body>
</html>