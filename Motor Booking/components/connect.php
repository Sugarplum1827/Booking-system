<?php
   $db_host = "localhost";
   $db_name = "booking";
   $db_user_name = "root";
   $db_user_pass = "";


   try {

   $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user_name, $db_user_pass);

      $conn ->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e){
         echo "connection failed: " . $e->getMessage();
      }

if (!function_exists('create_unique_id')) {
    function create_unique_id() {
    return uniqid(); 
}

    }


?>