<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include __DIR__ . '/../components/connect.php';

$warning_msg = [];

if (isset($_POST['submit'])) {
    $name = trim(strip_tags($_POST['name']));
    $pass = trim(strip_tags($_POST['pass']));

    $select = $conn->prepare("SELECT * FROM users WHERE full_name = ? LIMIT 1");
    $select->execute([$name]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass, $row['password'])) {
        if ($row['verified'] == 2) {
                if ($row['num_log'] < 2) {
            $reset = $conn->prepare("UPDATE users SET num_log = num_log + 1 WHERE id = ?");
            $reset->execute([$row['id']]);

            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_email'] = $row['gmail'];

            setcookie('user_id', $row['user_id'], time() + 60 * 60 * 24 * 30, '/', '', true, true);

            header('Location: /user/dashboard.php');
            exit;
            } else {
                $reset = $conn->prepare("UPDATE users SET num_log = 0 WHERE id = ?");
                $reset->execute([$row['id']]);
                $warning_msg[] = 'Maximum login attempts reached. Please verify your account.';

                $_SESSION['pending_user_id'] = $row['id'];
                $_SESSION['pending_user_email'] = $row['gmail'];

                header('Location: ../otp/login_otp.php');
                exit;
            }
        } else {
            $warning_msg[] = 'Account not verified. Please try again later.';
        }
    } else {
        $warning_msg[] = 'Incorrect username or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<section class="form-container" style="min-height: 100vh;">
   <form action="" method="POST">
      <h3>welcome back!</h3>
      <input type="text" name="name" placeholder="Enter username" maxlength="30" class="box" required>
      <input type="password" name="pass" placeholder="Enter password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Login Now" name="submit" class="btn">
      <p style="color: black; margin-bottom: 5px;">Don't have an account? <a href="signup.php">Sign up here</a></p>
   </form>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php 
if (!empty($warning_msg)) {
    foreach ($warning_msg as $msg) {
        echo '<script>swal("Warning", "' . htmlspecialchars($msg) . '", "warning");</script>';
    }
}
?>
</body>
</html>
