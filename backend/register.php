<?php
require_once('config.php');
require_once('funcs.php');
if (!$config['regOpen']) {
  die();
}
#
# Verify captcha
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}
$post_data = http_build_query(
  array(
    'secret' => $config['captchaSecret'],
    'response' => $_POST['g-recaptcha-response'],
    'remoteip' => $_SERVER['REMOTE_ADDR']
  )
);
$opts = array('http' =>
                array(
                  'method'  => 'POST',
                  'header'  => 'Content-type: application/x-www-form-urlencoded',
                  'content' => $post_data
                )
);
$context  = stream_context_create($opts);
$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
$result = json_decode($response);
if (!$result->success) {
  throw new Exception('reCAPTCHA Verification Failed', 1);
}
if (empty($_POST['firstname'])) {
  die('no first name');
} else {
  $firstNamePost = $_POST['firstname'];
}
if (empty($_POST['lastname'])) {
  die('no last name');
} else {
  $lastNamePost = $_POST['lastname'];
}
if (empty($_POST['nickname'])) {
  die('no nickname');
} else {
  $nicknamePost = $_POST['nickname'];
}
if (empty($_POST['dob'])) {
  die('no date of birth');
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
  die('no email');
} else {
  $emailPost = $_POST['email'];
}
if (empty($_POST['password'])) {
  die('no password');
} else {
  $passwordPost = $_POST['password'];
}
if (empty($_POST['passwordVerify'])) {
  die('no password verify');
} else {
  $passwordVerifyPost = $_POST['passwordVerify'];
}
if (empty($_POST['country'])) {
  die('no country');
} else {
  $countryPost = $_POST['country'];
}
if (empty($_POST['tos'])) {
  $tos = false;
} else {
  $tos = true;
}
$publicList = empty($_POST['publicList']) ? false : true;

if (preg_match('/[\sa-zA-Z]/', $firstNamePost) !== 1) {
  //ToDo: Test pregmatch
  echo 'Illegal Char in First Name';
} else {
  $firstName = $firstNamePost;
}

if (preg_match('/[\sa-zA-Z]/', $lastNamePost) !== 1) {
  echo 'Illegal Char in Last Name';
} else {
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
if ($dobStamp === false) {
  die('Invalid Birthdate Format. Please use the following format: YYYY-MM-DD');
}
$dob = date('Y-m-d', $dobStamp);

if (filter_var($emailPost, FILTER_VALIDATE_EMAIL)) {
  $email = $emailPost;
} else {
  die('Invalid Email');
}

if ($passwordPost !== $passwordVerifyPost) {
  die('Do not match');
}
$passValid = validatePassword($passwordPost);
if ($passValid !== true) {
  die('Password invalid because ' . $passValid);
} else {
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
