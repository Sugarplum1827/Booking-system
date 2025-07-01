<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../components/connect.php';

$warning_msg = [];

if (isset($_POST['submit'])) {
    $full_name = trim(strip_tags($_POST['full_name']));
    $gmail = trim(strip_tags($_POST['gmail']));
    $phone = trim(strip_tags($_POST['phone']));
    $raw_pass = $_POST['pass'];
    $c_pass = $_POST['c_pass'];

    if (!filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        $warning_msg[] = 'Invalid email format!';
    }

    if ($raw_pass !== $c_pass) {
        $warning_msg[] = 'Password not matched!';
    }

    $check = $conn->prepare("SELECT * FROM users WHERE gmail = ?");
    $check->execute([$gmail]);

    if ($check->rowCount() > 0) {
        $warning_msg[] = 'Email already registered!';
    }

    if (empty($warning_msg)) {
        $user_id = create_unique_id();
        $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);

        $_SESSION['signup_data'] = [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'gmail' => $gmail,
            'phone_number' => $phone,
            'password' => $hashed_pass
        ];

        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300;
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username   = '@gmail.com';// account
            $mail->Password   = ''; //password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('my-account@gmail.com', 'THAI KIM MOTORS Booking');
            $mail->addAddress($gmail, $full_name);
            $mail->isHTML(true);
            $mail->Subject = 'OTP Verification Code';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <h2>OTP Verification</h2>
                    <p>Hi <strong>$full_name</strong>,</p>
                    <p>Thanks for registering. Your OTP is:</p>
                    <h3 style='background:#964B00;color:#fff;display:inline-block;padding:10px;border-radius:5px;'>$otp</h3>
                    <p>This OTP is valid for 5 minutes.</p>
                    <p>THAI KIM MOTORSHOP</p>
                </div>
            ";

            $mail->send();
            header('Location: /otp/verify_signup.php');
            exit;
        } catch (Exception $e) {
            $warning_msg[] = 'OTP Email failed to send.';
        }
    }
}
?>

<!-- HTML Part -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<section class="form-container">
    <form action="" method="POST">
        <h3>Register Now</h3>
        <p style="color: black; margin-bottom: 5px;">Already have an account? <a href="login.php">Log in</a></p>
        <input type="text" name="full_name" placeholder="Enter full name" class="box" required>
        <input type="email" name="gmail" placeholder="Enter email" class="box" required>
        <input type="text" name="phone" placeholder="Enter phone number" class="box" required>
        <input type="password" name="pass" placeholder="Enter password" class="box" required>
        <input type="password" name="c_pass" placeholder="Confirm password" class="box" required>
        <input type="submit" value="Register Now" name="submit" class="btn">
    </form>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php 
if (!empty($warning_msg)) {
    foreach ($warning_msg as $msg) {
        echo '<script>swal("Warning", "' . htmlspecialchars($msg) . '", "warning");</script>';
    }
}
?>
</body>
</html>
