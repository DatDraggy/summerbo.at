<?php
require_once('../backend/config.php');
require_once('../backend/funcs.php');
header('Cache-Control: max-age=0');
session_start();
if (empty($_SESSION['userId']) || empty($_POST['nickname']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['efregid'])) {
  $status = 'Missing details. Check Nickname, Email and old Password.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ./');
  die($status);
}

if($config['readOnly'] === true){
  $status = 'Read-Only Mode Activated.';
  onError($status);
}

$dbConnection = buildDatabaseConnection($config);
$userId = $_SESSION['userId'];
////////////////////////
// Check Reg Validity //
if (!checkRegValid($userId)) {
  //If invalid, kill that bih
  /*if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
  }
  session_destroy();*/
  header('Location: ../login');
  die();
}
// Check Reg Validity //
////////////////////////
session_commit();
$nicknamePost = $_POST['nickname'];
if (preg_match('/[^\w\-. ~]/', $nicknamePost) === 1) {
  $status = 'Illegal character in Nickname';
  onError($status);
} else {
  $nickname = $nicknamePost;
}
$newEmailPost = strtolower($_POST['email']);
if (filter_var($newEmailPost, FILTER_VALIDATE_EMAIL)) {
  $newEmail = $newEmailPost;
} else {
  $status = 'Invalid Email Format';
  onError($status);
}
if (empty($_POST['dob'])) {
  $status = 'Birthday can\'t be empty.';
  onError($status);
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
if (empty($_POST['country'])) {
  $status = 'Country can\'t be empty.';
  onError($status);
} else {
  $countryPost = $_POST['country'];
}
$efregidPost = $_POST['efregid'];
if (is_numeric($efregidPost)) {
  $efregid = $efregidPost;
} else {
  $status = 'Invalid EF Reg ID';
  onError($status);
}

if (empty($_POST['party']) || !in_array($_POST['party'], [1, 2])){
    $status = 'You must select which party you want to attend.';
    onError($status);
} else {
    $party = (int) $_POST['party'];
}

$attendees = getConfirmedAttendees($party);
$currentParty = getRegDetails($userId, 'party')['party'];
if ($party !== $currentParty && ($attendees === false || $attendees >= $config['attendeesMax'.$party])){
    $status = 'Sadly we do not have any more slots available for the selected party. But remember to check back in! It might be possible that some slots will free up again.';
    onError($status);
}

/////////////////////
// Password Verify //
$hash = checkPassword($userId, $_POST['password']);

if ($hash === false) {
  $status = 'Incorrect Password';
  onError($status);
}
// Password Verify //
/////////////////////

/////////////////////
// Password Change //
if (!empty($_POST['passwordNew'])) {
  $passwordNew = $_POST['passwordNew'];
  if (empty($_POST['passwordNewVerify']) || $passwordNew !== $_POST['passwordNewVerify']) {
    $status = 'Passwords do not match';
    onError($status);
  }
  $valid = validatePassword($passwordNew);
  if ($valid !== true) {
    $status = $valid;
    onError($status);
  }
  $hash = hashPassword($passwordNew);
}
// Sponsor Upgrade //
/////////////////////

if (empty($_POST['fursuiter'])) {
  $fursuiter = false;
} else {
  $fursuiter = true;
}
if (empty($_POST['publiclist'])) {
  $list = false;
} else {
  $list = true;
}
$dobStamp = strtotime($dobPost);
if ($dobStamp === false) {
  $status = 'Invalid Birthdate Format. Please use the following format: DD.MM.YYYY';
  onError($status);
}
$dob = date('Y-m-d', $dobStamp);

if (strtotime($config['start']) < strtotime('+18 years', strtotime($dob))) {
  $status = 'You have to be at least 18 years old on the day of the party.';
  onError($status);
}

$country = preg_replace('/[^A-Z]/', '', $countryPost);
if (strlen($country) != 2) {
  $status = 'Invalid country';
  onError($status);
}
/////////////////////
// Sponsor Upgrade //
if (empty($_POST['sponsor'])) {
  $sponsorNew = false;
} else {
  $sponsorNew = true;
}
try {
  $sql = "SELECT email, sponsor FROM users WHERE id = $userId";
  $stmt = $dbConnection->prepare('SELECT email, sponsor FROM users WHERE id = :userId');
  $stmt->bindParam(':userId', $userId);
  $stmt->execute();
  $row = $stmt->fetch();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}
// Sponsor Upgrade //
/////////////////////

$sponsorOld = $row['sponsor'];
$oldEmail = $row['email'];

//////////////////
// Email Change //
$confirmationLink = false;
$emailText = '';
if ($oldEmail !== $newEmail) {
  try {
    $sql = "SELECT email FROM users WHERE email = '$newEmail' OR email_new = '$newEmail'";
    $stmt = $dbConnection->prepare('SELECT email FROM users WHERE email = :newEmail OR email_new = :newEmail2');
    $stmt->bindParam(':newEmail', $newEmail);
    $stmt->bindParam(':newEmail2', $newEmail);
    $stmt->execute();
    $stmt->fetchAll();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  if ($stmt->rowCount() > 0) {
    $status = 'This email address is already taken.';
    session_start();
    $_SESSION['status'] = $status;
    session_commit();
    header('Location: ./');
    die($status);
  }
  else {
    try {
      $sql = "UPDATE users SET email_new = $newEmail WHERE id = $userId";
      $stmt = $dbConnection->prepare('UPDATE users SET email_new = :email WHERE id = :userId');
      $stmt->bindParam(':email', $newEmail);
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();
    } catch (PDOException $e) {
      notifyOnException('Database Update', $config, $sql, $e);
    }
    if ($stmt->rowCount() === 1) {
      $confirmationLink = requestEmailConfirm($userId, 'emailold');
      $emailText = '. Check your old email.';
    }
  }
}
// Email Change //
//////////////////

/////////////////////////////////
// Update Nickname, Fursuit, Country and DoB //
try {
  $sql = "UPDATE users SET nickname = $nickname, fursuiter = $fursuiter, country = $country, dob = $dob, list = :list, hash = $hash, efregid = $efregid, party = $party WHERE id = $userId";
  $stmt = $dbConnection->prepare('UPDATE users SET nickname = :nickname, fursuiter = :fursuiter, country = :country, dob = :dob, list = :list, hash = :hash, efregid = :efregid, party = :party WHERE id = :userId');
  $stmt->bindParam(':nickname', $nickname);
  $stmt->bindParam(':fursuiter', $fursuiter);
  $stmt->bindParam(':country', $country);
  $stmt->bindParam(':dob', $dob);
  $stmt->bindParam(':list', $list);
  $stmt->bindParam(':hash', $hash);
  $stmt->bindParam(':efregid', $efregid);
  $stmt->bindParam(':party', $party);
  $stmt->bindParam(':userId', $userId);
  $stmt->execute();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}
// Update Nickname and Fursuit //
/////////////////////////////////

$status = 'Details changed successfully' . $emailText;
session_start();
$_SESSION['statusSuccess'] = $status;
$_SESSION['hash'] = $hash;
session_commit();
header('Location: ./');

if ($sponsorNew === true && $sponsorOld == 0) {
  upgradeToSponsor($userId);
  sendStaffNotification($userId, "<a href=\"https://summerbo.at/admin/view?type=reg&id=$userId\">Attendee $userId</a> upgraded to sponsor.");
}
/*else if ($sponsorNew === false && $sponsorOld == 1){
  downgradeSponsor($userId);
}*/

if ($confirmationLink !== false) {
  sendEmail($oldEmail, 'Email Change Confirmation', "Dear $nickname, 

You requested to change your email. Please follow this link to confirm: <a href=\"$confirmationLink\">$confirmationLink</a>

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
");
}

function onError($status){
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ./');
  die($status);
}