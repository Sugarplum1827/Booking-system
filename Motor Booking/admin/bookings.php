<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include __DIR__ . '/../components/connect.php';
require __DIR__ . '/../vendor/autoload.php'; 

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['cancel'])) {

    $cancel_id = strip_tags($_POST['cancel_id']);
    $cancel_reason = isset($_POST['cancel_reason']) ? trim(strip_tags($_POST['cancel_reason'])) : '';

    if (empty($cancel_reason)) {
        $warning_msg[] = 'Cancellation reason is required!';
    } else {
        $verify_cancel = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ? AND complete = 0");
        $verify_cancel->execute([$cancel_id]);

        if($verify_cancel->rowCount() > 0){
            $cancel_bookings = $conn->prepare("UPDATE `bookings` SET complete = 2, cancellation_reason = ? WHERE booking_id = ?");
            $cancel_bookings->execute([$cancel_reason, $cancel_id]);

            $booking = $verify_cancel->fetch(PDO::FETCH_ASSOC);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '';//account
                $mail->Password = ''; //password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('my-account@gmail.com', 'THAI KIM MOTOR Booking');
                $mail->addAddress($booking['email'], $booking['name']);

                $mail->isHTML(true);
                $mail->Subject = 'Booking Cancelled';
                $mail->Body = "
                    <html>
                    <body>
                        <h2>Booking Cancellation Notice</h2>
                        <p>Hi <strong>{$booking['name']}</strong>,</p>
                        <p>Your booking with ID <strong>{$booking['booking_id']}</strong> has been cancelled.</p>
                        <p><strong>Reason for Cancellation:</strong></p>
                        <blockquote style=\"border-left: 4px solid #f44336; padding-left: 10px; color: #d32f2f;\">
                            {$cancel_reason}
                        </blockquote>
                        <p>If you have any questions, feel free to contact us.</p>
                        <br>
                        <p>Thank you,<br>THAI KIM MOTORSHOP</p>
                    </body>
                    </html>
                ";

                $mail->send();
                $success_msg[] = 'Booking Cancelled and email sent to customer.';
            } catch (Exception $e) {
                $warning_msg[] = 'Booking Cancelled but email not sent. Error: ' . $mail->ErrorInfo;
            }
        } else {
            $warning_msg[] = 'Booking Cancelled already!';
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



if (isset($_POST['complete'])) {
   $complete_id = $_POST['complete_id'];
   $complete_id = strip_tags($complete_id);

  
   $verify_complete = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ? AND complete = 0");
   $verify_complete->execute([$complete_id]);

   if ($verify_complete->rowCount() > 0) {
       $complete_bookings = $conn->prepare("UPDATE `bookings` SET complete = 1 WHERE booking_id = ?");
       $complete_bookings->execute([$complete_id]);

       $success_msg[] = 'Booking marked as complete!';
       
   } else {
       $warning_msg[] = 'Booking already marked as complete or does not exist!';
   }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Bookings</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>
   
<!-- header section starts  -->
<?php include '../components/admin_header.php'; ?>
<!-- header section ends -->

<!-- bookings section starts  -->

<section class="grid">
   <h1 class="heading">bookings</h1>

   <div class="box-container">
   <?php
      $select_bookings = $conn->prepare("SELECT * FROM `bookings`");
      $select_bookings->execute();
      
      if($select_bookings->rowCount() > 0){
         while($fetch_bookings = $select_bookings->fetch(PDO::FETCH_ASSOC)){


            if ($fetch_bookings['complete'] == 1 && $fetch_bookings['verified'] = 1) {
                continue; 
            }
            if ($fetch_bookings['complete'] == 2 && $fetch_bookings['verified'] == 1) {
               continue;
           }
           if ($fetch_bookings['verified'] == 0) {
               continue;
           }
   ?>
   <div class="box">
      <p>Booking ID: <span><?= $fetch_bookings['booking_id']; ?></span></p>
      <p>Name: <span><?= $fetch_bookings['name']; ?></span></p>
      <p>Email: <span><?= $fetch_bookings['email']; ?></span></p>
      <p>Number: <span><?= $fetch_bookings['number']; ?></span></p>
      <p>Check-in: <span><?= $fetch_bookings['check_in']; ?></span></p>
      <p>Service: <span><?= $serviceNames[$fetch_bookings['service']] ?? 'Unknown Service'; ?></span></p>
      <p>Price: <span><?=$fetch_bookings['price'] ?? 'Unknown Price'; ?></span></p>
      <p>Description: <span><?= $fetch_bookings['description']; ?></span></p>

      <form action="" method="POST" onsubmit="return askCancellationReason(this);">
     <input type="hidden" name="cancel_id" value="<?= $fetch_bookings['booking_id']; ?>">
       <input type="hidden" name="cancel_reason" value="">
       <input type="submit" value="Cancel Booking" name="cancel" class="btn">
   
       <input type="hidden" name="complete_id" value="<?= $fetch_bookings['booking_id']; ?>">
     <input type="submit" value="Complete Booking" onclick="return confirm('Mark this booking as complete?');" name="complete" class="btn">
      </form>

   </div>
   <?php
         }
      } else {
   ?>
   <div class="box" style="text-align: center;">
      <p>No bookings found!</p>
      <a href="dashboard.php" class="btn">Go to Home</a>
   </div>
   <?php
      }
   ?>
   </div>
</section>


<!-- bookings section ends -->
















<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

<?php include '../components/message.php'; ?>
<script>
function askCancellationReason(form) {
    let reason = prompt("Please enter the reason for cancellation:");
    if(reason === null || reason.trim() === "") {
        alert("Cancellation reason is required.");
        return false; 
    }
    form.cancel_reason.value = reason.trim();
    return confirm("Are you sure you want to cancel this booking?");
}
</script>


</body>
</html>