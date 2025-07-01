<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['cancel'])){

   $cancel_id = $_POST['cancel_id'];
   $cancel_id = strip_tags($cancel_id);

   $verify_cancel = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ? AND complete = 0");
   $verify_cancel->execute([$cancel_id]);

   if($verify_cancel->rowCount() > 0){
      $cancel_bookings = $conn->prepare("UPDATE `bookings` SET complete = 2 WHERE booking_id = ?");
      $cancel_bookings->execute([$cancel_id]);
      $success_msg[] = 'Booking Cancelled!';
   }else{
      $warning_msg[] = 'Booking Cancellend already!';
   }

}
if (isset($_POST['complete'])) {
   $complete_id = $_POST['complete_id'];
   $complete_id = strip_tags($complete_id);

   // Verify if the booking exists and is not marked as complete
   $verify_complete = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ? AND complete = 0");
   $verify_complete->execute([$complete_id]);

   if ($verify_complete->rowCount() > 0) {
       // Mark the booking as complete
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

            // Hide completed bookings
            if ($fetch_bookings['complete'] == 1 && $fetch_bookings['verified'] = 1) {
                continue; // Skip rendering this booking
            }
            if ($fetch_bookings['complete'] == 2 && $fetch_bookings['verified'] == 1) {
               continue; // Skip rendering this booking
           }
           if ($fetch_bookings['verified'] == 0) {
               continue; // Skip rendering this booking
           }
   ?>
   <div class="box">
      <p>Booking ID: <span><?= $fetch_bookings['booking_id']; ?></span></p>
      <p>Name: <span><?= $fetch_bookings['name']; ?></span></p>
      <p>Email: <span><?= $fetch_bookings['email']; ?></span></p>
      <p>Number: <span><?= $fetch_bookings['number']; ?></span></p>
      <p>Check-in: <span><?= $fetch_bookings['check_in']; ?></span></p>
      <p>Service: <span><?= $fetch_bookings['service']; ?></span></p>
      <p>Description: <span><?= $fetch_bookings['description']; ?></span></p>

      <form action="" method="POST">
         <input type="hidden" name="cancel_id" value="<?= $fetch_bookings['booking_id']; ?>">
         <input type="submit" value="Cancel Booking" onclick="return confirm('Cancel this booking?');" name="cancel" class="btn">
         
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

</body>
</html>