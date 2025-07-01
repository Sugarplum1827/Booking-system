<?php

include __DIR__ . '/../components/connect.php';

setcookie('admin_id', '', time() - 1, '/');

header('location:../admin/login.php');

?>