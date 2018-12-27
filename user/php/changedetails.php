<?php
require_once ('../../backend/config.php');
if(empty($_POST['userId']) || empty($_POST['nickname']) || empty($_POST['email']) || empty($_POST['passwordOld'])){
  die('Details can\'t be empty');
}

$userId = $_POST['userId'];
$nickname = $_POST['nickname'];
$newEmail = $_POST['email'];
$dbConnection = buildDatabaseConnection($config);

$hash = checkPassword($userId, $_POST['passwordOld']);

if($hash === false){
  die('Incorrect Password');
}

if(!empty($_POST['password'])){
  $passwordNew = $_POST['password'];
  if(empty($_POST['passwordVerify']) || $passwordNew !== $_POST['passwordVerify']){
    die('Passwords do not match');
  }
  $valid = validatePassword($passwordNew);
  if($valid !== true){
    die($valid);
  }
  $hash = hashPassword($passwordNew);
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

try{
  $sql = "SELECT email FROM users WHERE id = $userId";
  $stmt = $dbConnection->prepare('SELECT email FROM users WHERE id = :userId');
  $stmt->bindParam(':userId', $userId);
  $stmt->execute();
  $row = $stmt->fetch();
}catch (PDOException $e){
  notifyOnException('Database Select', $config, $sql, $e);
}
$oldEmail = $row['email'];
if($oldEmail !== $newEmail) {
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

//ToDo: Update all Info