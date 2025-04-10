<?php
include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id']; 
}else{
   header('location: login.php');
   exit(); // Stop further execution
}

if(isset($_POST['delete'])){
   $delete_id = $_POST['delete_id']; 

   $verify_delete = $conn->prepare("SELECT * FROM `messages` WHERE id = ?");
   $verify_delete->execute([$delete_id]);

   if($verify_delete->rowCount() > 0){
      $delete_bookings = $conn->prepare("DELETE FROM `messages` WHERE id = ? LIMIT 1");
      $delete_bookings->execute([$delete_id]);
      $success_msg[] = 'Message deleted successfully!';
   }else{
      $warning_msg[] = 'Message already deleted or does not exist!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Messages</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="grid">
   <h1 class="heading">Messages</h1>
   <div class="box-container">
   <?php
      $select_messages = $conn->prepare("SELECT * FROM `messages` ORDER BY id DESC");
      $select_messages->execute();

      if($select_messages->rowCount() > 0){
         while($fetch_messages = $select_messages->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p><strong>Name:</strong> <span><?= htmlspecialchars($fetch_messages['name']); ?></span></p>
      <p><strong>Email:</strong> <span><?= htmlspecialchars($fetch_messages['email']); ?></span></p>
      <p><strong>Number:</strong> <span><?= htmlspecialchars($fetch_messages['number']); ?></span></p>
      <p><strong>Message:</strong> <span><?= nl2br(htmlspecialchars($fetch_messages['message'])); ?></span></p>
      <form action="" method="POST">
         <input type="hidden" name="delete_id" value="<?= $fetch_messages['id']; ?>">
         <input type="submit" value="Delete Message" onclick="return confirm('Delete this message?');" name="delete" class="btn">
      </form>
   </div>
   <?php
      }
   }else{
   ?>
   <div class="box" style="text-align: center;">
      <p>No messages found!</p>
      <a href="dashboard.php" class="btn">Go to Home</a>
   </div>
   <?php } ?>
   </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>

</body>
</html>
