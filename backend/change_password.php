<?php
require_once('config.php');
require_once('funcs.php');
header('Cache-Control: max-age=0');

if (empty($_POST['token'])) {
  $status = 'something went wrong. No token set in form. Contact dev@summerbo.at';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('../login');
  die($status);
} else {
  $token = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['token']);
}
$dbConnection = buildDatabaseConnection($config);
$userId = getIdFromToken($token);
if ($userId !== false) {
  $passwordNew = $_POST['password'];
  if (empty($_POST['passwordVerify']) || $passwordNew !== $_POST['passwordVerify']) {
    $status = 'Passwords do not match';
    session_start();
    $_SESSION['status'] = $status;
    session_commit();
    header('../login');
    die($status);
  }
  $valid = validatePassword($passwordNew);
  if ($valid !== true) {
    $status = $valid;
    session_start();
    $_SESSION['status'] = $status;
    session_commit();
    header('../login');
    die($status);
  }
  $hash = hashPassword($passwordNew);

  try {
    $sql = "UPDATE users SET hash = $hash WHERE id = $userId";
    $stmt = $dbConnection->prepare('UPDATE users SET hash = :hash WHERE id = :userId');
    $stmt->bindParam(':hash', $hash);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Update', $config, $sql, $e);
  }
  session_start();
  if ($stmt->rowCount() === 1) {
    $_SESSION['status'] = 'Password Changed';
    session_commit();
    header('Location: ../login');
    die();
  } else {
    $_SESSION['status'] = 'Something went wrong';
    session_commit();
    header('Location: ../login');
    die();
  }
} else {
  $status = 'Invalid token provided.';
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../login');
  die();
}