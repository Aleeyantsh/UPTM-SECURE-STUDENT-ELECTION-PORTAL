<?php
// =============================================
// SESSION & AUTH CHECK
// =============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_config.php');

// Pastikan hanya POST request diterima
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: portal.php");
    exit();
}

// Auth check
if (!isset($_SESSION['voter_id'])) {
    header("Location: login.php");
    exit();
}

// =============================================
// CSRF VERIFICATION
// =============================================
verify_csrf_token($_POST['csrf_token'] ?? '');

// =============================================
// INPUT VALIDATION
// =============================================
if (!isset($_POST['candidate_id'])) {
    header("Location: portal.php");
    exit();
}

// FIX: Cast ke integer - elak SQL injection & pastikan ID valid
$voter_id     = (int) $_SESSION['voter_id'];
$candidate_id = (int) $_POST['candidate_id'];

// Pastikan candidate_id adalah nombor positif yang valid
if ($candidate_id <= 0) {
    header("Location: portal.php?error=invalid");
    exit();
}

// =============================================
// VERIFY CANDIDATE WUJUD DALAM DATABASE
// =============================================
$stmt_check = $conn->prepare("SELECT id FROM candidates WHERE id = ?");
$stmt_check->bind_param("i", $candidate_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0) {
    header("Location: portal.php?error=invalid_candidate");
    exit();
}
$stmt_check->close();

// =============================================
// VERIFY ELECTION SEDANG AKTIF
// =============================================
date_default_timezone_set('Asia/Kuala_Lumpur');
$now = date('Y-m-d H:i:s');

$stmt_election = $conn->prepare(
    "SELECT id FROM elections WHERE ? BETWEEN start_date AND end_date LIMIT 1"
);
$stmt_election->bind_param("s", $now);
$stmt_election->execute();
if ($stmt_election->get_result()->num_rows === 0) {
    header("Location: dashboard.php?error=election_closed");
    exit();
}
$stmt_election->close();

// =============================================
// SEMAK JUMLAH UNDI SEMASA
// =============================================
$stmt_count = $conn->prepare(
    "SELECT COUNT(*) as total_voted FROM votes WHERE voter_id = ?"
);
$stmt_count->bind_param("i", $voter_id);
$stmt_count->execute();
$total_voted = $stmt_count->get_result()->fetch_assoc()['total_voted'];
$stmt_count->close();

if ($total_voted >= 5) {
    header("Location: dashboard.php?error=quota_full");
    exit();
}

// =============================================
// SEMAK UNDI PENDUA UNTUK CALON YANG SAMA
// =============================================
$stmt_duplicate = $conn->prepare(
    "SELECT id FROM votes WHERE voter_id = ? AND candidate_id = ?"
);
$stmt_duplicate->bind_param("ii", $voter_id, $candidate_id);
$stmt_duplicate->execute();
if ($stmt_duplicate->get_result()->num_rows > 0) {
    header("Location: portal.php?error=already_voted");
    exit();
}
$stmt_duplicate->close();

// =============================================
// DATABASE TRANSACTION - INSERT UNDI
// =============================================
$conn->begin_transaction();

try {
    // A. Insert rekod undi
    $stmt_vote = $conn->prepare(
        "INSERT INTO votes (candidate_id, voter_id) VALUES (?, ?)"
    );
    $stmt_vote->bind_param("ii", $candidate_id, $voter_id);
    $stmt_vote->execute();
    $stmt_vote->close();

    $new_total = $total_voted + 1;

    // B. Kalau ini undi ke-5, kemaskini status pengundi
    if ($new_total >= 5) {
        $stmt_status = $conn->prepare(
            "UPDATE users SET status = 'Voted' WHERE id = ?"
        );
        $stmt_status->bind_param("i", $voter_id);
        $stmt_status->execute();
        $stmt_status->close();
    }

    // C. Audit log
    write_log($conn, 'CAST_VOTE', "Voter ID $voter_id voted for Candidate ID $candidate_id");

    $conn->commit();

    // FIX: Guna header redirect dengan flash message dalam session
    // elak guna alert() + inline script untuk keselamatan (XSS)
    if ($new_total >= 5) {
        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => 'Congratulations! You have completed all 5 of your voting quotas.'
        ];
        header("Location: thank_you.php");
    } else {
        $remaining = 5 - $new_total;
        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => "Vote accepted! You have $remaining vote(s) remaining."
        ];
        header("Location: dashboard.php");
    }
    exit();

} catch (Exception $e) {
    $conn->rollback();

    // FIX: Jangan dedahkan mesej error teknikal kepada pengguna
    error_log("Vote submission error: " . $e->getMessage());

    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'A technical error occurred. Please try again.'
    ];
    header("Location: portal.php");
    exit();
}
?>