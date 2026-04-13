<?php
session_start();

// Redirect back to login if the session is empty
if (!isset($_SESSION['voter_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Complete | UPTM SECURE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #f8fafc; 
            margin: 0; 
            font-family: 'Inter', sans-serif;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh;
        }
        .success-card {
            background: white;
            padding: 50px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background-color: #f0fdf4;
            color: #16a34a;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }
        h1 { color: #1e293b; margin-bottom: 10px; font-size: 24px; }
        p { color: #64748b; line-height: 1.6; margin-bottom: 30px; }
        
        .btn-home {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }
        .confetti { font-size: 30px; display: block; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="success-card">
        <div class="icon-circle">✓</div>
        <h1>Submission Successful!</h1>
        <p>
            Thank you, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>!<br>
            Your votes have been securely recorded in the UPTM Election system. Your contribution helps shape our future.
        </p>
        
        <a href="dashboard.php" class="btn-home">Return to Dashboard</a>
        
        <span class="confetti">🎉🗳️✨</span>
    </div>

</body>
</html>