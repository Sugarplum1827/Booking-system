<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include __DIR__ . '/../components/connect.php';


if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    header('location:login.php');
    exit;
}

$select_user = $conn->prepare("SELECT * FROM `users` WHERE user_id = ? LIMIT 1");
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Dashboard</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<!-- header section starts -->
<?php include '../components/verified_header.php'; ?>
<!-- header section ends -->

<!-- dashboard section starts -->
<section class="dashboard">
   <h1 class="heading">Your Dashboard</h1>

   <div class="box-container">

      <!-- Welcome box -->
      <div class="box">
         <h3>Welcome!</h3>
         <?php if ($fetch_user): ?>
            <p><?= htmlspecialchars($fetch_user['full_name']) ?></p>
            <a class="btn"> Profile</a>
         <?php else: ?>
            <p>User not found.</p>
         <?php endif; ?>
      </div>

      <!-- Pending bookings -->
      <div class="box">
         <?php
         $stmt = $conn->prepare("SELECT COUNT(*) FROM `bookings` WHERE user_id = ? AND complete = 0");
         $stmt->execute([$user_id]);
         $count_pending = $stmt->fetchColumn();
         ?>
         <h3><?= $count_pending; ?></h3>
         <p>Pending Bookings</p>
         <a href="bookings.php" class="btn">View Bookings</a>
      </div>

      <!-- Completed bookings -->
      <div class="box">
         <?php
         $stmt = $conn->prepare("SELECT COUNT(*) FROM `bookings` WHERE user_id = ? AND complete = 1");
         $stmt->execute([$user_id]);
         $count_completed = $stmt->fetchColumn();
         ?>
         <h3><?= $count_completed; ?></h3>
         <p>Completed Bookings</p>
         <a href="user_complete.php" class="btn">View Bookings</a>
      </div>

      <!-- Cancelled bookings -->
      <div class="box">
         <?php
         $stmt = $conn->prepare("SELECT COUNT(*) FROM `bookings` WHERE user_id = ? AND complete = 2");
         $stmt->execute([$user_id]);
         $count_cancelled = $stmt->fetchColumn();
         ?>
         <h3><?= $count_cancelled; ?></h3>
         <p>Cancelled Bookings</p>
         <a href="user_cancel.php" class="btn">View Bookings</a>
      </div>

   </div>
</section>
<!-- dashboard section ends -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>

<?php include '../components/message.php'; ?>

</body>
</html>
