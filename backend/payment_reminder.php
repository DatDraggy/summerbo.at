<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}

require_once('config.php');
require_once('funcs.php');

$dbConnection = buildDatabaseConnection($config);
try {
  $sql = "SELECT users.id, email, nickname, topay - paid as remaining FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 604800 < UNIX_TIMESTAMP() AND reminded = false AND status < 3";
  $stmt = $dbConnection->prepare('SELECT users.id, email, nickname, topay - paid as remaining FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 604800 < UNIX_TIMESTAMP() AND reminded = false AND status < 3');
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $userId = $row['id'];
  $email = $row['email'];
  $nickname = $row['nickname'];
  $remaining = $row['remaining'];
  sendEmail($email, 'Summerbo.at Payment Reminder', "<p>Dear $nickname,</p>

<p>Do you still want to join our party? Sadly we haven't received the payment for your ticket yet. Please make sure to pay within the coming 7 days to secure your spot on the deck. The total amount of €$remaining.- is still open. 
Below you will find our bank details to transfer the money. </p>

<p>Bank details:<br>
Name: Edwin Verstaij<br>
Bank: Bunq<br>
IBAN: NL04 BUNQ 2290 9065 14<br>
IC/SWIFT: BUNQNL2AXXX<br>
Comment: $userId, $nickname

<p>Did you already pay everything? Please ignore this email or send us a message.</p>

<p>If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.</p>

<p>Your Boat Party Crew</p>
", true);

  try {
    $sql = "UPDATE users SET reminded = true WHERE id = $userId";
    $stmt = $dbConnection->prepare('UPDATE users SET reminded = true WHERE id = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Update', $config, $sql, $e);
  }
  sleep(5);
}