<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('../config.php');
require_once('../funcs.php');

$dbConnection = buildDatabaseConnection($config);

if (empty($argv[1])) {
  die('Argument 1 Missing');
}
$regId = $argv[1];

try {
  $sql = "INSERT INTO users_deleted SELECT * FROM users WHERE id = $regId";
  $stmt = $dbConnection->prepare('INSERT INTO users_deleted SELECT * FROM users WHERE id = :regId');
  $stmt->bindParam(':regId', $regId);
  $stmt->execute();
} catch (PDOException $e) {
  notifyOnException('Database Insert', $config, $sql, $e);
}

if ($stmt->rowCount() === 1) {
  try {
    $sql = "DELETE FROM users WHERE id = $regId";
    $stmt = $dbConnection->prepare('DELETE FROM users WHERE id = :regId');
    $stmt->bindParam(':regId', $regId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
  }

  try {
    $sql = "SELECT efregid, nickname, email FROM users_deleted WHERE id = $regId";
    $stmt = $dbConnection->prepare('SELECT efregid, nickname, email FROM users_deleted WHERE id = :regId');
    $stmt->bindParam(':regId', $regId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
  }

  $nickname = $row['nickname'];
  $email = $row['email'];
  $efregid = $row['efregid'];

  try {
    $sql = "UPDATE users_deleted_reasons SET reason = 'Canceled via Script' WHERE id = $regId AND efregid = $efregid";
    $stmt = $dbConnection->prepare("UPDATE users_deleted_reasons SET reason = 'Canceled via Script' WHERE id = :regId AND efregid = :efregid");
    $stmt->bindParam(':regId', $regId);
    $stmt->bindParam(':efregid', $efregid);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
  }


  sendEmail($email, 'Registration Canceled', "Dear $nickname,

We regret to inform you that your registration has been canceled and deleted.

If you believe this was a mistake, please send us an email. We will inform you about the situation after checking the system. 

In case you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.", true);
} else {
  echo 'No rows found';
}