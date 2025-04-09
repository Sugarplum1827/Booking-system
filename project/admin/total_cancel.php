<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '/home/discipolo/com_progs/school_proj/booking_system/project/components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['delete'])){

    $booking_id = $_POST['delete_id'];
    $booking_id = strip_tags($booking_id);
 
    $verify_booking = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ?");
    $verify_booking->execute([$booking_id]);
 
    if($verify_booking->rowCount() > 0){
       $delete_booking = $conn->prepare("DELETE FROM `bookings` WHERE booking_id = ?");
       $delete_booking->execute([$booking_id]);
       $success_msg[] = 'Record Deletion successful!';
    }else{
       $warning_msg[] = 'Record Deleted already!';
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
      $select_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE complete = 2");
      $select_bookings->execute();
      
    if ($select_bookings->rowCount() > 0){
         while($fetch_bookings = $select_bookings->fetch(PDO::FETCH_ASSOC)){
           // Hide completed bookings
            if ($fetch_bookings['complete'] == 1) {
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
     <p>CANCELLED</p>
     <form action="" method="POST">
         <input type="hidden" name="delete_id" value="<?= $fetch_bookings['booking_id']; ?>">
         <input type="submit" value="Delete Booking" onclick="return confirm('Delete this Record?');" name="delete" class="btn">
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