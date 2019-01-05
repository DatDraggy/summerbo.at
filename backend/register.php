<?php
require_once('config.php');
require_once('funcs.php');
session_start();
if (!$config['regOpen'] || $_SESSION['secret'] === $config['secret']) {
  die();
}
session_commit();
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
  header('Location: register');
  die($status);
}

if (empty($_POST['firstname'])) {
  $status = 'First Name can\'t be empty.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $firstNamePost = $_POST['firstname'];
}
if (empty($_POST['lastname'])) {
  $status = 'Last Name can\'t be empty.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $lastNamePost = $_POST['lastname'];
}
if (empty($_POST['nickname'])) {
  $status = 'Nickname can\'t be empty.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $nicknamePost = $_POST['nickname'];
}
if (empty($_POST['dob'])) {
  $status = 'Birthday can\'t be empty.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
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
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $emailPost = $_POST['email'];
}
if (empty($_POST['password'])) {
  $status = 'Password can\'t be empty.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $passwordPost = $_POST['password'];
}
if (empty($_POST['passwordVerify'])) {
  $status = 'Password Confirmation can\'t be empty.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $passwordVerifyPost = $_POST['passwordVerify'];
}
if (empty($_POST['country'])) {
  $status = 'Country can\'t be empty.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $countryPost = $_POST['country'];
}
if (empty($_POST['tos'])) {
  $status = 'Please accept the ToS.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
}

$publicList = empty($_POST['publicList']) ? false : true;

if (preg_match('/[\sa-zA-Z]/', $firstNamePost) !== 1) {
  //ToDo: Test pregmatch
  $status = 'Illegal character in First Name.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $firstName = $firstNamePost;
}

if (preg_match('/[\sa-zA-Z]/', $lastNamePost) !== 1) {
  $status = 'Illegal character in Last Name';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
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
  $status = 'Invalid Birthdate Format. Please use the following format: YYYY-MM-DD';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
}
$dob = date('Y-m-d', $dobStamp);

if (strtotime('-1 day', strtotime(file_get_contents('https://isitef.com/?start'))) < strtotime('+18 years', strtotime($dob))) {
  $status = 'You have to be at least 18 years old on the day of the party.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
}

if (filter_var($emailPost, FILTER_VALIDATE_EMAIL)) {
  $email = $emailPost;
} else {
  $status = 'Invalid Email Format';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
}

//Check for used email

if ($passwordPost !== $passwordVerifyPost) {
  $status = 'Passwords do not match';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
}
$passValid = validatePassword($passwordPost);
if ($passValid !== true) {
  $status = 'Password invalid because ' . $passValid;
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
} else {
  $password = $passwordPost;
}
$hash = hashPassword($password);

if (strlen($countryPost) == 2) {
  $country = preg_replace('/[^A-Z]/', '', $countryPost);
}

$dbConnection = buildDatabaseConnection($config);
if ($dbConnection === false) {
  $status = 'Database Connection Broken. Notifications have been sent.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
}

$userId = newRegistration($firstName, $lastName, $nickname, $dob, $fursuiter, $sponsor, $email, $hash, $country, 0, time(), $publicList);
if ($userId === false) {
  $status = 'Unknown Error in Registration. Administrator has been notified.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
}
$confirmationLink = requestEmailConfirm($userId);
if ($confirmationLink === false) {
  mail($config['mail'], 'ERROR IN SUMMERBOAT REG URGENT', $userId . ' No token generate possible');
  $status = 'Unknown Error in Registration. Administrator has been notified';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: register');
  die($status);
}
sendEmail($email, 'Please Confirm Your Summerbo.at Registration', "Dear $nickname,

Thank you for your registration with the Summernights party.

Your current status is: NEW - Regnumber $userId

You first have to verify your email address and confirm your registration by clicking on the following link: <a href=\"$confirmationLink\">$confirmationLink</a>
Afterwards another mail will be sent.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
", true);

$status = 'Registration successful. Check your email for the confirmation link. (Check spam too)';
session_start();
$_SESSION['status'] = $status;
session_commit();
header('Location: login?reg');
die($status);
