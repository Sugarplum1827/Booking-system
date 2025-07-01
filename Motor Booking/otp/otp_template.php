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
