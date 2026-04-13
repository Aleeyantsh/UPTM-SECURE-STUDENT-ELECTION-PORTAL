<?php
session_start();
include '../db_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// 1. Get General Stats
$total_voters = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];
$voted_count = $conn->query("SELECT COUNT(*) as t FROM users WHERE status = 'Voted'")->fetch_assoc()['t'];
$turnout_rate = ($total_voters > 0) ? ($voted_count / $total_voters) * 100 : 0;

// 2. Get Detailed Results & Determine Winner
$sql_results = "SELECT c.name, c.faculty, COUNT(v.id) as total_votes 
                FROM candidates c 
                LEFT JOIN votes v ON c.id = v.candidate_id 
                GROUP BY c.id 
                ORDER BY total_votes DESC";
$result_set = $conn->query($sql_results);

// Store results in an array to find the winner easily
$candidates = [];
while($row = $result_set->fetch_assoc()) {
    $candidates[] = $row;
}
$winner = $candidates[0] ?? null; // The top candidate
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Election Report | UPTM</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<?php include 'sidebar.php'; ?>
<body>

    <button class="btn-print" onclick="window.print()">🖨️ Print Report / Save PDF</button>

    <div class="report-paper">
        <div class="header">
            <h2>UPTM STUDENT ELECTION PORTAL</h2>
            <p style="margin: 5px 0;">Official Voting Results Report</p>
            <p style="font-size: 12px; color: #666;">Generated on: <?php echo date('d M Y, h:i A'); ?></p>
        </div>

        <div class="stats-summary">
            <div class="stat-item">
                <span>Total Voters</span>
                <strong><?php echo $total_voters; ?></strong>
            </div>
            <div class="stat-item">
                <span>Total Cast</span>
                <strong><?php echo $voted_count; ?></strong>
            </div>
            <div class="stat-item">
                <span>Turnout Rate</span>
                <strong><?php echo number_format($turnout_rate, 2); ?>%</strong>
            </div>
        </div>

        <?php if ($winner && $winner['total_votes'] > 0): ?>
        <div class="winner-box">
            <h3 style="margin: 0; color: #7d97f5; font-size: 14px; letter-spacing: 1px;">🏆 CURRENT LEADER / WINNER</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 10px 0;"><?php echo htmlspecialchars($winner['name']); ?></p>
            <p style="margin: 0; color: #666;"><?php echo htmlspecialchars($winner['faculty']); ?> — <strong><?php echo $winner['total_votes']; ?> Votes</strong></p>
        </div>
        <?php endif; ?>

        <h3 style="font-size: 16px; margin-bottom: 15px; color: #1e293b;">Detailed Candidate Breakdown</h3>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Candidate Name</th>
                        <th>Faculty</th>
                        <th>Votes Received</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($candidates as $index => $c): ?>
                    <tr>
                        <td style="color: #64748b;">#<?php echo $index + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($c['faculty']); ?></td>
                        <td style="font-weight: bold; color: var(--uptm-blue);"><?php echo $c['total_votes']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="signature-section">
            <div class="sig-box">
                <p style="font-size: 13px; margin: 0;">Election Officer</p>
            </div>
            <div class="sig-box">
                <p style="font-size: 13px; margin: 0;">System Administrator</p>
            </div>
        </div>
    </div>

</body>
</html>