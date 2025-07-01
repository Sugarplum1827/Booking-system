<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../components/connect.php';
session_start();

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    header('location:login.php');
    exit;
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
$servicePrices = [
    1 => 500,
    2 => 800,
    3 => 1000,
    4 => 600,
    5 => 1500,
    6 => 300,
    7 => 700,
    8 => 850,
    9 => 1200,
    10 => 1800
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Your Completed Bookings</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   
<!-- header section starts  -->
<?php include '../components/verified_header.php'; ?>
<!-- header section ends -->

<!-- bookings section starts  -->

<section class="grid">
   <h1 class="heading">Your Completed Bookings</h1>

   <div class="box-container">
   <?php
      $select_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE complete = 1 AND user_id = ?");
      $select_bookings->execute([$user_id]);
      
      if ($select_bookings->rowCount() > 0){
         while($fetch_bookings = $select_bookings->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p>Booking ID: <span><?= htmlspecialchars($fetch_bookings['booking_id']); ?></span></p>
      <p>Name: <span><?= htmlspecialchars($fetch_bookings['name']); ?></span></p>
      <p>Email: <span><?= htmlspecialchars($fetch_bookings['email']); ?></span></p>
      <p>Number: <span><?= htmlspecialchars($fetch_bookings['number']); ?></span></p>
      <p>Check-in: <span><?= htmlspecialchars($fetch_bookings['check_in']); ?></span></p>
      <p>Service: <span><?= $serviceNames[$fetch_bookings['service']] ?? 'Unknown Service'; ?></span></p>
      <p>Price: <span><?= $fetch_bookings['price'] ?? 'Unknown Price'; ?></span></p>
      <p>Description: <span><?= htmlspecialchars($fetch_bookings['description']); ?></span></p>
      <p>Status: <strong>✅ COMPLETE</strong></p>
   </div>
   <?php
         }
      } else {
   ?>
   <div class="box" style="text-align: center;">
      <p>No completed bookings found!</p>
      <a href="dashboard.php" class="btn">Go to Home</a>
   </div>
   <?php
      }
   ?>
   </div>
</section>

<!-- bookings section ends -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>

<?php include '../components/message.php'; ?>

</body>
</html>
