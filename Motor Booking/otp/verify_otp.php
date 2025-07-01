<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require __DIR__ . '/../vendor/autoload.php';

$message = '';

if (!isset($_SESSION['booking_data'])) {
    header('Location: /index.php');
    exit;
}


if (isset($_POST['resend_otp'])) {
    if (isset($_SESSION['otp_expiry']) && time() < $_SESSION['otp_expiry']) {
        $remaining = $_SESSION['otp_expiry'] - time();
        $minutes = floor($remaining / 60);
        $seconds = $remaining % 60;
        $message = "⏳ Please wait {$minutes}m {$seconds}s before requesting a new OTP.";
    } else {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300; 

        $data = $_SESSION['booking_data'];
        $email = $data['email'];
        $name = $data['name'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '@gmail.com';// account
            $mail->Password   = ''; //password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('my-account@gmail.com', 'THAI KIM MOTOR Booking');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = "Your New OTP Code";
            $mail->Body = "
            <div style=\"font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2\">
                <div style=\"margin:50px auto;width:70%;padding:20px 0\">
                    <div style=\"border-bottom:1px solid #eee\">
                        <a href=\"#\" style=\"font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600\">THAI KIM MOTORSHOP</a>
                    </div>
                    <p style=\"font-size:1.1em\">Hi,</p>
                    <p>Thank you for choosing THAI KIM MOTORSHOP. Use the following OTP to complete your booking procedures. OTP is valid for 5 minutes</p>
                    <h2 style=\"background: #00466a;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;\">$otp</h2>
                    <p style=\"font-size:0.9em;\">Regards,<br />THAI KIM MOTORSHOP</p>
                    <hr style=\"border:none;border-top:1px solid #eee\" />
                </div>
            </div>
            ";

            $mail->send();
            $message = "📨 New OTP sent to your email.";
        } catch (Exception $e) {
            $message = "❌ Failed to send OTP email. Error: {$mail->ErrorInfo}";
        }
    }
}


