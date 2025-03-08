<?php

include '/home/discipolo/Computer programs/School projects/Booking system/project/components/connect.php';

setcookie('admin_id', '', time() - 1, '/');

header('location:../admin/login.php');

?>