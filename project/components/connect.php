<?php
   $db_host = "localhost";
   $db_name = "project_db";
   $db_user_name = "testuser";
   $db_user_pass = "3k9b0r4b";


   try {

   $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user_name, $db_user_pass);

      $conn ->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e){
         echo "connection failed: " . $e->getMessage();
      }

   function create_unique_id(){
      $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
      $rand = array();
      $length = strlen($str) - 1;

      for($i = 0; $i < 20; $i++){
         $n = mt_rand(0, $length);
         $rand[] = $str[$n];
      }
      return implode($rand);
   }

?>