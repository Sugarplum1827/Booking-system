<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../components/connect.php';

$message = '';

if (!isset($_SESSION['signup_data'])) {
    header('Location: /user/signup.php');
    exit;
}

if (isset($_POST['resend_otp'])) {
    if (isset($_SESSION['otp_expiry']) && time() < $_SESSION['otp_expiry']) {
        $remaining = $_SESSION['otp_expiry'] - time();
        $message = "⏳ Please wait " . floor($remaining / 60) . "m " . ($remaining % 60) . "s to resend OTP.";
    } else {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300;

        $data = $_SESSION['signup_data'];
        $email = $data['gmail'];
        $name = $data['full_name'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '@gmail.com';// account
            $mail->Password   = ''; //password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('my-account@gmail.com', 'THAI KIM MOTOR Admin Signup');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = "Signup OTP Code";
            $mail->Body = "<p>Your OTP for signup is: <strong>$otp</strong>. It is valid for 5 minutes.</p>";

            $mail->send();
            $message = "📨 New OTP sent to your email.";
        } catch (Exception $e) {
            $message = "❌ Failed to send OTP. Error: {$mail->ErrorInfo}";
        }
    }
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];

    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
        $message = "❌ OTP session expired. Please restart signup.";
    } elseif ((string)$entered_otp === (string)$_SESSION['otp'] && time() <= $_SESSION['otp_expiry']) {
        $data = $_SESSION['signup_data'];
        $id = $data['user_id'];
        $name = $data['full_name'];
        $gmail = $data['gmail'];
        $phone_number = $data['phone_number'];
        $hashed_pass = $data['password'];

        $insert = $conn->prepare("INSERT INTO users (user_id, full_name, gmail, phone_number, password, verified) VALUES (?, ?, ?, ?, ?, 1)");
        $success = $insert->execute([$id, $name, $gmail, $phone_number, $hashed_pass]);

        if ($success) {
            unset($_SESSION['signup_data'], $_SESSION['otp'], $_SESSION['otp_expiry']);
                    $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'thaikimmotoshop@gmail.com';
            $mail->Password   = 'sobq nhqx maie aerr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('thaikimmotoshop@gmail.com', 'THAI KIM MOTOR Admin Signup');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = "Signup OTP Code";
            $mail->Body = "<p>Your Registration Successful. Please Wait for the Approval of admin</p>";

            $mail->send();
        } catch (Exception $e) {
            $message = "❌ Failed to send OTP. Error: {$mail->ErrorInfo}";
        }
            header('Location: /user/not_verified.php');
            exit;
        } else {
            $message = "❌ Signup failed. Try again.";
        }
    } else {
        $message = "❌ Invalid or expired OTP.";
    }
}

include __DIR__ . '/otp_template.php';
