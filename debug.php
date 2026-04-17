<?php
session_start();
include('../db_config.php');

// =============================================
// TUKAR student_id dan password anda kat sini
// =============================================
$test_student_id = "am2408016644";   // <-- tukar kepada student ID anda
$test_password   = "tehah123";    // <-- tukar kepada password anda
// =============================================

echo "<h2>🔍 Debug Login Candidate</h2>";
echo "<hr>";

// STEP 1: Check connection DB
if ($conn) {
    echo "✅ <b>DB Connection:</b> OK<br><br>";
} else {
    echo "❌ <b>DB Connection FAILED</b><br><br>";
    die();
}

// STEP 2: Check student_id wujud dalam DB
$sql = "SELECT id, name, student_id, password FROM candidates WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $test_student_id);
$stmt->execute();
$result = $stmt->get_result();

echo "🔎 <b>Cari Student ID:</b> " . $test_student_id . "<br>";
echo "📊 <b>Rows found:</b> " . $result->num_rows . "<br><br>";

if ($result->num_rows == 0) {
    echo "❌ <b>Student ID tidak wujud dalam database!</b><br>";
    echo "👉 Pastikan student_id dalam DB betul (case-sensitive, tiada spaces)<br>";

    // Show all candidates in DB
    echo "<br><b>Senarai semua student_id dalam table candidates:</b><br>";
    $all = $conn->query("SELECT id, name, student_id FROM candidates");
    while ($row = $all->fetch_assoc()) {
        echo "- ID: <b>" . $row['id'] . "</b> | Name: <b>" . $row['name'] . "</b> | Student ID: <b>" . $row['student_id'] . "</b><br>";
    }

} else {
    $data = $result->fetch_assoc();
    echo "✅ <b>Student ID ditemui!</b><br>";
    echo "👤 <b>Name:</b> " . $data['name'] . "<br>";
    echo "🔑 <b>Password hash dalam DB:</b> " . $data['password'] . "<br><br>";

    // STEP 3: Verify password
    $verify = password_verify($test_password, $data['password']);

    if ($verify) {
        echo "✅ <b>Password BETUL!</b> Login sepatutnya berjaya.<br>";
        echo "👉 Cuba semak redirect portal.php atau session issue.<br>";
    } else {
        echo "❌ <b>Password SALAH!</b> password_verify() returned false.<br><br>";
        echo "💡 <b>Cara fix:</b><br>";
        echo "1. Pergi phpMyAdmin → table candidates<br>";
        echo "2. Edit row untuk student_id ini<br>";
        echo "3. Set password baru menggunakan kod di bawah:<br><br>";

        $new_hash = password_hash($test_password, PASSWORD_BCRYPT);
        echo "<div style='background:#f0f0f0;padding:10px;border-radius:8px;font-family:monospace;'>";
        echo "Hash baru untuk password '<b>" . $test_password . "</b>':<br>";
        echo "<b>" . $new_hash . "</b>";
        echo "</div><br>";
        echo "Copy hash di atas dan paste dalam column <b>password</b> dalam phpMyAdmin.<br>";
    }
}

echo "<hr>";
echo "<small style='color:red;'>⚠️ PADAM fail debug.php ini selepas selesai!</small>";
?>