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

// --- 2. ADMIN ACCESS CHECK ---
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for development
error_reporting(E_ALL); 
ini_set('display_errors', 1);

// Set Malaysia Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');
$current_time = date('Y-m-d H:i:s');

// --- 3. DATA FETCHING ---

// Voter Turnout Data
$voted_res = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'Voted'");
$voted_count = $voted_res->fetch_assoc()['total'];

$not_voted_res = $conn->query("SELECT COUNT(*) as total FROM users WHERE status != 'Voted' OR status IS NULL");
$not_voted_count = $not_voted_res->fetch_assoc()['total'];

$voters_count = $voted_count + $not_voted_count;
$candidates_count = ($res = $conn->query("SELECT id FROM candidates")) ? $res->num_rows : 0;

// Active Elections
$sql_active = "SELECT * FROM elections WHERE '$current_time' >= start_date AND '$current_time' <= end_date";
$result_active = $conn->query($sql_active);

// Candidate Results for Bar Chart
$sql_chart = "SELECT c.name, COUNT(v.id) as total_votes FROM candidates c LEFT JOIN votes v ON c.id = v.candidate_id GROUP BY c.id";
$result_chart = $conn->query($sql_chart);

$candidate_names = [];
$vote_counts = [];
while($row_chart = $result_chart->fetch_assoc()) {
    $candidate_names[] = $row_chart['name'];
    $vote_counts[] = $row_chart['total_votes'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UPTM</title>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="section-header" style="margin-bottom: 25px;">
            <h2>Admin Control Panel 👋</h2>
            <p>Live overview of the UPTM Student Election.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Registered</h3>
                <div class="number"><?php echo $voters_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Voted</h3>
                <div class="number" style="color: #16a34a;"><?php echo $voted_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Candidates</h3>
                <div class="number" style="color: #3b82f6;"><?php echo $candidates_count; ?></div>
            </div>
        </div>

        <div class="charts-row">
            <div class="stat-card chart-box">
                <h3 style="margin-bottom: 15px;">Live Standings 📊</h3>
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="votingChart"></canvas>
                </div>
            </div>

            <div class="stat-card chart-box">
                <h3 style="margin-bottom: 15px;">Turnout 🗳️</h3>
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="turnoutChart"></canvas>
                </div>
            </div>
        </div>

        <div class="section-header" style="margin-top: 30px; margin-bottom: 15px;">
            <h3>Active Election Schedule 📅</h3>
        </div>
        
        <div class="table-responsive stat-card">
            <table>
                <thead>
                    <tr>
                        <th>Election Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_active && $result_active->num_rows > 0): while($row = $result_active->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($row['election_name']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['start_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['end_date'])); ?></td>
                        <td><span style="color: #16a34a; font-weight: bold; background: #f0fdf4; padding: 5px 12px; border-radius: 20px; font-size: 11px;">● ACTIVE</span></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">No active elections.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } }
};

new Chart(document.getElementById('votingChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($candidate_names); ?>,
        datasets: [{
            data: <?php echo json_encode($vote_counts); ?>,
            backgroundColor: '#3b82f6',
            borderRadius: 8
        }]
    },
    options: chartOptions
});

new Chart(document.getElementById('turnoutChart'), {
    type: 'doughnut',
    data: {
        labels: ['Voted', 'Pending'],
        datasets: [{
            data: [<?php echo $voted_count; ?>, <?php echo $not_voted_count; ?>],
            backgroundColor: ['#10b981', '#e2e8f0'],
            borderWidth: 0
        }]
    },
    options: { ...chartOptions, cutout: '75%', plugins: { legend: { display: true, position: 'bottom' } } }
});
</script>
</body>
</html>