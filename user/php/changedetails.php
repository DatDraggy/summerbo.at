<?php
require_once('../../backend/config.php');
require_once('../../backend/funcs.php');
session_start();
if (empty($_SESSION['userId']) || empty($_POST['nickname']) || empty($_POST['email']) || empty($_POST['passwordOld'])) {
  $status = 'Missing details. Check Nickname, Email and old Password.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../details');
  die($status);
}

$dbConnection = buildDatabaseConnection($config);
$userId = $_SESSION['userId'];
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
session_commit();
$nicknamePost = $_POST['nickname'];
if (preg_match('/[^\w-. ~]/', $nicknamePost) === 1) {
  $status = 'Illegal character in Nickname';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../details');
  die($status);
} else {
  $nickname = $nicknamePost;
}
$newEmailPost = $_POST['email'];
if (filter_var($newEmailPost, FILTER_VALIDATE_EMAIL)) {
  $email = $newEmailPost;
} else {
  $status = 'Invalid Email Format';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../details');
  die($status);
}

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
    $status = 'Passwords do not match';
    session_start();
    $_SESSION['status'] = $status;
    session_commit();
    header('Location: ../details');
    die($status);
  }
  $valid = validatePassword($passwordNew);
  if ($valid !== true) {
    $status = $valid;
    session_start();
    $_SESSION['status'] = $status;
    session_commit();
    header('Location: ../details');
    die($status);
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

/////////////////////
// Sponsor Upgrade //
if (empty($_POST['sponsor'])) {
  $sponsorNew = false;
} else {
  $sponsorNew = true;
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
    $confirmationLink = requestEmailConfirm($userId, 'email');
    if ($confirmationLink !== false) {
      sendEmail($oldEmail, 'Email Change Confirmation', "Dear $nickname, 

You requested to change your email to $newEmail. Please follow this link to confirm: <a href=\"$confirmationLink\">$confirmationLink</a>

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

Your Boat Party Crew
");
    }
  }
}
// Email Change //
//////////////////

/////////////////////////////////
// Update Nickname and Fursuit //
try {
  $sql = "UPDATE users SET nickname = $nickname, fursuiter = $fursuiter, hash = $hash WHERE id = $userId";
  $stmt = $dbConnection->prepare('UPDATE users SET nickname = :nickname, fursuiter = :fursuiter, hash = :hash WHERE id = :userId');
  $stmt->bindParam(':nickname', $nickname);
  $stmt->bindParam(':fursuiter', $fursuiter);
  $stmt->bindParam(':hash', $hash);
  $stmt->bindParam(':userId', $userId);
  $stmt->execute();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}
// Update Nickname and Fursuit //
/////////////////////////////////


header('Location: ../details');