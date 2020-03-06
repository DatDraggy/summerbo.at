<?php
require_once('config.php');
require_once('funcs.php');
header('Cache-Control: max-age=0');

$dbConnection = buildDatabaseConnection($config);

if (empty($_POST['party']) || !in_array($_POST['party'], [1, 2])){
  $status = 'You must select which party you want to attend.';
  errorStatus($status);
}else{
  $party = $_POST['party'];
}

$waitinglistCount = getWaitinglistCount($party);

if ($waitinglistCount === false || $waitinglistCount >= 25){
  $status = 'The waiting list is full. Please check back at a later date.';
  errorStatus($status);
}

if (empty($_POST['email'])) {
  $status = 'Email can\'t be empty.';
  errorStatus($status);
} else {
  $emailPost = strtolower($_POST['email']);
}if (filter_var($emailPost, FILTER_VALIDATE_EMAIL)) {
  $email = $emailPost;
} else {
  $status = 'Invalid Email Format';
  errorStatus($status);
}

if(checkWaitinglist($email, $party)){
  $status = 'This email is already on the waitinglist.';
  errorStatus($status);
}

if(addToWaitinglist($email, $party)){
  $status = 'Success! You will receive an email if there is a spot for you on the boat.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../login?reg');
} else {
  $status = 'Unknown Error. Administrator has been notified';
  errorStatus($status);
}