<?php
// =============================================
// SESSION & DB CONNECTION
// =============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Pastikan path ke db_config.php adalah betul
include('../db_config.php');

// =============================================
// PHPMAILER
// =============================================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/UPTM_Election_Portal/vendor/PHPMailer-master/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/UPTM_Election_Portal/vendor/PHPMailer-master/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/UPTM_Election_Portal/vendor/PHPMailer-master/src/SMTP.php';

// SMTP CREDENTIALS
$smtp_user = 'informationtechnology085@gmail.com';
$smtp_pass = 'onyn ucsh hpft tgrm';

$allowed_faculties  = ['FCOM', 'FESS', 'FBA'];
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];
$max_file_size      = 2 * 1024 * 1024; // 2MB

// =============================================
// REGISTRATION HANDLER
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_candidate'])) {

    // 1. CSRF VERIFICATION (Panggil function dari db_config)
    if(function_exists('verify_csrf_token')) {
        verify_csrf_token($_POST['csrf_token'] ?? '');
    }

    // 2. SANITIZE & VALIDATE INPUT
    $name       = strtoupper(trim($_POST['name'] ?? ''));
    $email      = strtolower(trim($_POST['email'] ?? '')); // Ini input dari user
    $student_id = strtolower(trim($_POST['student_id'] ?? ''));
    $faculty    = trim($_POST['faculty'] ?? '');
    $manifesto  = trim($_POST['manifesto'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($student_id) || empty($faculty) || empty($manifesto) || empty($password)) {
        die("All fields are required.");
    }

    // --- FIX 1: TUKAR 'email' KEPADA 'user_email' DALAM SEMAKAN PENDUA ---
    $check = $conn->prepare("SELECT id FROM candidates WHERE student_id = ? OR user_email = ?");
    $check->bind_param("ss", $student_id, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("Student ID or email is already registered.");
    }
    $check->close();

    // 3. VALIDATE FILE UPLOAD
    $image_name = "default.png";
    if (!empty($_FILES['image']['name'])) {
        $file_mime = mime_content_type($_FILES['image']['tmp_name']);
        if (in_array($file_mime, $allowed_mime_types) && $_FILES['image']['size'] <= $max_file_size) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../assets/img/" . $image_name);
        }
    }

    // 4. INSERT KE DATABASE
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // --- FIX 2: TUKAR 'email' KEPADA 'user_email' DALAM INSERT ---
    // Pastikan susunan kolum ini sama dengan table 'candidates' anda
    $stmt = $conn->prepare(
        "INSERT INTO candidates (name, user_email, student_id, password, faculty, manifesto, image)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssssss", $name, $email, $student_id, $hashed_password, $faculty, $manifesto, $image_name);

    if ($stmt->execute()) {
        
        // --- FIX 3: REKOD KE AUDIT LOG ---
        // Kita gunakan email calon sebagai pengidentifikasi log
        write_log($conn, "CANDIDATE_REG", "New candidate registered: $name ($email)");

        // 5. HANTAR EMAIL CONFIRMATION
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@uptmsecure.com', 'UPTM SECURE');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Candidate Registration Successful';
            $mail->Body    = "Hello <b>$name</b>, your registration for faculty <b>$faculty</b> is successful.";
            $mail->send();
        } catch (Exception $e) {
            error_log("Mail error: " . $mail->ErrorInfo);
        }

        echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
    } else {
        error_log("DB Error: " . $stmt->error);
        die("Registration failed. Please ensure you are already registered as a voter first.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Registration | UPTM Voting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }
        .reg-card {
            background: white; width: 100%; max-width: 520px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-top: 5px solid #3b82f6;
            overflow: hidden;
        }
        .card-header {
            padding: 32px 40px 24px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .card-header img   { width: 70px; margin-bottom: 12px; }
        .card-header h2    { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .card-header p     { font-size: 13px; color: #94a3b8; }
        .card-body         { padding: 28px 40px 36px; }
        .form-group        { margin-bottom: 18px; }
        .form-group label  {
            display: block; font-size: 12px; font-weight: 600;
            color: #475569; text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 7px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 12px 16px;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            color: #1e293b; background: #f8fafc;
            transition: all 0.3s ease; outline: none;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3b82f6; background: white;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.08);
        }
        .form-group select   { cursor: pointer; }
        .form-group textarea { resize: vertical; min-height: 110px; }
        .input-wrapper       { position: relative; }
        .toggle-pw {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            font-size: 16px; color: #94a3b8;
        }
        .toggle-pw:hover { color: #3b82f6; }
        .file-upload-wrapper input[type="file"] {
            padding: 10px 16px; cursor: pointer; color: #64748b;
        }
        .file-upload-wrapper input[type="file"]::file-selector-button {
            background: #3b82f6; color: white; border: none;
            padding: 6px 14px; border-radius: 6px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; margin-right: 12px;
        }
        .file-upload-wrapper input[type="file"]::file-selector-button:hover {
            background: #2563eb;
        }
        .hint {
            font-size: 11px; color: #94a3b8; margin-top: 4px;
        }
        .form-divider {
            border: none; border-top: 1px solid #f1f5f9; margin: 22px 0;
        }
        .section-label {
            font-size: 11px; font-weight: 700; color: #3b82f6;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;
        }
        .btn-submit {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700;
            font-family: 'Inter', sans-serif; cursor: pointer;
            transition: opacity 0.3s ease, transform 0.2s ease;
            margin-top: 6px;
        }
        .btn-submit:hover { opacity: 0.92; transform: translateY(-1px); }
        .back-link {
            display: block; text-align: center; margin-top: 16px;
            color: #94a3b8; font-size: 13px; text-decoration: none;
        }
        .back-link:hover { color: #1e293b; }
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
        @media (max-width: 560px) {
            .card-header, .card-body { padding-left: 24px; padding-right: 24px; }
        }
    </style>
</head>
<body>
<div class="reg-card">
    <div class="card-header">
        <img src="/img/logo uptm.png" alt="UPTM Logo">
        <h2>Candidate Registration 🗳️</h2>
        <p>UPTM Voting System — Fill in your details below</p>
    </div>
    <div class="card-body">

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

            <p class="section-label">Personal Information</p>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name"
                       placeholder="e.g. NUR ALEEYA NATASHA"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email"
                       placeholder="e.g. student@gmail.com"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id"
                       placeholder="e.g. AM2408016651"
                       maxlength="20" required>
                <p class="hint">⚠️ Student ID will be stored in lowercase automatically.</p>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="regPassword"
                           placeholder="Minimum 8 characters"
                           minlength="8" required>
                    <button type="button" class="toggle-pw"
                            onclick="togglePw()">👁️</button>
                </div>
            </div>

            <hr class="form-divider">
            <p class="section-label">Academic & Campaign Info</p>

            <div class="form-group">
                <label>Faculty</label>
                <select name="faculty" required>
                    <option value="">Select Faculty</option>
                    <option value="FCOM">FCOM (Faculty of Computing & Multimedia)</option>
                    <option value="FESS">FEESH (Faculty of Education, Social Science & Humanities)</option>
                    <option value="FBA">FABA (Faculty of Business & Accountancy)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Manifesto & Vision</label>
                <textarea name="manifesto"
                          placeholder="Share your vision and goals..."
                          maxlength="1000" required></textarea>
            </div>

            <div class="form-group">
                <label>Profile Photo</label>
                <div class="file-upload-wrapper">
                    <input type="file" name="image"
                           accept="image/jpeg,image/png,image/webp"
                           required>
                </div>
                <p class="hint">JPG, PNG, WEBP only. Max 2MB.</p>
            </div>

            <button type="submit" name="register_candidate"
                    class="btn-submit">Register Now</button>
            <a href="login.php" class="back-link">
                ← Already registered? Login here
            </a>

        </form>
    </div>
</div>

<script>
function togglePw() {
    const input = document.getElementById('regPassword');
    const btn   = event.target;
    if (input.type === 'password') {
        input.type    = 'text';
        btn.textContent = '🙈';
    } else {
        input.type    = 'password';
        btn.textContent = '👁️';
    }
}
</script>
</body>
</html>