<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}

require_once('../config.php');
require_once('../funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT users.id, email, nickname FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 1209600 < UNIX_TIMESTAMP() AND locked = false AND status < 3';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $userId = $row['id'];
  $email = $row['email'];
  $nickname = $row['nickname'];
  sendEmail($email, 'Summerbo.at No Payment Received', "Dear $nickname,

Sadly we haven't received the payment for your ticket. Therefore we had to lock your account and invalidate your reservation.

Keep in mind that you do NOT have a reservation anymore, meaning that if you pay now but it doesn't arrive in the next 3 days, you will lose your money and not get a registration.

In case you already paid and the payment arrives within 3 days, your account will be unlocked.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
", true);

  try {
    $sql = "UPDATE users SET locked = true WHERE id = $userId";
    $stmt = $dbConnection->prepare('UPDATE users SET locked = true WHERE id = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Update', $config, $sql, $e);
  }
  sleep(5);
}