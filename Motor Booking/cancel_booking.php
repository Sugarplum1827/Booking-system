<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require __DIR__ . '/components/connect.php';
require __DIR__ . '/vendor/autoload.php';

$message = '';
$error = '';

if (!isset($_GET['booking_id'])) {
    die("Invalid request: booking_id is missing.");
}

$booking_id = $_GET['booking_id'];
$booking = null;


$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
$stmt->execute([$booking_id]);
if ($stmt->rowCount() > 0) {
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("❌ Booking not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancel_reason = trim(strip_tags($_POST['cancel_reason'] ?? ''));
    $cancel_id = strip_tags($_POST['cancel_id'] ?? '');

    if (empty($cancel_reason)) {
        $error = '❌ Cancellation reason is required!';
    } else {
        if ($booking['booking_id'] != $cancel_id) {
            $error = "❌ Booking ID mismatch.";
        } elseif ($booking['complete'] == 0) {
            $update = $conn->prepare("UPDATE bookings SET complete = 2, cancellation_reason = ? WHERE booking_id = ?");
            $success = $update->execute([$cancel_reason, $cancel_id]);

            if (!$success) {
                print_r($update->errorInfo());
            }

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
                $mail->addAddress($booking['email'], $booking['name']);

                $mail->isHTML(true);
                $mail->Subject = 'Booking Cancelled';
                $mail->Body = "
                    <html>
                    <body>
                        <h1>Booking Cancelled</h1>
                        <p>Hi {$booking['name']},</p>
                        <p>Your booking with Thai Motor has been cancelled.</p>
                        <p><strong>Reason:</strong> {$cancel_reason}</p>
                        <p>Thank you!</p>
                    </body>
                    </html>
                ";

                $mail->send();
                $message = "✅ Booking cancelled and email sent to the user.";
            } catch (Exception $e) {
                $message = "⚠️ Booking cancelled, but email failed to send: {$mail->ErrorInfo}";
            }
        } elseif ($booking['complete'] == 2) {
            $error = "⚠️ This booking has already been cancelled.";
        } elseif ($booking['complete'] == 1) {
            $error = "⚠️ This booking has already been completed.";
        } else {
            $error = "⚠️ This booking cannot be cancelled due to unknown status.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Booking</title>
    <style>
        body {
            background-color: #f5f5dc;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .cancel-box {
            background-color: #8b4513;
            color: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }

        textarea {
            width: 100%;
            height: 100px;
            margin-top: 10px;
            border-radius: 5px;
            padding: 10px;
            font-size: 16px;
            resize: vertical;
        }

        button {
            padding: 10px 20px;
            background-color: #deb887;
            border: none;
            color: #333;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .message, .error {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<?php if ($booking['complete'] == 0): ?>
    <div class="cancel-box">
        <h2 style="text-align: center; font-size: 2rem;">Cancel Your Booking</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($message)): ?>
            <form method="POST">
                <input type="hidden" name="cancel_id" value="<?= htmlspecialchars($booking_id) ?>">
                <label for="cancel_reason">Please tell us why you’re cancelling:</label><br>
                <textarea name="cancel_reason" id="cancel_reason" required></textarea><br>
                <button type="submit">Submit Cancellation</button>
            </form>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="cancel-box">
        <h2 style="text-align: center; font-size: 2rem;">Booking is Already Cancelled or Completed</h2>
    </div>
<?php endif; ?>

</body>
</html>
