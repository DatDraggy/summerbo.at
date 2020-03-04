<?php
require_once('config.php');
require_once('funcs.php');
header('Cache-Control: max-age=0');
session_start();

if (strtotime($config['startReg']."T11:00:00") > time()) {
  if(empty($_SESSION['secret']) || $_SESSION['secret'] !== $config['secret']) {
    die('Reg Closed');
  }
}

session_commit();
/*
# Verify captcha
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}
$post_data = http_build_query(array('secret'   => $config['captchaSecret'],
                                    'response' => $_POST['g-recaptcha-response'],
                                    'remoteip' => $_SERVER['REMOTE_ADDR']
));
$opts = array('http' => array('method'  => 'POST',
                              'header'  => 'Content-type: application/x-www-form-urlencoded',
                              'content' => $post_data
)
);
$context = stream_context_create($opts);
$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
$result = json_decode($response);
if (!$result->success) {
  $status = 'reCAPTCHA Verification Failed. Did you forget to allow cookies?';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../register');
  die($status);
}*/

if (empty($_POST['party']) || !in_array($_POST['party'], [1, 2])){
  $status = 'You must select which party you want to attend.';
  errorStatus($status);
} else {
  $party = $_POST['party'];
}

$dbConnection = buildDatabaseConnection($config);
//if (!openSlots() && $_SESSION['secret'] !== $config['secret']) {
$attendees = getConfirmedAttendees($party);
if (($attendees === false || $attendees >= $config['attendeesMax'.$party]) && $_SESSION['secret'] !== $config['secret']){
  $status = 'Sadly we do not have any more slots available for the selected party. But remember to check back in! It might be possible that some slots will free up again.';
  errorStatus($status);
}

if (empty($_POST['firstname'])) {
  $status = 'First Name can\'t be empty.';
  errorStatus($status);
} else {
  $firstNamePost = $_POST['firstname'];
}
if (empty($_POST['lastname'])) {
  $status = 'Last Name can\'t be empty.';
  errorStatus($status);
} else {
  $lastNamePost = $_POST['lastname'];
}



if (empty($_POST['nickname'])) {
  $status = 'Nickname can\'t be empty.';
  errorStatus($status);
} else {
  $nicknamePost = $_POST['nickname'];
}
if (empty($_POST['efregid']) && !is_int($_POST['efregid'])) {
  $status = 'A valid EF registration is required.';
  errorStatus($status);
} else {
  $efregid = abs($_POST['efregid']);
}
if (empty($_POST['dob'])) {
  $status = 'Birthday can\'t be empty.';
  errorStatus($status);
} /*
if (!empty($_POST['day'])) {
  $dayofbirthPost = $_POST['day'];
}
if (!empty($_POST['month'])) {
  $monthofbirthPost = $_POST['month'];
}
if (!empty($_POST['year'])) {
  $yearofbirthPost = $_POST['year'];
}*/ else {
  $dobPost = $_POST['dob'];
}
if (empty($_POST['fursuiter'])) {
  $fursuiter = false;
} else {
  $fursuiter = true;
}
if (empty($_POST['sponsor'])) {
  $sponsor = false;
} else {
  $sponsor = true;
}
if (empty($_POST['email'])) {
  $status = 'Email can\'t be empty.';
  errorStatus($status);
} else {
  $emailPost = strtolower($_POST['email']);
}
if (empty($_POST['password'])) {
  $status = 'Password can\'t be empty.';
  errorStatus($status);
} else {
  $passwordPost = $_POST['password'];
}
if (empty($_POST['passwordVerify'])) {
  $status = 'Password Confirmation can\'t be empty.';
  errorStatus($status);
} else {
  $passwordVerifyPost = $_POST['passwordVerify'];
}
if (empty($_POST['country'])) {
  $status = 'Country can\'t be empty.';
  errorStatus($status);
} else {
  $countryPost = $_POST['country'];
}
if (empty($_POST['tos'])) {
  $status = 'Please accept the ToS.';
  errorStatus($status);
}

$publicList = empty($_POST['publicList']) ? false : true;

/*if (preg_match('/[^\sa-zA-Z]/', $firstNamePost) === 1) {
  $status = 'Illegal character in First Name.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../register');
  die($status);
} else {*/
  $firstName = htmlspecialchars($firstNamePost);
//}

