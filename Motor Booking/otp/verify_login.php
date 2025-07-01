<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../components/connect.php';

$message = '';

if (!isset($_SESSION['login_otp_data'])) {
    header('Location: /user/login.php');
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

        $data = $_SESSION['login_otp_data'];
        $email = $data['gmail'];
        $name = $data['full_name'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username   = '@gmail.com';// account
            $mail->Password   = ''; //password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('my-account@gmail.com', 'THAI KIM MOTOR Login Verification');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = "Login OTP Code";
            $mail->Body = "<p>Your OTP for login is: <strong>$otp</strong>. It is valid for 5 minutes.</p>";

            $mail->send();
            $message = "📨 New OTP sent to your email.";
        } catch (Exception $e) {
            $message = "❌ Failed to send OTP. Error: {$mail->ErrorInfo}";
        }
    }
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'] ?? '';

    if (!preg_match('/^\d{6}$/', $entered_otp)) {
        $message = "❌ Invalid OTP format.";
    } elseif (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
        $message = "❌ OTP session expired. Please restart login.";
    } elseif ((string)$entered_otp === (string)$_SESSION['otp'] && time() <= $_SESSION['otp_expiry']) {
        $user = $_SESSION['login_otp_data'];

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];

        unset($_SESSION['login_otp_data'], $_SESSION['otp'], $_SESSION['otp_expiry']);
        header('Location: /user/dashboard.php');
        exit;
    } else {
        $message = "❌ Invalid or expired OTP.";
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

    <form method="POST" id="otp-form">
        <div class="form-group otp-boxes">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
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

    function updateHiddenOTP() {
        const otp = Array.from(inputs).map(i => i.value).join('');
        hiddenInput.value = otp;
    }

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

    document.getElementById('otp-form').addEventListener('submit', function(event) {
        updateHiddenOTP();
        const otp = hiddenInput.value;
        if (otp.length < 6 || !/^\d{6}$/.test(otp)) {
            alert("Please enter all 6 digits of the OTP.");
            event.preventDefault();
        }
    });
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
