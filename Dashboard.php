<?php
// =============================================
// SESSION & AUTH CHECK
// =============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_config.php');

// Auth check - db_config.php dah handle timeout & last_activity
if (!isset($_SESSION['voter_id'])) {
    header("Location: login.php");
    exit();
}

$voter_id = (int) $_SESSION['voter_id'];

// =============================================
// FETCH VOTER DATA
// =============================================
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$display_name = $user['full_name'] ?? ($_SESSION['full_name'] ?? 'Voter');

// =============================================
// FLASH MESSAGE (dari submit_vote.php)
// =============================================
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// =============================================
// KIRA JUMLAH UNDI
// =============================================
$stmt_count = $conn->prepare(
    "SELECT COUNT(*) as total FROM votes WHERE voter_id = ?"
);
$stmt_count->bind_param("i", $voter_id);
$stmt_count->execute();
$votes_cast = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$remaining_quota = 5 - $votes_cast;

// =============================================
// ELECTION INFORMATION
// =============================================
date_default_timezone_set('Asia/Kuala_Lumpur');
$now = date('Y-m-d H:i:s');

$stmt_election = $conn->prepare(
    "SELECT election_name, start_date, end_date FROM elections LIMIT 1"
);
$stmt_election->execute();
$election = $stmt_election->get_result()->fetch_assoc();
$stmt_election->close();

// Semak election aktif
$is_active = false;
if ($election) {
    $is_active = ($now >= $election['start_date'] && $now <= $election['end_date']);
}

$can_vote = ($election && $remaining_quota > 0 && $is_active);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UPTM VOTING SYSTEM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Inter', sans-serif; 
            background: #f1f5f9; 
            display: flex; 
        }

        .main-content { 
            margin-left: 240px; 
            flex: 1; 
            padding: 40px; 
            min-height: 100vh; 
        }

        /* ---- FLASH MESSAGE ---- */
        .flash-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-weight: 500;
            font-size: 14px;
        }

        .flash-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-weight: 500;
            font-size: 14px;
        }

        /* ---- CARD ---- */
        .card { 
            background: white; 
            padding: 35px; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07); 
            max-width: 860px; 
            margin: auto;
            border: 1px solid #e2e8f0;
        }

        .welcome-text { 
            font-size: 22px; 
            color: #1e293b; 
            margin-bottom: 24px; 
            font-weight: 700; 
            padding-bottom: 16px;
            border-bottom: 2px solid #f1f5f9; 
        }

        /* ---- ELECTION INFO ---- */
        .election-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 14px 20px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1e40af;
        }

        /* ---- QUOTA BOX ---- */
        .quota-box { 
            background: #fafafa; 
            border: 1px solid #e2e8f0; 
            padding: 20px 24px; 
            border-radius: 12px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin: 20px 0; 
        }

        .quota-label { font-size: 15px; font-weight: 600; color: #1e293b; }
        .quota-sub   { font-size: 13px; color: #64748b; margin-top: 4px; }

        .quota-number { 
            font-size: 34px; 
            font-weight: 800; 
            color: #3b82f6; 
        }

        /* Progress bar */
        .progress-bar-wrap {
            background: #e2e8f0;
            border-radius: 99px;
            height: 10px;
            margin-top: 14px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            transition: width 0.4s ease;
        }

        /* ---- ACTIONS ---- */
        .action-area { text-align: center; margin-top: 28px; }

        .btn-vote { 
            background: #1d4ed8; 
            color: white; 
            padding: 14px 36px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 15px;
            display: inline-block; 
            transition: all 0.2s; 
        }

        .btn-vote:hover { 
            background: #1e40af; 
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px rgba(29,78,216,0.3);
        }

        .status-success { 
            background: #f0fdf4; 
            color: #166534; 
            border: 1px solid #bbf7d0;
            padding: 16px 20px; 
            border-radius: 10px; 
            font-weight: 600;
            font-size: 15px;
        }

        .status-inactive { 
            background: #fef9c3; 
            color: #854d0e; 
            border: 1px solid #fde68a;
            padding: 16px 20px; 
            border-radius: 10px; 
            font-size: 14px;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="card">

        <!-- WELCOME -->
        <div class="welcome-text">
            👋 Welcome, <?php echo htmlspecialchars(strtoupper($display_name)); ?>!
        </div>

        <!-- FLASH MESSAGE -->
        <?php if ($flash): ?>
            <div class="flash-<?php echo htmlspecialchars($flash['type']); ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- ELECTION INFO -->
        <?php if ($election): ?>
            <div class="election-info">
                🗳️ <strong><?php echo htmlspecialchars($election['election_name']); ?></strong>
                &nbsp;|&nbsp;
                <?php echo date('d M Y, h:i A', strtotime($election['start_date'])); ?>
                &nbsp;→&nbsp;
                <?php echo date('d M Y, h:i A', strtotime($election['end_date'])); ?>
                &nbsp;|&nbsp;
                <strong><?php echo $is_active ? '🟢 Active' : '🔴 Inactive'; ?></strong>
            </div>
        <?php endif; ?>

        <!-- QUOTA BOX -->
        <div class="quota-box">
            <div>
                <div class="quota-label">Voting Quota Status</div>
                <div class="quota-sub">You are eligible to vote for up to 5 different candidates.</div>
                <div class="progress-bar-wrap" style="width: 220px;">
                    <div class="progress-bar-fill" 
                         style="width: <?php echo ($votes_cast / 5) * 100; ?>%;">
                    </div>
                </div>
            </div>
            <div class="quota-number">
                <?php echo $votes_cast; ?> / 5
            </div>
        </div>

        <!-- ACTION AREA -->
        <div class="action-area">
            <?php if ($can_vote): ?>
                <p style="color: #475569; margin-bottom: 16px; font-size: 14px;">
                    You have <strong><?php echo $remaining_quota; ?></strong> vote(s) remaining.
                </p>
                <a href="portal.php" class="btn-vote">
                    🗳️ CONTINUE VOTING (Vote <?php echo $votes_cast + 1; ?> of 5)
                </a>

            <?php elseif ($remaining_quota <= 0): ?>
                <div class="status-success">
                    ✅ You have successfully cast all 5 votes. Thank you for participating!
                </div>

            <?php else: ?>
                <div class="status-inactive">
                    ⏳ The election session is either not yet active or has already concluded.
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>