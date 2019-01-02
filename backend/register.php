<?php
require_once('config.php');
require_once('funcs.php');
if(!$config['regOpen']){
  die();
}
if (!empty($_POST['firstname'])) {
  $firstNamePost = $_POST['firstname'];
}
else {
  die('no first name');
}
if (!empty($_POST['lastname'])) {
  $lastNamePost = $_POST['lastname'];
}
else {
  die('no last name');
}
if (!empty($_POST['nickname'])) {
  $nicknamePost = $_POST['nickname'];
}
else {
  die('no nickname');
}
if (!empty($_POST['dob'])) {
  $dobPost = $_POST['dob'];
}
  /*
if (!empty($_POST['day'])) {
  $dayofbirthPost = $_POST['day'];
}
if (!empty($_POST['month'])) {
  $monthofbirthPost = $_POST['month'];
}
if (!empty($_POST['year'])) {
  $yearofbirthPost = $_POST['year'];
}*/
else {
  die('no date of birth');
}
if (!empty($_POST['fursuiter'])) {
  $fursuiter = true;
}
else {
  $fursuiter = false;
}
if (!empty($_POST['sponsor'])) {
  $sponsor = true;
}
else {
  $sponsor = false;
}
if (!empty($_POST['email'])) {
  $emailPost = $_POST['email'];
}
else {
  die('no email');
}
if (!empty($_POST['password'])) {
  $passwordPost = $_POST['password'];
}
else {
  die('no password');
}
if (!empty($_POST['passwordVerify'])) {
  $passwordVerifyPost = $_POST['passwordVerify'];
}
else {
  die('no password verify');
}
if (!empty($_POST['country'])) {
  $countryPost = $_POST['country'];
}
else {
  die('no country');
}
if (!empty($_POST['tos'])) {
  $tos = true;
}
else {
  $tos = false;
}
if (!empty($_POST['publicList'])) {
  $publicList = true;
}
else {
  $publicList = false;
}

if (preg_match('/[\sa-zA-Z]/', $firstNamePost) !== 1) {
  echo 'Illegal Char in First Name';
}
else {
  $firstName = $firstNamePost;
}

if (preg_match('/[\sa-zA-Z]/', $lastNamePost) !== 1) {
  echo 'Illegal Char in Last Name';
}
else {
  $lastName = $lastNamePost;
}

//$nicknameRaw = str_replace(' ', '_', $nicknamePost);
$nicknameRaw = $nicknamePost;
$nickname = preg_replace('/[^\w-. ~]/', '', $nicknameRaw);

/*if(is_numeric($dayofbirthPost) && is_numeric($monthofbirthPost) && is_numeric($yearofbirthPost)) {
  $dobPost = "$yearofbirthPost-$monthofbirthPost-$dayofbirthPost";
  $dobStamp = strtotime($dobPost);
  if ($dobStamp === false) {
    die('Invalid Birthdate Format. Please use the following format: YYYY-MM-DD');
  }
  $dob = date('Y-m-d', $dobStamp);
}
else{
  die('Bad Date of Birth');
}*/
$dobStamp = strtotime($dobPost);
if($dobStamp === false){
  die('Invalid Birthdate Format. Please use the following format: YYYY-MM-DD');
}
$dob = date('Y-m-d', $dobStamp);

if (filter_var($emailPost, FILTER_VALIDATE_EMAIL)) {
  $email = $emailPost;
}
else {
  die('Invalid Email');
}

if ($passwordPost !== $passwordVerifyPost) {
  die('Do not match');
}
$passValid = validatePassword($passwordPost);
if ($passValid !== true) {
  die('Password invalid because ' . $passValid);
}
else {
  $password = $passwordPost;
}
$hash = hashPassword($password);

if (strlen($countryPost) == 2) {
  $country = preg_replace('/[^A-Z]/', '', $countryPost);
}

$dbConnection = buildDatabaseConnection($config);
if ($dbConnection === false) {
  die('Broken Database Connection');
}

$userId = newRegistration($firstName, $lastName, $nickname, $dob, $fursuiter, $sponsor, $email, $hash, $country, 0, time(), $publicList);
if ($userId === false) {
  die('Reg failed for some reason');
}
$confirmationLink = requestEmailConfirm($userId);
if ($confirmationLink === false) {
  mail($config['mail'], 'ERROR IN SUMMERBOAT REG URGENT', $userId . ' No token generate possible');
  die('Database Error');
}
sendEmail($email, 'Please Confirm Your Summerbo.at Registration', "Dear $nickname,

Thank you for your registration with the Summernights party.

Your current status is: NEW - Regnumber $userId

You first have to verify your email address and confirm your registration by clicking on the following link: <a href=\"$confirmationLink\">$confirmationLink</a>
Afterwards another mail will be sent.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
", true);

echo 'Done. You\'ve got mail';
