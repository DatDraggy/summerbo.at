<?php
require_once('../../backend/config.php');
session_start();
if (empty($_SESSION['userId']) || empty($_POST['nickname']) || empty($_POST['email']) || empty($_POST['passwordOld'])) {
  die('Details can\'t be empty');
}

$userId = $_SESSION['userId'];
$nickname = $_POST['nickname'];
$newEmail = $_POST['email'];
$dbConnection = buildDatabaseConnection($config);

////////////////////////
// Check Reg Validity //
if (!checkRegValid($userId)) {
//If invalid, kill that bih
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
  }
  session_destroy();
  header('Location: ../login.html');
  die();
}
// Check Reg Validity //
////////////////////////

/////////////////////
// Password Verify //
$hash = checkPassword($userId, $_POST['passwordOld']);

if ($hash === false) {
  die('Incorrect Password');
}
// Password Verify //
/////////////////////

/////////////////////
// Password Change //
if (!empty($_POST['password'])) {
  $passwordNew = $_POST['password'];
  if (empty($_POST['passwordVerify']) || $passwordNew !== $_POST['passwordVerify']) {
    die('Passwords do not match');
  }
  $valid = validatePassword($passwordNew);
  if ($valid !== true) {
    die($valid);
  }
  $hash = hashPassword($passwordNew);
}
// Sponsor Upgrade //
/////////////////////

if (!empty($_POST['fursuiter'])) {
  $fursuiter = true;
}
else {
  $fursuiter = false;
}

/////////////////////
// Sponsor Upgrade //
if (!empty($_POST['sponsor'])) {
  $sponsorNew = true;
}
else {
  $sponsorNew = false;
}
try {
  $sql = "SELECT sponsor FROM users WHERE id = $userId";
  $stmt = $dbConnection->prepare('SELECT sponsor FROM users WHERE id = :userId');
  $stmt->bindParam(':userId', $userId);
  $stmt->execute();
  $row = $stmt->fetch();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}
$sponsorOld = $row['sponsor'];
if ($sponsorNew === true && $sponsorOld == 0) {
  upgradeToSponsor($userId);
  sendStaffNotification($userId, "Attendee $userId upgraded to sponsor.");
}
// Sponsor Upgrade //
/////////////////////

//////////////////
// Email Change //
try {
  $sql = "SELECT email FROM users WHERE id = $userId";
  $stmt = $dbConnection->prepare('SELECT email FROM users WHERE id = :userId');
  $stmt->bindParam(':userId', $userId);
  $stmt->execute();
  $row = $stmt->fetch();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}
$oldEmail = $row['email'];
if ($oldEmail !== $newEmail) {
  try {
    $sql = "UPDATE users SET email_new = $newEmail WHERE id = $userId";
    $stmt = $dbConnection->prepare('UPDATE users SET email_new = $email WHERE id = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  if ($stmt->rowCount() === 1) {
    $confirmationLink = requestEmailConfirm($userId, true);

    sendEmail($oldEmail, 'Email Change Confirmation', "Dear $nickname, 

You requested to change your email to $newEmail. Please follow this link to confirm: <a href=\"$confirmationLink\">$confirmationLink</a>

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

Your Boat Party Crew
");
  }
}
// Email Change //
//////////////////

/////////////////////////////////
// Update Nickname and Fursuit //
try {
  $sql = "UPDATE users SET nickname = $nickname, fursuiter = $fursuiter WHERE id = $userId";
  $stmt = $dbConnection->prepare('UPDATE users SET nickname = :nickname, fursuiter = :fursuiter WHERE id = :userId');
  $stmt->bindParam(':nickname', $nickname);
  $stmt->bindParam(':fursuiter', $fursuiter);
  $stmt->bindParam(':userId', $userId);
  $stmt->execute();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}
// Update Nickname and Fursuit //
/////////////////////////////////