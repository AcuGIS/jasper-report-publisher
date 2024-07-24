<?php
session_start();

foreach($_SESSION as $sk=>$sv){
  unset($_SESSION[$sk]);
}

header("location: /");
?>
