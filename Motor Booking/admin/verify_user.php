<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include __DIR__ . '/../components/connect.php';
require __DIR__ . '/../vendor/autoload.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    header('Location: login.php');
    exit;
}

$warning_msg = [];
$success_msg = [];

if (isset($_POST['revoked'])) {
    $revoked_id = strip_tags($_POST['revoked_id']);
    $revoked_reason = isset($_POST['revoke_reason']) ? trim(strip_tags($_POST['revoke_reason'])) : '';

    if (empty($revoked_reason)) {
        $warning_msg[] = 'Revoke reason is required!';
    } else {
        $verify_revoked = $conn->prepare("SELECT * FROM `users` WHERE user_id = ? AND verified = 1");
        $verify_revoked->execute([$revoked_id]);

        if ($verify_revoked->rowCount() > 0) {
            $revoked_bookings = $conn->prepare("UPDATE `users` SET verified = 3, revoke_reason = ? WHERE user_id = ?");
            $revoked_bookings->execute([$revoked_reason, $revoked_id]);

            $booking = $verify_revoked->fetch(PDO::FETCH_ASSOC);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '@gmail.com';//account
                $mail->Password = '';//password 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('my-account@gmail.com', 'THAI KIM MOTOR Booking');
                $mail->addAddress($booking['gmail'], $booking['full_name']);

                $mail->isHTML(true);
                $mail->Subject = '❌ Registration Revoked';
                $mail->Body = "
                    <html>
                    <body>
                        <h2>Registration Status Notice</h2>
                        <p>Hi <strong>{$booking['full_name']}</strong>,</p>
                        <p>Your registration for THAI KIM MOTORS has been revoked.</p>
                        <p><strong>Reason:</strong></p>
                        <blockquote style=\"border-left: 4px solid #f44336; padding-left: 10px; color: #d32f2f;\">
                            {$revoked_reason}
                        </blockquote>
                        <p>If you have any questions, feel free to contact us.</p>
                        <br>
                        <p>Thank you,<br>THAI KIM MOTORSHOP</p>
                    </body>
                    </html>
                ";

                $mail->send();
                $success_msg[] = 'User revoked and email sent successfully.';
            } catch (Exception $e) {
                $warning_msg[] = 'User revoked but email could not be sent. Error: ' . $mail->ErrorInfo;
            }
        } else {
            $warning_msg[] = 'User already revoked or does not exist.';
        }
    }
}

// Handle Verifying a user
if (isset($_POST['verified'])) {
    $verified_id = strip_tags($_POST['verified_id']);

    $verify_user = $conn->prepare("SELECT * FROM `users` WHERE user_id = ? AND verified != 2");
    $verify_user->execute([$verified_id]);

    if ($verify_user->rowCount() > 0) {
        $update_user = $conn->prepare("UPDATE `users` SET verified = 2 WHERE user_id = ?");
        $update_user->execute([$verified_id]);

        $verified = $verify_user->fetch(PDO::FETCH_ASSOC);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'thaikimmotoshop@gmail.com';
            $mail->Password = 'sobq nhqx maie aerr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('thaikimmotoshop@gmail.com', 'THAI KIM MOTOR Booking');
            $mail->addAddress($verified['gmail'], $verified['full_name']);

            $mail->isHTML(true);
            $mail->Subject = '✅ Registration Verified';
            $mail->Body = "
                <html>
                <body>
                    <h2>Registration Status Notice</h2>
                    <p>Hi <strong>{$verified['full_name']}</strong>,</p>
                    <p>Your registration for THAI KIM MOTORS has been verified.</p>
                    <p><strong>Reason:</strong></p>
                    <blockquote style=\"border-left: 4px solid #4CAF50; padding-left: 10px; color: #388E3C;\">
                        Your information meets the requirements for registration.
                    </blockquote>
                    <p>If you have any questions, feel free to contact us.</p>
                    <br>
                    <p>Thank you,<br>THAI KIM MOTORSHOP</p>
                </body>
                </html>
            ";

            $mail->send();
            $success_msg[] = 'User verified and email sent successfully.';
        } catch (Exception $e) {
            $warning_msg[] = 'User verified but email could not be sent. Error: ' . $mail->ErrorInfo;
        }
    } else {
        $warning_msg[] = 'User already verified or does not exist!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Registrations</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<!-- header section starts  -->
<?php include '../components/admin_header.php'; ?>
<!-- header section ends -->

<section class="grid">
   <h1 class="heading">User Registrations</h1>

   <div class="box-container">
   <?php
      $select_users = $conn->prepare("SELECT * FROM `users`");
      $select_users->execute();

      if ($select_users->rowCount() > 0) {
         while ($fetch_user = $select_users->fetch(PDO::FETCH_ASSOC)) {
            if ($fetch_user['verified'] == 2 || $fetch_user['verified'] == 3) {
                continue;
            }
   ?>
        <div class="box">
        <p>User ID: <span><?= $fetch_user['user_id']; ?></span></p>
        <p>Name: <span><?= $fetch_user['full_name']; ?></span></p>
        <p>Email: <span><?= $fetch_user['gmail']; ?></span></p>
        <p>Phone: <span><?= $fetch_user['phone_number']; ?></span></p>

        <!-- Revoke Form -->
        <form action="" method="POST" onsubmit="return askCancellationReason(this);">
            <input type="hidden" name="revoked_id" value="<?= $fetch_user['user_id']; ?>">
            <input type="hidden" name="revoke_reason" value="">
            <input type="submit" value="Revoke User" name="revoked" class="btn">
        </form>

        <!-- Verify Form -->
        <form action="" method="POST">
            <input type="hidden" name="verified_id" value="<?= $fetch_user['user_id']; ?>">
            <input type="submit" value="Verify User" name="verified" class="btn" onclick="return confirm('Mark this user as verified?');">
        </form>
        </div>
   <?php
         }
      } else {
   ?>
   <div class="box" style="text-align: center;">
      <p>No registrations found!</p>
      <a href="dashboard.php" class="btn">Go to Dashboard</a>
   </div>
   <?php
      }
   ?>
   </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script>
function askCancellationReason(form) {
    let reason = prompt("Please enter the reason for revoking this user:");
    if (reason === null || reason.trim() === "") {
        alert("Revoke reason is required.");
        return false;
    }
    form.revoke_reason.value = reason.trim();
    return confirm("Are you sure you want to revoke this user?");
}
</script>

<?php include '../components/message.php'; ?>
</body>
</html>
