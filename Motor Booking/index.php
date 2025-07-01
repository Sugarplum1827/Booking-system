<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include __DIR__ . '/components/connect.php';


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


   if($total_service > 1){
      $warning_msg[] = 'service are not available';
   }else{
      $success_msg[] = 'service are available';
   }

}

  if (isset($_POST['book'])) {
    $booking_id = create_unique_id();
    $user_id = create_unique_id(); 

    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $number = htmlspecialchars($_POST['number'], ENT_QUOTES, 'UTF-8');
    $service = htmlspecialchars($_POST['service'], ENT_QUOTES, 'UTF-8');
    $check_in = htmlspecialchars($_POST['check_in'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');

    $_SESSION['booking_data'] = compact('booking_id', 'user_id', 'name', 'email', 'number', 'service', 'check_in', 'description');
    $_SESSION['user_email'] = $email;
    header('Location: /otp/send_otp.php');
    exit;
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
   <title>THAI KIM MOTORS</title>
   <!--  add logo  <link rel="icon" type="image/x-icon" href="/images/favicon.ico"> -->

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
               <option value="1">CHANGE OIL</option>
               <option value="2">CVT CLEANING</option>
               <option value="3">FRONT SHOCK REPACK</option>
               <option value="4">BRAKE CLEANING</option>
               <option value="5">PARTS AND ACCESSORIES</option>
               <option value="6">VULCANIZING</option>
               <option value="7">MAGNETO REPAINT</option>
               <option value="8">FI CLEANING</option>
               <option value="9">SENSOR DIAGNOSTICS</option>
               <option value="10">PMS (Preventive Maintenance Service)</option>
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
         <p>Welcome to THAI KIM MOTORSHOP, your trusted destination for high-quality automotive parts and expert vehicle services. We have been committed to delivering the best products and outstanding customer care to automotive enthusiasts and everyday drivers alike</p>
         <a href="#contact" class="btn">contact us</a>
      </div>
   </div>

   <div class="row">
      <div class="image">
         <img src="#pic" alt="">
      </div>
      <div class="content">
         <h3>Best Staff</h3>
         <p>"Our Team, Your Trusted Automotive Experts."</p>
         <a href="#reservation" class="btn">Make a reservation</a>
      </div>
   </div>

   <div class="row revers">
      <div class="image">
         <img src="#pic" alt="">
      </div>
      <div class="content">
         <h3>Best Service</h3>
         <p>"Excellence in Every Mile, Service You Can Trust"</p>
         <a href="#contact" class="btn">contact us</a>
      </div>
   </div>

   <div class="row">
      <div class="image">
         <img src="#pic" alt="">
      </div>
      <div class="content">
         <h3>Top Rated</h3>
         <p>“Preventive Maintenance Service is available for 20,000 plus odometers motorcycle for affordable price only”</p>
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
         <p>Our customers consistently rate us 5 stars for quality, service, and reliability — your satisfaction is our reputation</p>
      </div>

      <div class="box">
         <img src="#pic" alt="">
         <h3>Affordable</h3>
         <p>We offer competitive pricing to make expert automotive services accessible, without cutting corners on quality</p>
      </div>

      <div class="box">
         <img src="#pic" alt="">
         <h3>Top-Tier Diagnosis</h3>
         <p>We use advanced diagnostic tools to accurately identify issues — saving you time, money, and future problems</p>
      </div>

      <div class="box">
         <img src="#pic" alt="">
         <h3>Decorations</h3>
         <p>From decals to detailing, we help your vehicle reflect your style with eye-catching and professional enhancements</p>
      </div>

      <div class="box">
         <img src="picture" alt="">
         <h3>Time</h3>
         <p>We value your time — our efficient process ensures quick turnarounds without sacrificing quality</p>
      </div>

      <div class="box">
         <img src="#pic" alt="">
         <h3>Body Kits</h3>
         <p>Upgrade your motorcycle’s look and performance with our expertly installed, high-quality body kits tailored to your preferences</p>
      </div>

   </div>

</section>

<!-- services section ends -->

<!-- reservation section starts  -->


<!-- reservation section ends -->

<!-- gallery section starts  -->

<section class="gallery" id="gallery">
   <h3 style="font-size: 3rem; color: beige; text-align: center;">Our Gallery</h3>

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="images/gallery-1.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-2.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-3.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-4.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-5.jpg" class="swiper-slide" alt="">
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
</div>
         <div class="box">
            <h3>Is there any Vacancy?</h3>
            <p> Available vacancies are listed at top of this page. If you don’t see a role that fits but are still interested, feel free to reach out — we’d love to hear from you</p>
         </div>
         <div class="box">
            <h3>What are payment methods?</h3>
            <p>Payment methods are GCASH and cash only </p>
         </div>
         <div class="box">
            <h3>Where is the location?</h3>
            <p>928 Bilibid Viejo St. Quiapo,Manila</p>
         </div>
         <div class="box">
            <h3>What is the cost?</h3>
            <p>The cost of our services varies depending on the type of work required. We offer a wide range of automotive solutions, and pricing is tailored to match the specific service and vehicle needs. For an accurate quote, feel free to contact us or visit our shop for a quick assessment.</p>
         </div>
      </div>

   </div>

</section>

<!-- contact section ends -->

<!-- reviews section starts  -->

<section class="reviews" id="reviews">

   <h3 style="font-size: 3rem; color: beige; text-align: center">Our Team</h3>



   <div class="swiper reviews-slider">
      <div class="swiper-wrapper">
         <div class="swiper-slide box">
            <img src="images/mechanic-1.png" alt="">
            <h3>Kim Ibrahim</h3>
         </div>
         <div class="swiper-slide box">
            <img src="images/mechanic-2.png" alt="">
            <h3>Naifh Macadaag</h3>
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