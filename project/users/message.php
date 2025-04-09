<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


include '/home/discipolo/Computer programs/School projects/Booking system/project/components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   header('location:index.php');
}

if(isset($_POST['check'])){

   $check_in = $_POST['check_in'];
   $check_in = strip_tags($check_in);

   $total_service = 0;

   $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
   $check_bookings->execute([$check_in]);

   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      $total_service += $fetch_bookings['service'];
   }

   // if the hotel has total 30 rooms 
   if($total_service >= 30){
      $warning_msg[] = 'service are not available';
   }else{
      $success_msg[] = 'service are available';
   }

}

    if (!isset($_SESSION['user_id'])) {
        $warning_msg[] = 'Please log in to book a service.';
    } elseif (isset($_POST['book'])) {

   $booking_id = create_unique_id();
   $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
   $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
   $number = htmlspecialchars($_POST['number'], ENT_QUOTES, 'UTF-8');
   $service = htmlspecialchars($_POST['service'], ENT_QUOTES, 'UTF-8');
   $check_in = htmlspecialchars($_POST['check_in'], ENT_QUOTES, 'UTF-8');
   $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');


   $total_service = 0;

   $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
   $check_bookings->execute([$check_in]);

   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      $total_service += $fetch_bookings['service'];
   }

   if($total_service >= 30){
      $warning_msg[] = 'Sevice are Full';
   }else{

      $verify_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND service = ? AND check_in = ? AND description = ?");
      $verify_bookings->execute([$user_id, $name, $email, $number, $service, $check_in, $description]);

      if($verify_bookings->rowCount() > 0){
         $warning_msg[] = 'It is booked already!';
      }else{
         $book_room = $conn->prepare("INSERT INTO `bookings`(booking_id, user_id, name, email, number, service, check_in, description) VALUES(?,?,?,?,?,?,?,?)");
         $book_room->execute([$booking_id, $user_id, $name, $email, $number, $service, $check_in, $description]);
         $success_msg[] = 'Booked successfully!';
      }

   }

}

if(isset($_POST['send'])){

   $id = create_unique_id();
   $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
   $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
   $number = htmlspecialchars($_POST['number'], ENT_QUOTES, 'UTF-8');
   $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
   

   $verify_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $verify_message->execute([$name, $email, $number, $message]);

   if($verify_message->rowCount() > 0){
      $warning_msg[] = 'message sent already!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `messages`(id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$id, $name, $email, $number, $message]);
      $success_msg[] = 'message send successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>home</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<section class="header">

   <div class="flex">
      <a href="#home" class="logo">Motor Services</a>
      <a href="#availability" class="btn">check availability</a>
      <div id="menu-btn" class="fas fa-bars"></div>
   </div>

   <nav class="navbar">
      <a href="dashboard.php"> Dashboard</a>
      <a href="index.php">Reservation</a>
      <a href="message.php">Contact</a>
      <a href="bookings.php";>My Booking</a>
   </nav>

</section>

<!-- header section ends -->

<!-- contact section starts  -->

<section class="contact" id="contact">

   <div class="row">

      <form action="" method="post">
         <h3>send us message</h3>
         <input type="text" name="name" required maxlength="50" placeholder="enter your name" class="box">
         <input type="email" name="email" required maxlength="50" placeholder="enter your email" class="box">
         <input type="number" name="number" required maxlength="10" min="0" max="9999999999" placeholder="enter your number" class="box">
         <textarea name="message" class="box" required maxlength="1000" placeholder="enter your message" cols="30" rows="10"></textarea>
         <input type="submit" value="send message" name="send" class="btn">
      </form>
