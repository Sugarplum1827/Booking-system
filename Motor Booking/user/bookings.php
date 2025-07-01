<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
include __DIR__ . '/../components/connect.php';
require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header('Location: /user/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = [];
$warning_msg = [];

if (isset($_POST['cancel'])) {
    $cancel_id = strip_tags($_POST['cancel_id']);
    $cancel_reason = isset($_POST['cancel_reason']) ? trim(strip_tags($_POST['cancel_reason'])) : '';

    if (empty($cancel_reason)) {
        $warning_msg[] = 'Cancellation reason is required!';
    } else {
        $verify_cancel = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ? AND user_id = ? AND complete = 0");
        $verify_cancel->execute([$cancel_id, $user_id]);

        if ($verify_cancel->rowCount() > 0) {
            $cancel_bookings = $conn->prepare("UPDATE `bookings` SET complete = 2, cancellation_reason = ? WHERE booking_id = ?");
            $cancel_bookings->execute([$cancel_reason, $cancel_id]);

            $booking = $verify_cancel->fetch(PDO::FETCH_ASSOC);
 
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username   = '@gmail.com';// account
                $mail->Password   = ''; //password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
    
                $mail->setFrom('my-account@gmail.com', 'THAI KIM MOTOR Booking');
                $mail->addAddress($booking['email'], $booking['name']);
                $mail->isHTML(true);
                $mail->Subject = 'Booking Cancelled';
                $mail->Body = "
                    <html><body>
                        <h2>Booking Cancellation Notice</h2>
                        <p>Hi <strong>{$booking['name']}</strong>,</p>
                        <p>Your booking ID <strong>{$booking['booking_id']}</strong> has been cancelled.</p>
                        <p><strong>Reason:</strong></p>
                        <blockquote style='border-left: 4px solid red; padding-left: 10px; color: darkred;'>
                            {$cancel_reason}
                        </blockquote>
                        <p>Thank you,<br>THAI KIM MOTORSHOP</p>
                    </body></html>";

                $mail->send();
                $success_msg[] = 'Booking Cancelled';
            } catch (Exception $e) {
                $warning_msg[] = 'Booking cancelled but email failed. Error: ' . $mail->ErrorInfo;
            }
        } else {
            $warning_msg[] = 'Booking not found or already cancelled.';
        }
    }
}

$serviceNames = [
   1 => 'Change Oil',
   2 => 'CVT Cleaning',
   3 => 'Front Shock Repack',
   4 => 'Brake Cleaning',
   5 => 'Parts and Accessories',
   6 => 'Vulcanizing',
   7 => 'Magneto Repaint',
   8 => 'FI Cleaning',
   9 => 'Sensor Diagnotics',
   10 => 'PMS (Preventive Maintenace Service)',
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Bookings</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">


   <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
   <script>
      function askCancellationReason(form) {
         const reason = prompt("Please provide a reason for cancellation:");
         if (!reason) {
            alert("Cancellation reason is required.");
            return false;
         }
         form.cancel_reason.value = reason;
         return true;
      }
   </script>
</head>

<body>

<?php include '../components/verified_header.php'; ?>
<?php include '../components/message.php'; ?>


<section class="grid">
   <h1 class="heading">My Bookings</h1>

   <div class="box-container">
   <?php
      $select_bookings = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND verified = 1 AND complete = 0");
      $select_bookings->execute([$user_id]);

      if ($select_bookings->rowCount() > 0):
         while ($fetch = $select_bookings->fetch(PDO::FETCH_ASSOC)):
   ?>
   <div class="box">
      <p>Booking ID: <span><?= $fetch['booking_id'] ?></span></p>
      <p>Name: <span><?= $fetch['name'] ?></span></p>
      <p>Email: <span><?= $fetch['email'] ?></span></p>
      <p>Number: <span><?= $fetch['number'] ?></span></p>
      <p>Check-in: <span><?= $fetch['check_in'] ?></span></p>
      <p>Service: <span><?= $serviceNames[$fetch['service']] ?? 'Unknown Service'; ?></span></p>
      <p>Price: <span><?= $fetch['price'] ?? 'Unknown price'; ?></span></p>
      <p>Description: <span><?= $fetch['description'] ?></span></p>

      <form method="POST" onsubmit="return askCancellationReason(this);">
         <input type="hidden" name="cancel_id" value="<?= $fetch['booking_id'] ?>">
         <input type="hidden" name="cancel_reason" value="">
         <input type="submit" name="cancel" value="Cancel Booking" class="btn">
      </form>
   </div>
   <?php endwhile; else: ?>
      <div class="box" style="text-align: center;">
         <p>No active bookings found!</p>
         <a href="reservation.php" class="btn">Book a Service</a>
      </div>
   <?php endif; ?>
   </div>
</section>

</body>
</html>
