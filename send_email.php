<?php
// 1. Manually call PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';

// 2. Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    // --- SERVER CONFIGURATION ---
    $mail->isSMTP();                                            // Use SMTP
    $mail->Host       = 'smtp.gmail.com';                       // Gmail SMTP Server
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'informationtechnology085@gmail.com';     // Your Gmail email
    $mail->Password   = 'onyn ucsh hpft tgrm';                  // Your 16-digit Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // TLS Encryption
    $mail->Port       = 587;                                    // TCP Port

    // --- RECIPIENTS ---
    $mail->setFrom('no-reply@uptmsecure.com', 'UPTM SECURE SYSTEM');
    $mail->addAddress($candidate_email, $candidate_name);       // Candidate email from form input

    // --- EMAIL CONTENT ---
    $mail->isHTML(true);                                        // Set format to HTML
    $mail->Subject = 'CONFIRMATION: UPTM Election Candidate Registration';
    
    // Professional & Clean Email Design
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; border: 1px solid #e2e8f0; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto;'>
            <h2 style='color: #2563eb;'>Congratulations, " . htmlspecialchars($candidate_name) . "!</h2>
            <p style='color: #475569;'>Your registration as a candidate for the <b>Student Representative Council (MPP) Election</b> has been successfully processed.</p>
            <hr style='border: 0; border-top: 1px solid #f1f5f9; margin: 20px 0;'>
            <p style='color: #1e293b;'>Your current registration status is: 
                <span style='color: #16a34a; font-weight: bold;'>WAITING FOR ADMIN VERIFICATION</span>.
            </p>
            <p style='color: #475569;'>Please log in to the candidate portal regularly to check your status and update your manifesto.</p>
            <br>
            <p style='font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px;'>
                This is an automated registration email. Please do not reply to this message.
            </p>
        </div>
    ";

    $mail->send();
    // Redirect or show success message
    echo "<script>alert('Registration Successful & Confirmation Email Sent!'); window.location.href='index.php';</script>";

} catch (Exception $e) {
    // Show error message if email fails
    echo "<script>alert('The confirmation email could not be sent. Error: {$mail->ErrorInfo}');</script>";
}