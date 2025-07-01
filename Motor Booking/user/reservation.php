<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include __DIR__ . '/../components/connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header('Location: /user/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

$success_msg = [];
$warning_msg = [];

$get_user = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$get_user->execute([$user_id]);

if ($get_user->rowCount() > 0) {
    $user = $get_user->fetch(PDO::FETCH_ASSOC);
    $full_name = $user['full_name'];
    $email = $user['gmail'];
    $phone = $user['phone_number'];
} else {
    $warning_msg[] = "User not found.";
}
$service_prices = [
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

if (isset($_POST['book'])) {
    $booking_id = create_unique_id();
    $service = (int)strip_tags($_POST['service']);
    $check_in_date = strip_tags($_POST['check_in_date']);
    $check_in_time = strip_tags($_POST['check_in_time']);
    $description = strip_tags($_POST['description']);
    $price = $service_prices[$service] ?? 0;

    $check_in = $check_in_date . ' ' . $check_in_time . ':00';

    $current_datetime = date('Y-m-d H:i:s');
    if ($check_in <= $current_datetime) {
        $warning_msg[] = 'Please choose a future date and time.';
    } else {
        $check_duplicate = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND check_in = ? AND complete = 0");
        $check_duplicate->execute([$user_id, $check_in]);

        if ($check_duplicate->rowCount() > 0) {
            $warning_msg[] = 'You already have a booking on this date and time.';
        } else {
            $_SESSION['booking_data'] = [
                'booking_id' => $booking_id,
                'user_id' => $user_id,
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'service' => $service,
                'check_in' => $check_in,
                'description' => $description,
                'price' => $price
            ];
            $_SESSION['user_email'] = $email;

            header('Location: /otp/send_otp.php');
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Reservation</title>
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      body {
         font-family: Arial, sans-serif;
         margin: 0;
         padding: 0;
         background-color: #fff;
      }

      .reservation {
         display: flex;
         justify-content: center;
         align-items: center;
         min-height: 100vh;
         padding: 2rem;
         background-color: #fff;
      }

      form {
         background-color: #f5f5dc;
         padding: 2.5rem;
         border-radius: 1rem;
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
         max-width: 600px;
         width: 100%;
      }

      form h3 {
         text-align: center;
         font-size: 3rem;
         margin-bottom: 1.5rem;
      }

      .form-group {
         margin-bottom: 1.2rem;
      }

      .form-group p {
         margin-bottom: 0.5rem;
         font-size: 1.1rem;
         font-weight: 600;
         color: #333;
      }

      .form-group input,
      .form-group select {
         width: 100%;
         padding: 0.9rem;
         font-size: 2rem;
         border: 1px solid #ccc;
         border-radius: 0.5rem;
         box-sizing: border-box;
      }

      .btn {
         display: block;
         width: 100%;
         padding: 1rem;
         font-size: 2rem;
         background-color: #8b4513;
         color: white;
         border: none;
         border-radius: 0.5rem;
         cursor: pointer;
         transition: background-color 0.3s ease;
         margin-top: 1rem;
      }

      .btn:hover {
         background-color: #a0522d;
      }

      .message {
         text-align: center;
         font-size: 1rem;
         margin-bottom: 1rem;
      }

      .message.success {
         color: green;
      }

      .message.warning {
         color: red;
      }
   </style>
</head>
<body>

<?php include '../components/verified_header.php'; ?>

<section class="reservation">
   <form method="post">
      <h3>Make a Reservation</h3>

      <?php foreach ($success_msg as $msg): ?>
         <div class="message success"><?= $msg ?></div>
      <?php endforeach; ?>

      <?php foreach ($warning_msg as $msg): ?>
         <div class="message warning"><?= $msg ?></div>
      <?php endforeach; ?>

      <!-- Hidden Fields -->
      <input type="hidden" name="name" value="<?= htmlspecialchars($full_name) ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
      <input type="hidden" name="number" value="<?= htmlspecialchars($phone) ?>">

      <div class="form-group">
         <p>Name:</p>
         <input type="text" value="<?= htmlspecialchars($full_name) ?>" disabled>
      </div>

      <div class="form-group">
         <p>Email:</p>
         <input type="email" value="<?= htmlspecialchars($email) ?>" disabled>
      </div>

      <div class="form-group">
         <p>Phone:</p>
         <input type="text" value="<?= htmlspecialchars($phone) ?>" disabled>
      </div>

       <div class="form-group">
            <p>Service <span>*</span></p>
            <select name="service" id="service-select" required onchange="updatePrice()">
                <option value="1" data-price="500">Oil Change</option>
                <option value="2" data-price="800">CVT CLEANING </option>
                <option value="3" data-price="1000">FRONT SHOCK REPACK </option>
                <option value="4" data-price="600">BRAKE CLEANING </option>
                <option value="5" data-price="1500">PARTS AND ACCESSORIES </option>
                <option value="6" data-price="300">VULCANIZING </option>
                <option value="7" data-price="700">MAGNETO REPAINT </option>
                <option value="8" data-price="850">FI CLEANING</option>
                <option value="9" data-price="1200">SENSOR DIAGNOSTICS </option>
                <option value="10" data-price="1800">PMS </option>
            </select>
         <h3 id="price-display">Price: ₱<span id="price-amount">500</span></h3>
        </div>

      <div class="form-group">
   <p>Date of Booking <span>*</span></p>
   <input type="date" name="check_in_date" required>
</div>

<div class="form-group">
   <p>Time of Service <span>*</span></p>
   <select name="check_in_time" required>
      <?php
      $start = strtotime("08:00 AM"); // Start time
      $end = strtotime("06:00 PM");   // End time

      while ($start <= $end) {
         $label = date('h:i A', $start); // Display in 12-hour format
         $value = date('H:i', $start);   // Submit in 24-hour format
         echo "<option value=\"$value\">$label</option>";
         $start = strtotime('+30 minutes', $start); // 30-minute interval
      }
      ?>
   </select>
</div>



      <div class="form-group">
         <p>Description <span>*</span></p>
         <input type="text" name="description" required>
      </div>

      <input type="submit" value="Book Now" name="book" class="btn">
   </form>
</section>
<script>
function updatePrice() {
    const select = document.getElementById('service-select');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    document.getElementById('price-amount').textContent = price;
}
window.onload = updatePrice;
</script>

</body>
</html>
