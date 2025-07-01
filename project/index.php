<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '/home/maelstrom/Booking-system/project/components/connect.php';


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

   $check_bookings = $conn->prepare("SELECT * FROM bookings WHERE check_in = ?");
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

    if (isset($_POST['book'])) {

   $booking_id = create_unique_id();
   $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
   $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
   $number = htmlspecialchars($_POST['number'], ENT_QUOTES, 'UTF-8');
   $service = htmlspecialchars($_POST['service'], ENT_QUOTES, 'UTF-8');
   $check_in = htmlspecialchars($_POST['check_in'], ENT_QUOTES, 'UTF-8');
   $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');


   $total_service = 0;

   $check_bookings = $conn->prepare("SELECT * FROM bookings WHERE check_in = ?");
   $check_bookings->execute([$check_in]);

   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      $total_service += $fetch_bookings['service'];
   }

   if($total_service >= 30){
      $warning_msg[] = 'Sevice are Full';
   }else{

      $verify_bookings = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND service = ? AND check_in = ? AND description = ?");
      $verify_bookings->execute([$user_id, $name, $email, $number, $service, $check_in, $description]);

      if($verify_bookings->rowCount() > 0){
         $warning_msg[] = 'It is booked already!';
      }else{
         $book_room = $conn->prepare("INSERT INTO bookings(booking_id, user_id, name, email, number, service, check_in, description) VALUES(?,?,?,?,?,?,?,?)");
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
   

   $verify_message = $conn->prepare("SELECT * FROM messages WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $verify_message->execute([$name, $email, $number, $message]);

   if($verify_message->rowCount() > 0){
      $warning_msg[] = 'message sent already!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO messages(id, name, email, number, message) VALUES(?,?,?,?,?)");
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

<?php include 'components/user_header.php'; ?>

<!-- home section starts  -->

<section class="home" id="home">

   <div class="swiper home-slider">

      <div class="swiper-wrapper">

         <div class="box swiper-slide">
            <img src="images/home-img-1.jpg" alt="">
            <div class="flex">
               <h3>Motor  Body Diagnosis</h3>
               <a href="#availability" class="btn">Check Availability</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="images/home-img-2.jpg" alt="">
            <div class="flex">
               <h3>Oil Change</h3>
               <a href="#reservation" class="btn">Make A Reservation</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="images/home-img-3.jpg" alt="">
            <div class="flex">
               <h3>Motor Body KIts</h3>
               <a href="#contact" class="btn">Contact Us</a>
            </div>
         </div>

      </div>

      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>

   </div>

</section>

<!-- home section ends -->

<!-- availability section starts  -->

<section class="availability" id="availability">

   <form action="" method="post">
      <div class="flex">
         <div class="box">
            <p>Check in <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>Services <span>*</span></p>
            <select name="service" class="input" required>
               <option value="1">1 service</option>
               <option value="2">2 service</option>
               <option value="3">3 service</option>
               <option value="4">4 service</option>
               <option value="5">5 service</option>
               <option value="6">6 service</option>
            </select>
         </div>
      </div>
      <input type="submit" value="check availability" name="check" class="btn">
   </form>

</section>

<!-- availability section ends -->

<!-- about section starts  -->

<section class="about" id="about">

<div class="row revers">
      <div class="image">
         <img src="#pic" alt="">
      </div>
      <div class="content">
         <h3>ABOUT US</h3>
         <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Animi laborum maxime eius aliquid temporibus unde?</p>
         <a href="#contact" class="btn">contact us</a>
      </div>
   </div>

   <div class="row">
      <div class="image">
         <img src="#pic" alt="">
      </div>
      <div class="content">
         <h3>Best Staff</h3>
         <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Animi laborum maxime eius aliquid temporibus unde?</p>
         <a href="#reservation" class="btn">make a reservation</a>
      </div>
   </div>

   <div class="row revers">
      <div class="image">
         <img src="#pic" alt="">
      </div>
      <div class="content">
         <h3>Best Service</h3>
         <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Animi laborum maxime eius aliquid temporibus unde?</p>
         <a href="#contact" class="btn">contact us</a>
      </div>
   </div>

   <div class="row">
      <div class="image">
         <img src="#pic" alt="">
      </div>
      <div class="content">
         <h3>Top Rated</h3>
         <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Animi laborum maxime eius aliquid temporibus unde?</p>
         <a href="#availability" class="btn">check availability</a>
      </div>
   </div>

</section>

<!-- about section ends -->

<!-- services section starts  -->

<section class="services">

   <div class="box-container">

      <div class="box">
         <img src="#pic" alt="">
         <h3>Top Reviews</h3>
         <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero, sunt?</p>
      </div>

      <div class="box">
         <img src="#pic" alt="">
         <h3>Affordable</h3>
         <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero, sunt?</p>
      </div>

      <div class="box">
         <img src="#pic" alt="">
         <h3>Top Tier Diognosis</h3>
         <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero, sunt?</p>
      </div>

      <div class="box">
         <img src="#pic" alt="">
         <h3>Decorations</h3>
         <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero, sunt?</p>
      </div>

      <div class="box">
         <img src="picture" alt="">
         <h3>Oil Change</h3>
         <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero, sunt?</p>
      </div>

      <div class="box">
         <img src="#pic" alt="">
         <h3>Body Kits</h3>
         <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero, sunt?</p>
      </div>

   </div>

</section>

<!-- services section ends -->

<!-- reservation section starts  -->

<section class="reservation" id="reservation">

   <form action="" method="post">
      <h3>Make a Reservation</h3>
      <div class="flex">
         <div class="box">
            <p>Name <span>*</span></p>
            <input type="text" name="name" maxlength="50" required placeholder="enter your name" class="input">
         </div>
         <div class="box">
            <p>Email <span>*</span></p>
            <input type="email" name="email" maxlength="50" required placeholder="enter your email" class="input">
         </div>
         <div class="box">
            <p>Number <span>*</span></p>
            <input type="number" name="number" maxlength="10" min="0" max="9999999999" required placeholder="enter your number" class="input">
         </div>
         <div class="box">
            <p>Service  <span>*</span></p>
            <select name="service" class="input" required>
               <option value="1" selected>Oil Change</option>
               <option value="2">Body Search</option>
               <option value="3">Change tire</option>
               <option value="4">Lunos</option>
               <option value="5">edo</option>
               <option value="6">estr</option>
            </select>
         </div>
         <div class="box">
            <p>Date of Booking<span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>Description <span>*</span></p>
            <input type="text" name="description" class="input" required>
         </div>
      </div>
      <input type="submit" value="book now" name="book" class="btn">
   </form>

</section>

<!-- reservation section ends -->

<!-- gallery section starts  -->

<section class="gallery" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="pic" class="swiper-slide" alt="">
         <img src="pic" class="swiper-slide" alt="">
         <img src="pic" class="swiper-slide" alt="">
         <img src="pic" class="swiper-slide" alt="">
         <img src="pic" class="swiper-slide" alt="">
         <img src="pic" class="swiper-slide" alt="">
      </div>
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
   </div>

</section>

<!-- gallery section ends -->

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

      <div class="faq">
         <h3 class="title">frequently asked questions</h3>
         <div class="box active">
            <h3>How to Cancel?</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Natus sunt aspernatur excepturi eos! Quibusdam, sapiente.</p>
         </div>
         <div class="box">
            <h3>Is there any Vacancy?</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa ipsam neque quaerat mollitia ratione? Soluta!</p>
         </div>
         <div class="box">
            <h3>What are payment methods?</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa ipsam neque quaerat mollitia ratione? Soluta!</p>
         </div>
         <div class="box">
            <h3>Where is the location?</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa ipsam neque quaerat mollitia ratione? Soluta!</p>
         </div>
         <div class="box">
            <h3>What is the cost?</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa ipsam neque quaerat mollitia ratione? Soluta!</p>
         </div>
      </div>

   </div>

</section>

<!-- contact section ends -->

<!-- reviews section starts  -->

<section class="reviews" id="reviews">

   <div class="swiper reviews-slider">

      <div class="swiper-wrapper">
         <div class="swiper-slide box">
            <img src="images/pic-1.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-2.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-3.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-4.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-5.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-6.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
      </div>

      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
   </div>

</section>

<!-- reviews section ends  -->





<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>