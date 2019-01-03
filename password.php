<?php
require_once('backend/config.php');
require_once('backend/funcs.php');
if (!empty($_GET['token'])) {
  $token = $_GET['token'];
  $dbConnection = buildDatabaseConnection($config);

  if(canChangePassword($token)){

  }
}
else {
  header('Location: login');
  die();
}