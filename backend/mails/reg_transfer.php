<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('../config.php');
require_once('../funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT email, nickname, id, efregid, sponsor FROM users WHERE id = 263';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $email = $row['email'];
  $nickname = $row['nickname'];
  $efregid = $row['efregid'];
  $userid = $row['id'];

  sendEmail($email, 'Registration Transfer on Summerbo.at', "Dear $nickname,

Your registration transfer on Summerbo.at has been completed.
Please change your password on <a href=\"https://summerbo.at/login\">Summerbo.at</a> and review your details.

We're looking forward to seeing you on the boat.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
 
Your Boat Party Crew
", true);
}