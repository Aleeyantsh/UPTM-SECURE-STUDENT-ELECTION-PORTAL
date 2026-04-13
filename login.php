<?php
session_start();
include('../db_config.php');

$error = "";

if (isset($_POST['login_candidate'])) {
    // ✅ FIX UTAMA: trim + strtolower supaya match dengan DB
    $student_id = strtolower(trim($_POST['student_id']));
    $password   = trim($_POST['password']);

    $sql = "SELECT id, name, password FROM candidates WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $data = $result->fetch_assoc();

        if (password_verify($password, $data['password'])) {
            $_SESSION['candidate_id']   = $data['id'];
            $_SESSION['candidate_name'] = $data['name'];
            $_SESSION['last_activity']  = time();
            header("Location: portal.php");
            exit();
        } else {
            $error = "Incorrect Student ID or Password!";
        }
    } else {
        $error = "Incorrect Student ID or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Login | UPTM Voting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f4f8; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .login-card { background: white; width: 100%; max-width: 420px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-top: 5px solid #3b82f6; overflow: hidden; }
        .card-header { padding: 32px 40px 24px; text-align: center; border-bottom: 1px solid #f1f5f9; }
        .card-header img { width: 70px; margin-bottom: 12px; }
        .card-header h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .card-header p { font-size: 13px; color: #94a3b8; }
        .card-body { padding: 28px 40px 36px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 7px; }
        .input-wrapper { position: relative; }
        .form-group input { width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-family: 'Inter', sans-serif; color: #1e293b; background: #f8fafc; transition: all 0.3s ease; outline: none; }
        .form-group input:focus { border-color: #3b82f6; background: white; box-shadow: 0 0 0 4px rgba(59,130,246,0.08); }
        .form-group input::placeholder { color: #cbd5e1; }
        .toggle-pw { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; color: #94a3b8; }
        .toggle-pw:hover { color: #3b82f6; }
        .error-msg { background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; border: 1px solid #fecaca; text-align: center; }
        .btn-submit { width: 100%; padding: 14px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; transition: opacity 0.3s ease, transform 0.2s ease; margin-top: 6px; }
        .btn-submit:hover { opacity: 0.92; transform: translateY(-1px); }
        .card-footer { text-align: center; margin-top: 20px; }
        .card-footer a { font-size: 13px; color: #3b82f6; text-decoration: none; font-weight: 500; }
        .card-footer a:hover { text-decoration: underline; }
        .card-footer p { font-size: 12px; color: #94a3b8; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header">
            <img src="/img/logo uptm.png" alt="UPTM Logo">
            <h2>Candidate Login 🔒</h2>
            <p>Access your candidate portal</p>
        </div>
        <div class="card-body">
            <?php if($error): ?>
                <div class="error-msg">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" placeholder="e.g. AM2408016651" value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="passwordInput" placeholder="Enter your password" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword()">👁️</button>
                    </div>
                </div>
                <button type="submit" name="login_candidate" class="btn-submit">Sign In</button>
            </form>
            <div class="card-footer">
                <a href="registration.php">← Register as a Candidate</a>
                <p>Having trouble? Please contact the admin.</p>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const btn = event.target;
            if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
            else { input.type = 'password'; btn.textContent = '👁️'; }
        }
    </script>
</body>
</html>