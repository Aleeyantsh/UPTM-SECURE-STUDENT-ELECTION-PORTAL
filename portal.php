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

// --- 2. AUTHENTICATION CHECK ---
if (!isset($_SESSION['candidate_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch live results from the database
$query = "SELECT c.*, COUNT(v.id) AS real_votes 
          FROM candidates c 
          LEFT JOIN votes v ON c.id = v.candidate_id 
          GROUP BY c.id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Portal | UPTM SECURE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <meta http-equiv="refresh" content="30">
    <style>
        body { 
            background-color: #f8fafc; margin: 0; font-family: 'Inter', sans-serif;
            display: flex; justify-content: center; min-height: 100vh;
        }
        .portal-container { width: 100%; max-width: 1200px; padding: 40px 20px; text-align: center; }
        .candidate-grid { display: flex; gap: 25px; justify-content: center; flex-wrap: wrap; margin-top: 30px; }
        
        .candidate-card { 
            background: white; padding: 30px; border-radius: 20px; 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); width: 300px;
            transition: transform 0.2s; position: relative;
        }
        .candidate-card:hover { transform: translateY(-5px); }

        .candidate-img {
            width: 120px; height: 120px; border-radius: 50%; 
            object-fit: cover; border: 4px solid #3b82f6; margin-bottom: 15px;
        }

        .btn-edit-self {
            display: block; background: #3b82f6; color: white;
            padding: 12px; border-radius: 8px; text-decoration: none;
            font-size: 14px; font-weight: bold; margin: 15px 0;
            transition: background 0.3s;
        }
        .btn-edit-self:hover { background: #2563eb; }

        .vote-count-badge {
            background: #f0fdf4; color: #166534; padding: 8px 20px;
            border-radius: 10px; font-weight: bold; border: 1px solid #bbf7d0;
            display: inline-block; margin-top: 5px;
        }

        /* Added a style for the "Hidden" message */
        .vote-hidden-msg {
            color: #94a3b8; font-size: 12px; font-weight: 500;
            padding: 8px 20px; display: inline-block; margin-top: 5px;
        }

        .live-indicator {
            display: inline-flex; align-items: center; color: #ef4444;
            font-size: 12px; font-weight: bold; margin-bottom: 10px;
        }
        .dot { height: 8px; width: 8px; background-color: #ef4444; border-radius: 50%; display: inline-block; margin-right: 5px; animation: blink 1s infinite; }

        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }

        .logout-btn {
            margin-top: 50px; display: inline-block; color: #ef4444; 
            text-decoration: none; font-weight: bold; padding: 12px 25px; 
            border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2;
            transition: all 0.2s;
        }
        .logout-btn:hover { background: #fee2e2; }
    </style>
</head>
<body>

    <div class="portal-container">
        <div class="live-indicator">
            <span class="dot"></span> LIVE VOTE TRACKING
        </div>
        <h2 style="font-size: 28px; color: #1e293b; text-transform: uppercase;">
            Welcome, <?php echo htmlspecialchars($_SESSION['candidate_name'] ?? 'Candidate'); ?>!
        </h2>
        <p style="color: #64748b;">Monitor election progress in real-time</p>

        <div class="candidate-grid">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="candidate-card">
                <img src="../assets/img/<?php echo !empty($row['image']) ? $row['image'] : 'default.png'; ?>" class="candidate-img" alt="Profile">
                
                <h3 style="margin: 10px 0; color: #1e293b;"><?php echo htmlspecialchars($row['name']); ?></h3>
                <p style="color: #3b82f6; font-weight: bold; margin-bottom: 20px; font-size: 14px;">
                    <?php echo htmlspecialchars($row['faculty']); ?>
                </p>
                
                <?php if($row['id'] == $_SESSION['candidate_id']): ?>
                    <a href="edit_profile.php" class="btn-edit-self">EDIT MY PROFILE</a>
                    
                    <div class="vote-count-badge">
                        Current Votes: <?php echo number_format($row['real_votes']); ?>
                    </div>
                <?php else: ?>
                    <div style="height: 65px; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #cbd5e1; font-size: 12px;">Candidate Profile Locked</span>
                    </div> 

                    <div class="vote-hidden-msg">
                        <span style="font-style: italic;">Votes Hidden</span>
                    </div>
                <?php endif; ?>

            </div>
            <?php endwhile; ?>
        </div>
        
        <a href="logout.php" class="logout-btn">Secure Logout</a>
    </div>

</body>
</html>