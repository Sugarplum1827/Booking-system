<?php

include '/home/discipolo/com_progs/school_proj/booking_system/project/components/connect.php';

setcookie('admin_id', '', time() - 1, '/');

header('location:../admin/login.php');

?>