if (isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']);

    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
        $message = "❌ OTP session not found. Please restart the booking.";
    } elseif ((string)$entered_otp === (string)$_SESSION['otp'] && time() <= $_SESSION['otp_expiry']) {
        include __DIR__ . '/../components/connect.php';

        $data = $_SESSION['booking_data'];

        $stmt = $conn->prepare("INSERT INTO bookings (user_id, booking_id, name, email, number, service, price, check_in, description, complete, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1)");

        $success = $stmt->execute([
            $data['user_id'],
            $data['booking_id'],
            $data['full_name'],   
            $data['email'],
            $data['phone'],       
            $data['service'],
            $data['price'],
            $data['check_in'],
            $data['description']
        ]);


        if ($success) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'thaikimmotoshop@gmail.com';
                $mail->Password   = 'sobq nhqx maie aerr';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('thaikimmotoshop@gmail.com', 'THAI KIM MOTORS Booking');
                $mail->addAddress($data['email'], $data['name']);

                $mail->isHTML(true);
                $mail->Subject = "Booking Confirmed";

                $cancel_link = "https://thaikimmotor.infinityfreeapp.com/cancel_booking.php?booking_id=" . urlencode($data['booking_id']);
                $mail->Body = "
                <div style=\"font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2\">
                <div style=\"margin:50px auto;width:70%;padding:20px 0\">
                    <div style=\"border-bottom:1px solid #eee\">
                    <a href=\"#\" style=\"font-size:1.4em;color: #964B00;text-decoration:none;font-weight:600\">THAI KIM MOTORSHOP</a>
                    </div>
                    <p style=\"font-size:1.1em\">Hi {$data['name']},</p>
                    <p>Your booking with <strong>Thai Kim Motor Shop</strong> has been <strong>confirmed</strong>.</p>
                    <p>If you wish to cancel, click the button below:</p>
                    <p style=\"text-align: center;\">
                    <a href=\"$cancel_link\" style=\"background: #FF0000; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display:                     inline-block;\">Cancel My Booking</a>
                    </p>
                    <p style=\"font-size:0.9em;\">Thank you,<br/>THAI KIM MOTORSHOP</p>
                    <hr style=\"border:none;border-top:1px solid #eee\" />
                </div>
                </div>
                ";

                $mail->send();
                header('Location: /user/reservation.php');
                unset($_SESSION['booking_data'], $_SESSION['otp'], $_SESSION['otp_expiry']);
                $message = "✅ Booking confirmed! A confirmation email has been sent.";
            } catch (Exception $e) {
                $message = "✅ Booking saved but email failed. Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Failed to save booking. Try again.";
        }
    } else {
        $message = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body {
            background-color: #f5f5dc;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #8b4513;
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        .form-group {
            margin: 20px 0;
        }

        .otp-boxes {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .otp-boxes input {
            width: 40px;
            height: 50px;
            font-size: 24px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: white;
            color: black;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            margin: 10px 5px;
            border: none;
            border-radius: 5px;
            background-color: #deb887;
            color: #333;
            cursor: pointer;
        }

        .message {
            font-weight: bold;
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Verify Your OTP</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" onsubmit="combineOTP(event)">
        <div class="form-group otp-boxes">
            <input type="text" maxlength="1" inputmode="numeric" oninput="moveNext(this, 0)" onkeydown="handleBackspace(event, 0)">
            <input type="text" maxlength="1" inputmode="numeric" oninput="moveNext(this, 1)" onkeydown="handleBackspace(event, 1)">
            <input type="text" maxlength="1" inputmode="numeric" oninput="moveNext(this, 2)" onkeydown="handleBackspace(event, 2)">
            <input type="text" maxlength="1" inputmode="numeric" oninput="moveNext(this, 3)" onkeydown="handleBackspace(event, 3)">
            <input type="text" maxlength="1" inputmode="numeric" oninput="moveNext(this, 4)" onkeydown="handleBackspace(event, 4)">
            <input type="text" maxlength="1" inputmode="numeric" oninput="moveNext(this, 5)" onkeydown="handleBackspace(event, 5)">
        </div>
        <input type="hidden" name="otp" id="otp-hidden">
        <button type="submit" name="verify_otp">Verify OTP</button>
    </form>

   <form method="POST" style="margin-top: 20px;">
    <p style="color: white; margin-bottom: 5px;">Didn't receive the code?</p>
    <button 
        id="resend-btn" 
        type="submit" 
        name="resend_otp" 
        style="background: none; border: none; color: #007bff; text-decoration: underline; cursor: pointer; font-size: 16px; padding: 0;">
        Resend code
    </button>
    <span id="countdown" style="margin-left: 10px; color: lightgray;"></span>
</form>


</div>

<script>
    const inputs = document.querySelectorAll('.otp-boxes input');
    const hiddenInput = document.getElementById('otp-hidden');

    inputs[0].focus();

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            updateHiddenOTP();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                if (input.value === '') {
                    if (index > 0) {
                        inputs[index - 1].focus();
                    }
                } else {
                    input.value = '';
                }
                e.preventDefault();
                updateHiddenOTP();
            }
        });
    });

    function updateHiddenOTP() {
        const otp = Array.from(inputs).map(i => i.value).join('');
        hiddenInput.value = otp;
    }

    function combineOTP(event) {
        updateHiddenOTP();
        const otp = hiddenInput.value;
        if (otp.length < 6) {
            alert("Please enter all 6 digits.");
            event.preventDefault();
        }
    }
</script>
<?php if (isset($_SESSION['otp_expiry'])): ?>
<script>
    const otpExpiry = <?= $_SESSION['otp_expiry'] ?>;

    const resendBtn = document.getElementById('resend-btn');
    const countdownSpan = document.getElementById('countdown');

    function updateCountdown() {
        const now = Math.floor(Date.now() / 1000);
        const remaining = otpExpiry - now;

        if (remaining > 0) {
            resendBtn.disabled = true;
            resendBtn.style.opacity = 0.5;
            resendBtn.style.cursor = 'not-allowed';

            const mins = Math.floor(remaining / 60);
            const secs = remaining % 60;
            countdownSpan.textContent = `(${mins}:${secs.toString().padStart(2, '0')})`;
        } else {
            resendBtn.disabled = false;
            resendBtn.style.opacity = 1;
            resendBtn.style.cursor = 'pointer';
            countdownSpan.textContent = '';
            clearInterval(timer);
        }
    }

    const timer = setInterval(updateCountdown, 1000);
    updateCountdown(); 
</script>
<?php endif; ?>

</body>
</html>
