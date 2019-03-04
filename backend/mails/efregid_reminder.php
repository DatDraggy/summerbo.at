<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('../config.php');
require_once('../funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT email, nickname, users.id, efregid, sponsor FROM users WHERE efregid = 9997';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $nickname = $row['nickname'];
  $efregid = $row['efregid'];
  $topay = $config['priceAttendee'];
  $viptext = 'No';
  if ($row['sponsor'] == 1) {
    $viptext = 'Yes';
  }

  sendEmail($row['email'], 'Eurofurence Registration Number on Summerbo.at', "Dear $nickname,

This is a reminder for you to please update your Eurofurence registartion number in your Summerbo.at account.
Payments will be processed by EF to make things easier for you. 
If you do not have an EF registration, please reply to this email and we will figure things out together.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
 
Your Boat Party Crew
", true);
}