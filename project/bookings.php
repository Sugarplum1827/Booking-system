<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


include __DIR__ . '/components/connect.php';


if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   header('location:index.php');
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>bookings</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- booking section starts  -->

<section class="bookings">

   <h1 class="heading">my bookings</h1>

   <div class="box-container">

   <?php
      $select_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ?");
      $select_bookings->execute([$user_id]);
      if($select_bookings->rowCount() < 0){
         while($fetch_booking = $select_bookings->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p>name : <span><?= $fetch_booking['name']; ?></span></p>
      <p>email : <span><?= $fetch_booking['email']; ?></span></p>
      <p>number : <span><?= $fetch_booking['number']; ?></span></p>
      <p>check in : <span><?= $fetch_booking['check_in']; ?></span></p>
      <p>service : <span><?= $fetch_booking['service']; ?></span></p>
      <p>description : <span><?= $fetch_booking['description']; ?></span></p>
      <p>booking id : <span><?= $fetch_booking['booking_id']; ?></span></p>
      <form action="" method="POST">
         <input type="hidden" name="cancel_id" value="<?= $fetch_booking['booking_id']; ?>">
         <input type="submit" value="cancel booking" name="cancel" class="btn" onclick="return confirm('cancel this booking?');">
      </form>
   </div>
   <?php
    }
   }else{
   ?>   
   <div class="box" style="text-align: center;">
      <p style="padding-bottom: .5rem; text-transform:capitalize;">no bookings found!</p>
      <a href="index.php#reservation" class="btn">book new</a>
   </div>
   <?php
   }
   ?>
   </div>

</section>

<!-- booking section ends -->


























<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>