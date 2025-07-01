<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
require __DIR__ . '/../vendor/autoload.php'; 

include __DIR__ . '/../components/connect.php';

if (!isset($_SESSION['user_email']) || !isset($_SESSION['booking_data'])) {
    die("Unauthorized access.");
}


$email = $_SESSION['booking_data']['email'];
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = time() + 300; 

$mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';             
        $mail->SMTPAuth   = true;                          
        $mail->Username   = '@gmail.com';// account
        $mail->Password   = ''; //password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('my-account@gmail.com', 'THAI KIM MOTORS Booking');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'OTP Code';

        $mail->Body = "
        <div style=\"font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2\">
        <div style=\"margin:50px auto;width:70%;padding:20px 0\">
            <div style=\"border-bottom:1px solid #eee\">
            <a href=\"#\" style=\"font-size:1.4em;color: #964B00;text-decoration:none;font-weight:600\">THAI KIM MOTORSHOP</a>
            </div>
            <p style=\"font-size:1.1em\">Hi,</p>
            <p>Thank you for choosing THAI KIM MOTORSHOP. Use the following OTP to complete your booking procedures. OTP is valid for 5 minutes</p>
            <h2 style=\"background: #964B00;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;\">$otp</h2>
            <p style=\"font-size:0.9em;\">Regards,<br />THAI KIM MOTORSHOP</p>
            <hr style=\"border:none;border-top:1px solid #eee\" />
        </div>
        </div>
        ";
        $mail->send();

         header('Location: /otp/verify_otp.php');
    exit;

} catch (Exception $e) {
    die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
}
?>