/*if (preg_match('/[^\sa-zA-Z]/', $lastNamePost) === 1) {
  $status = 'Illegal character in Last Name';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../register');
  die($status);
} else {*/
  $lastName = htmlspecialchars($lastNamePost);
//}

//$nicknameRaw = str_replace(' ', '_', $nicknamePost);


if (preg_match('/^([A-Za-z0-9 ]*[A-Za-z0-9][A-Za-z0-9 ]*[^A-Za-z0-9 ]?[A-Za-z0-9 ]*[^A-Za-z0-9 ]?[A-Za-z0-9 ]*|[A-Za-z0-9 ]*[^A-Za-z0-9 ]?[A-Za-z0-9 ]*[A-Za-z0-9][A-Za-z0-9 ]*[^A-Za-z0-9 ]?[A-Za-z0-9 ]*|[A-Za-z0-9 ]*[^A-Za-z0-9 ]?[A-Za-z0-9 ]*[^A-Za-z0-9 ]?[A-Za-z0-9 ]*[A-Za-z0-9][A-Za-z0-9 ]*)$/', $nicknamePost) === 0) {
  $status = 'Illegal character in Nickname. 2 symbols, 2 spaces, 2-20 characters, ';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../register');
  die($status);
} else {
    $nickname = substr($nicknamePost, 0, 20);
}

$dobStamp = strtotime($dobPost);
if ($dobStamp === false) {
  $status = 'Invalid Birthdate Format. Please use the following format: DD.MM.YYYY';
  errorStatus($status);
}
$dob = date('Y-m-d', $dobStamp);

if (strtotime($config['start']) < strtotime('+18 years', strtotime($dob))) {
  $status = 'You have to be at least 18 years old on the day of the party.';
  errorStatus($status);
}

if (filter_var($emailPost, FILTER_VALIDATE_EMAIL)) {
  $email = $emailPost;
} else {
  $status = 'Invalid Email Format';
  errorStatus($status);
}

//Check for used email
try{
  $sql = "SELECT count(id) as count FROM users WHERE email = $email";
  $stmt=$dbConnection->prepare('SELECT count(id) as count FROM users WHERE email = :email');
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $row = $stmt->fetch();
}catch (PDOException $e){
  notifyOnException('Database Insert', $config, $sql, $e);
  $status = 'Unknown Error in Registration. Administrator has been notified.';
  errorStatus($status);
}

if($row['count'] > 0){
  $status = 'Email already taken. Please login or choose another email.';
  errorStatus($status);
}

if ($passwordPost !== $passwordVerifyPost) {
  $status = 'Passwords do not match';
  errorStatus($status);
}
$passValid = validatePassword($passwordPost);
if ($passValid !== true) {
  $status = 'Password invalid because ' . $passValid;
  errorStatus($status);
} else {
  $password = $passwordPost;
}
$hash = hashPassword($password);

if (strlen($countryPost) == 2) {
  $country = preg_replace('/[^A-Z]/', '', $countryPost);
}

if ($dbConnection === false) {
  $status = 'Database Connection Broken. Administrator has been notified.';
  errorStatus($status);
}


$userId = newRegistration($firstName, $lastName, $nickname, $dob, $fursuiter, $sponsor, $email, $hash, $country, 0, time(), $publicList, $efregid, $party);
if ($userId === false) {
  $status = 'Unknown Error in Registration. Administrator has been notified.';
  errorStatus($status);
}
$confirmationLink = requestEmailConfirm($userId);
if ($confirmationLink === false) {
  mail($config['mail'], 'ERROR IN SUMMERBOAT REG URGENT', $userId . ' No token generate possible');
  $status = 'Unknown Error in Registration. Administrator has been notified';
  errorStatus($status);
}


$status = 'Registration successful. Check your email for the confirmation link. (Check spam too)';
session_start();
$_SESSION['status'] = $status;
session_commit();
header('Location: ../login?reg');

sendEmail($email, 'Please Confirm Your Summerbo.at Registration', "Dear $nickname,

Thank you for your registration for Summerbo.at: All Paws on Deck!

Registration Number $userId

Your current status is: {$config['status'][0]}

The next step in your registration process is to verify your email address and confirm your registration. Please click on the following link to verify:
<a href=\"$confirmationLink\">$confirmationLink</a>
After verification you will receive another email.

If you have any questions, please send us a message. Simply reply to this email or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
", true);