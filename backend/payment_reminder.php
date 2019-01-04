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
  sendEmail($email, 'Summerbo.at Payment Reminder', "Dear $nickname,

Do you still want to join our party? Sadly we haven't received the payment for your ticket yet. Please make sure to pay within the coming 7 days to secure your spot on the deck. The total amount of €$remaining.- is still open. 
Below you will find our bank details to transfer the money. 

Bank details:
Name: Edwin Verstaij
Bank: Bunq
IBAN: NL
BIC:
Comment: $userId, $nickname

You already paid? Please ignore this email or send us a message.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

Your Boat Party Crew
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


try {
  $sql = "SELECT users.id, email, nickname FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 1209600 < UNIX_TIMESTAMP() AND locked = false AND status < 3";
  $stmt = $dbConnection->prepare('SELECT users.id, email, nickname FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 1209600 < UNIX_TIMESTAMP() AND locked = false AND status < 3');
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
From now on you have 7 days time to send your payment, after that your account will be deleted and you will have to register again.
Keep in mind that you do NOT have a reservation anymore. If you pay, but all slots are taken, your payment won't be accepted.

If you already paid, your account will be unlocked if there are free slots left once the payment is confirmed.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

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

die();

try {
  $sql = "SELECT users.id, email, nickname FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 1814400 < UNIX_TIMESTAMP() AND locked = false AND status < 3";
  $stmt = $dbConnection->prepare('SELECT users.id, email, nickname FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 1814400 < UNIX_TIMESTAMP() AND locked = false AND status < 3');
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $userId = $row['id'];
  rejectRegistration($userId);
  sleep(5);
}