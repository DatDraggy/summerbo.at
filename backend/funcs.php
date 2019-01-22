<?php
require_once('/var/libraries/PHPMailer/PHPMailer.php');
require_once('/var/libraries/PHPMailer/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function buildDatabaseConnection($config) {
  //Connect to DB only here to save response time on other commands
  try {
    $dbConnection = new PDO('mysql:dbname=' . $config['dbname'] . ';host=' . $config['dbserver'] . ';port=' . $config['dbport'] . ';charset=utf8mb4', $config['dbuser'], $config['dbpassword'], array(PDO::ATTR_TIMEOUT => 25));
    $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    notifyOnException('Database Connection', $config, '', $e);
    return false;
  }
  return $dbConnection;
}

function notifyOnException($subject, $config, $sql = '', $e = '', $fail = false) {
  $to = $config['mail'];
  $txt = __FILE__ . ' ' . $sql . ' Error: ' . $e;
  $headers = 'From: ' . $config['mail'];
  mail($to, $subject, $txt, $headers);
  http_response_code(200);
  if ($fail) {
    die();
  }
}

function checkRegValid($userId) {
  global $dbConnection, $config;
  try {
    $sql = "SELECT id FROM users WHERE id = $userId AND status > 0 AND locked = 0";
    $stmt = $dbConnection->prepare('SELECT id FROM users WHERE id = :userId AND status > 0 AND locked = 0');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
    die();
  }
  if ($stmt->rowCount() === 1) {
    //Already logged in
    return true;
  } else {
    //Clear Session and Cookies
    return false;
  }
}

function getRegDetails($userId, $columns = '*') {
  global $dbConnection, $config;

  try {
    $sql = "SELECT $columns FROM users WHERE id = '$userId'";
    $stmt = $dbConnection->prepare("SELECT $columns FROM users WHERE id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  if (!empty($row)) {
    return $row;
  } else {
    return 'Not found';
  }
}

function getPaymentDetails($userId, $columns = '*') {
  global $dbConnection, $config;

  try {
    $sql = "SELECT $columns FROM payments INNER JOIN users ON users.id = payments.user_id INNER JOIN balance ON payments.user_id = balance.id WHERE users.id = '$userId'";
    $stmt = $dbConnection->prepare("SELECT $columns FROM payments INNER JOIN users ON users.id = payments.user_id INNER JOIN balance ON payments.user_id = balance.id WHERE users.id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetchAll();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  if ($stmt->rowCount() > 0) {
    return $row;
  } else {
    return false;
  }
}

function getBalanceDetails($userId, $columns = '*') {
  global $dbConnection, $config;

  try {
    $sql = "SELECT $columns FROM balance WHERE id = '$userId'";
    $stmt = $dbConnection->prepare("SELECT $columns FROM balance WHERE id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  if ($stmt->rowCount() === 1) {
    return $row;
  } else {
    return false;
  }
}

function getUserRank($userId) {
  global $dbConnection, $config;

  try {
    $sql = "SELECT `rank` FROM users WHERE id = '$userId'";
    $stmt = $dbConnection->prepare("SELECT `rank` FROM users WHERE id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  if (!empty($row)) {
    return $row['rank'];
  } else {
    return 0;
  }
}

function hashPassword($password) {
  return password_hash($password, PASSWORD_DEFAULT);
}

function isEarlyBird() {
  global $dbConnection, $config;

  $count = getConfirmedAttendees();
  if ($count < 100) {
    return true;
  } else {
    return false;
  }
}

function newRegistration($firstName, $lastName, $nickname, $dob, $fursuiter, $sponsor, $email, $hash, $country, $rank, $regdate, $list) {
  global $dbConnection, $config;
  //ToDo: INSERT INTO users
  //ToDo: UPDATE Balance SET topay

  try {
    $sql = "INSERT INTO users(nickname, first_name, last_name, dob, country, email, hash, sponsor, fursuiter, status, `rank`, regdate, approvedate, list) ('$nickname', '$firstName', '$lastName', '$dob', '$country', '$email', '$hash', $sponsor, $fursuiter, 0, $rank, $regdate, NULL, $list)";
    $stmt = $dbConnection->prepare('INSERT INTO users(nickname, first_name, last_name, dob, country, email, hash, sponsor, fursuiter, status, `rank`, regdate, approvedate, list) VALUES(:nickname, :firstName, :lastName, :dob, :country, :email, :hash, :sponsor, :fursuiter, 0, :rank, :regdate, NULL, :list)');
    $stmt->bindParam(':nickname', $nickname);
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':dob', $dob);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':hash', $hash);
    $stmt->bindParam(':sponsor', $sponsor);
    $stmt->bindParam(':fursuiter', $fursuiter);
    $stmt->bindParam(':rank', $rank);
    $stmt->bindParam(':regdate', $regdate);
    $stmt->bindParam(':list', $list);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
    return false;
  }
  return $dbConnection->lastInsertId();
}

function validatePassword($password) {
  if (strlen($password) < 8) {
    return 'Too short, min 8';
  }
  $hash = strtoupper(sha1($password));
  $hashShort = substr($hash, 0, 5);
  $list = file_get_contents('https://api.pwnedpasswords.com/range/' . $hashShort);
  $pwned = false;
  if (strpos($list, substr($hash, -35))) {
    $pwned = true;
  }
  if ($pwned) {
    return 'Password was leaked before, Choose another one!';
  } else {
    return true;
  }
}

//dbConnection needs to be set
function requestEmailConfirm($userId, $parameter = false) {
  global $dbConnection, $config;
  if (!isset($dbConnection)) {
    mail($config['mail'], 'CODE BUG, NOT SET VAR', 'SET VAR BEFORE USAGE OF requestRegistrationConfirm');
    die();
  }
  try {
    $sql = "SELECT id_internal FROM users WHERE id_internal = $userId";
    $stmt = $dbConnection->prepare('SELECT id_internal FROM users WHERE id_internal = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
    return false;
  }
  if ($stmt->rowCount() > 0) {
    $token = insertToken($userId);
    if ($token === false) {
      return false;
    }
  } else {
    return false;
  }
  if ($parameter !== false) {
    $token .= '&' . $parameter;
  }
  return 'https://' . $config['sitedomain'] . '/confirm?token=' . $token;
}

function getFursuiters() {
  global $dbConnection, $config;

  try {
    $sql = 'SELECT nickname, email FROM users WHERE list = 1 AND fursuiter = 1';
    $stmt = $dbConnection->prepare('SELECT nickname, email FROM users WHERE list = 1 AND fursuiter = 1 ORDER BY id ASC');
    $stmt->execute();
    $rows = $stmt->fetchAll();
  } catch (PDOException $e) {
    notifyOnException('Database Update', $config, $sql, $e);
  }
  return $rows;
}

function upgradeToSponsor($userId) {
  global $dbConnection, $config;
  try {
    $sql = "SELECT email, nickname FROM users WHERE id = $userId";
    $stmt = $dbConnection->prepare('SELECT email, nickname FROM users WHERE id = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Update', $config, $sql, $e);
  }
  if ($stmt->rowCount() === 1) {
    $email = $row['email'];
    $nickname = $row['nickname'];
    try {
      $sql = "UPDATE balance INNER JOIN users on balance.id = users.id SET topay = topay + {$config['priceSponsor']}, sponsor = 1, status = 2 WHERE users.id = $userId";
      $stmt = $dbConnection->prepare('UPDATE balance INNER JOIN users on balance.id = users.id SET topay = topay + :priceSponsor, sponsor = 1, status = 2 WHERE users.id = :userId');
      $stmt->bindParam(':priceSponsor', $config['priceSponsor']);
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();
    } catch (PDOException $e) {
      notifyOnException('Database Update', $config, $sql, $e);
    }

    sendEmail($email, '`VIP Upgrade', "Dear $nickname, 

Thank you for your upgrade! You are now a VIP for Hot Summer Nights 2019. As a VIP, you get a special gift and badge as a thank you for the extra support. 

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
");
  }
}

function confirmRegistration($token) {
  global $dbConnection, $config;
  try {
    $dbConnection->beginTransaction();
    $sql = "SELECT email, nickname, users.id_internal, sponsor FROM users INNER JOIN email_tokens on users.id_internal = email_tokens.id WHERE token = '$token'";
    $stmt = $dbConnection->prepare('SELECT email, nickname, users.id_internal, sponsor FROM users INNER JOIN email_tokens on users.id_internal = email_tokens.id WHERE token = :token');
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($stmt->rowCount() === 1) {
      $email = $row['email'];
      $nickname = $row['nickname'];
      $userIdInternal = $row['id_internal'];

      $topay = $config['priceAttendee'];
      if (isEarlyBird()) {
        $topay = $config['priceAttendeeEarly'];
      }
      if ($row['sponsor']) {
        $topay += $config['priceSponsor'];
      }

      $sql = "UPDATE users SET status = 1, id = ((SELECT selected_value FROM (SELECT MAX(id) AS selected_value FROM users) AS sub_selected_value) + 1) WHERE id_internal = '$userIdInternal'";
      $stmt = $dbConnection->prepare('UPDATE users SET status = 1, id = ((SELECT selected_value FROM (SELECT MAX(id) AS selected_value FROM users) AS sub_selected_value) + 1) WHERE id_internal = :userIdInternal');
      $stmt->bindParam(':userIdInternal', $userIdInternal);
      $stmt->execute();

      $sql = "SELECT id FROM users WHERE id_internal = $userIdInternal";
      $stmt = $dbConnection->prepare('SELECT id FROM users WHERE id_internal = :userIdInternal');
      $stmt->bindParam(':userIdInternal', $userIdInternal);
      $stmt->execute();
      $row = $stmt->fetch();
      $userId = $row['id'];

      $sql = "INSERT INTO balance(id, topay) VALUES ($userId, $topay)";
      $stmt = $dbConnection->prepare('INSERT INTO balance(id, topay) VALUES (:userId, :topay)');
      $stmt->bindParam(':userId', $userId);
      $stmt->bindParam(':topay', $topay);
      $stmt->execute();

      sendEmail($email, 'Email Confirmed', "Dear $nickname, 

You have successfully verified your email. 

Our registration team will now check your details. You will get another email about this soon.
It can take a couple of hours before your registration is accepted. You should receive another mail from us about the next step after being accepted.
It shouldn't take more than 24 hours.

Your current status is: {$status[1]} - Regnumber $userId

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
");
      sendStaffNotification($userId);

      $sql = "DELETE FROM email_tokens WHERE token = '$token'";
      $stmt = $dbConnection->prepare('DELETE FROM email_tokens WHERE token = :token');
      $stmt->bindParam(':token', $token);
      $stmt->execute();
    } else {
      $data = array(
        'ip'      => $_SERVER["HTTP_CF_CONNECTING_IP"],
        'token'   => $token,
        'server'  => $_SERVER,
        'headers' => $http_response_header
      );
      mail($config['mail'], 'Potentially Malicious Reg-Confirm Attempt', print_r($data, true));
      return false;
    }
    $dbConnection->commit();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
    return false;
  }
  return true;
}

function confirmEmail($token) {
  global $dbConnection, $config;

  try {
    $dbConnection->beginTransaction();
    $sql = "SELECT users.id FROM users INNER JOIN email_tokens on users.id_internal = email_tokens.id WHERE token = '$token'";
    $stmt = $dbConnection->prepare('SELECT users.id FROM users INNER JOIN email_tokens on users.id_internal = email_tokens.id WHERE token = :token');
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($stmt->rowCount() === 1) {
      $userId = $row['id'];
      $sql = "UPDATE users SET email = email_new, email_new = NULL WHERE id = $userId";
      $stmt = $dbConnection->prepare('UPDATE users SET email = email_new, email_new = NULL WHERE id = :userId');
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();

      $sql = "DELETE FROM email_tokens WHERE token = '$token'";
      $stmt = $dbConnection->prepare('DELETE FROM email_tokens WHERE token = :token');
      $stmt->bindParam(':token', $token);
      $stmt->execute();
    }
    $dbConnection->commit();
  } catch (PDOException $e) {
    notifyOnException('Database Transaction', $config, $sql, $e);
    return false;
  }
  return true;
}

function approveRegistration($userId, $approver) {
  global $dbConnection, $config;

  try {
    $sql = "UPDATE users SET status = 2, approvedate = UNIX_TIMESTAMP(), approver = $approver WHERE id = '$userId' AND status < 2";
    $stmt = $dbConnection->prepare('UPDATE users SET status = 2, approvedate = UNIX_TIMESTAMP(), approver = :approver WHERE id = :userId AND status < 2');
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':approver', $approver);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Update', $config, $sql, $e);
    return false;
  }
  if ($stmt->rowCount() === 1) {
    return true;
  } else {
    return false;
  }
}

function rejectRegistration($userId) {
  global $dbConnection, $config;

  list($email, $nickname) = getRegDetails($userId, 'email, nickname');

  try {
    $sql = "INSERT INTO users_deleted SELECT * FROM users WHERE id = '$userId'";
    $stmt = $dbConnection->prepare('INSERT INTO users_deleted SELECT * FROM users WHERE id = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
    return false;
  }
  try {
    $sql = "DELETE FROM users WHERE id = '$userId'";
    $stmt = $dbConnection->prepare('DELETE FROM users WHERE id = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Delete', $config, $sql, $e);
    return false;
  }
  if ($stmt->rowCount() === 1) {
    sendEmail($email, 'Registration Canceled', "Dear $nickname,

Sadly we have to inform you that your registration has been deleted.

If you believe this was a mistake, please send us an email. It can be that there is still space. We will inform you with more information after checking the system. 

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew", true);
    return true;
  } else {
    return false;
  }
}

function isTelegramAdmin($userId) {
  global $config;
  if (in_array($userId, $config['telegramAdmins'])) {
    return true;
  }
  return false;
}

function saveApplication($chatId, $name, $message) {
  global $dbConnection, $config;

  try {
    $sql = "INSERT INTO applications(chatId, name, message) VALUES ('$chatId', '$name', '$message')";
    $stmt = $dbConnection->prepare('INSERT INTO applications(chatId, name, message) VALUES (:chatId, :name, :message)');
    $stmt->bindParam(':chatId', $chatId);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':message', $message);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
    return false;
  }
  return true;
}

function sendStaffNotification($userId, $text = '', $replyMarkup = '') {
  global $config;

  foreach ($config['telegramAdmins'] as $admin) {
    if (empty($text)) {
      requestApproveMessage($admin, $userId);
    } else {
      if (empty($replyMarkup)) {
        sendMessage($admin, $text);
      } else {
        sendMessage($admin, $text, json_encode($replyMarkup));
      }
    }
  }
}

function buildApproveMarkup($userId) {
  return array(
    'inline_keyboard' => array(
      array(
        array(
          'text'          => 'Approve',
          'callback_data' => $userId . '|approve|0'
        ),
        array(
          'text'          => 'Reject',
          'callback_data' => $userId . '|reject|0'
        )
      )
    )
  );
}

function requestApproveMessage($chatId, $userId) {
  $replyMarkup = buildApproveMarkup($userId);
  sendMessage($chatId, "<b>New Registration on summerbo.at!</b>
<a href=\"https://summerbo.at/admin/view?type=reg&id=$userId\">Regnumber: $userId</a>", json_encode($replyMarkup));
}

function openSlots() {
  global $dbConnection, $config;
  $confirmedAttendees = getConfirmedAttendees();
  if ($confirmedAttendees === false || $confirmedAttendees >= $config['attendeesMax']) {
    return false;
  }
  return true;
}

function approvePayment($userId, $approver, $amount) {
  global $dbConnection, $config;

  try {
    $dbConnection->beginTransaction();
    $sql = "SELECT topay, paid, nickname, email, locked FROM users INNER JOIN balance ON users.id = balance.id WHERE users.id = '$userId'";
    $stmt = $dbConnection->prepare("SELECT topay, paid, nickname, email, locked FROM users INNER JOIN balance ON users.id = balance.id WHERE users.id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();

    if ($stmt->rowCount() === 1) {
      $email = $row['email'];
      $nickname = $row['nickname'];
      $topay = $row['topay'];
      $paid = $row['paid'];
      $locked = $row['locked'];

      if ($topay <= $amount + $paid) {
        $status = 3;
      } else {
        $status = 2;
      }

      $sql = "UPDATE balance INNER JOIN users on balance.id = users.id SET status = '$status', paid = paid + $amount WHERE users.id = '$userId'";
      $stmt = $dbConnection->prepare('UPDATE balance INNER JOIN users on balance.id = users.id SET status = :status, paid = paid + :amount WHERE users.id = :userId');
      $stmt->bindParam(':status', $status);
      $stmt->bindParam(':amount', $amount);
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();
      $openSlots = openSlots();
      if ($status == 3 && $locked != 0 && $openSlots) {
        $sql = "UPDATE users SET locked = 0 WHERE id = $userId";
        $stmt = $dbConnection->prepare('UPDATE users SET locked = 0 WHERE id = :userId');
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
      }
      if ($locked != 0 && !$openSlots) {
        $status = 1;
      }

      $sql = "INSERT INTO payments(user_id, date, approver_id, amount) VALUES ($userId, UNIX_TIMESTAMP(), $approver, $amount)";
      $stmt = $dbConnection->prepare('INSERT INTO payments(user_id, date, approver_id, amount) VALUES (:userId, UNIX_TIMESTAMP(), :approver, :amount)');
      $stmt->bindParam(':userId', $userId);
      $stmt->bindParam(':approver', $approver);
      $stmt->bindParam(':amount', $amount);
      $stmt->execute();
      $dbConnection->commit();

      if ($status == 3) {
        //ToDo Below more Information
        sendEmail($email, 'Payment Received', "Dear $nickname,

Welcome aboard! Your payment of $amount €,- has been received. Below you find more information about picking up your badge for the party. We will also send more information in the weeks before the party.

<a href=\"https://summerbo.at/#faq\">https://summerbo.at/#faq</a>

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
", true);
        return true;
      } else if ($status == 2) {
        sendEmail($email, 'Payment Received', "Dear $nickname,

Your payment of $amount €,- has been received. However, for some reason, this did not cover the full required payment of $topay €,-. 
Please pay the rest of the amount as well to make sure you are fully registered. If you think this is a mistake, let us know via email.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
", true);
        return false;
      } else {
        sendEmail($email, 'Payment Received', "Dear $nickname,

Your payment of $amount €,- has been received. However, there were no more open slots for you.
We're sorry and apologize for any inconvenience.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
", true);
        return false;
      }
    }
  } catch (PDOException $e) {
    notifyOnException('Database Transaction', $config, $sql, $e);
    return false;
  }
  return false;
}

function checkPassword($userId, $password) {
  global $dbConnection, $config;

  try {
    $sql = "SELECT hash FROM users WHERE id = $userId";
    $stmt = $dbConnection->prepare('SELECT hash FROM users WHERE id = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }

  if (password_verify($password, $row['hash'])) {
    return $row['hash'];
  }
  return false;
}

function requestUnapproved($chatId) {
  global $dbConnection, $config;
  try {
    $sql = "SELECT id FROM users WHERE status = 1";
    $stmt = $dbConnection->prepare("SELECT id FROM users WHERE status = 1");
    $stmt->execute();
    $rows = $stmt->fetchAll();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  foreach ($rows as $row) {
    requestApproveMessage($chatId, $row['id']);
    sleep(1);
  }
  sendMessage($chatId, 'Done.');
}

function sendEmail($address, $subject, $text, $internal = false) {
  global $config;
  if ($internal === false) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    $text .= "
--
The following IP triggered this event: <a href=\"https://www.ip-tracker.org/locator/ip-lookup.php?ip=$ip\">$ip</a>.";
  }

  $mail = new PHPMailer(true); // create a new object
  try {
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = false; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED
    $mail->Host = 'mail.summerbo.at';
    $mail->Port = 465; // or 587
    $mail->Username = 'team';
    $mail->Password = $config['mailPassword'];
    $mail->SetFrom('team@summerbo.at', 'Summerbo.at Team');
    $mail->Subject = $subject;
    $mail->Body = nl2br($text);
    $mail->AddAddress($address);
    $mail->IsHTML(true);
    $mail->send();
  } catch (Exception $e) {
    mail('admin@kieran.de', 'Error Sending mail', $mail->ErrorInfo);
  }
}

function getRandomString($length = 40) {
  $Chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  return substr(str_shuffle($Chars), 0, $length);
}

function sendMessage($chatId, $text, $replyMarkup = '') {
  global $config;
  $response = file_get_contents($config['url'] . "sendMessage?disable_web_page_preview=true&parse_mode=html&chat_id=$chatId&text=" . urlencode($text) . "&reply_markup=$replyMarkup");
  //Might use http_build_query in the future
  return json_decode($response, true)['result'];
}

function answerCallbackQuery($queryId, $text = '') {
  global $config;
  $response = file_get_contents($config['url'] . "answerCallbackQuery?callback_query_id=$queryId&text=" . urlencode($text));
  //Might use http_build_query in the future
  return json_decode($response, true)['result'];
}

function sendVenue($chatId, $latitude, $longitude, $title, $address) {
  global $config;
  $response = file_get_contents($config['url'] . "sendVenue?chat_id=$chatId&latitude=$latitude&longitude=$longitude&title=" . urlencode($title) . "&address=" . urlencode($address));
  //Might use http_build_query in the future
  return json_decode($response, true)['result'];
}

function getIdFromToken($token) {
  global $dbConnection, $config;

  try {
    $sql = "SELECT users.id FROM users INNER JOIN email_tokens on users.id_internal = email_tokens.id WHERE token = '$token'";
    $stmt = $dbConnection->prepare('SELECT users.id FROM users INNER JOIN email_tokens on users.id_internal = email_tokens.id WHERE token = :token');
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
    return false;
  }
  if ($stmt->rowCount() === 1) {
    return $row['id'];
  }
  return false;
}

function requestPasswordReset($userId) {
  global $dbConnection, $config;

  try {
    $sql = "SELECT email, nickname FROM users WHERE id = $userId AND status > 0";
    $stmt = $dbConnection->prepare('SELECT email, nickname FROM users WHERE id = :userId AND status > 0');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
    return false;
  }
  if ($stmt->rowCount() === 1) {
    $nickname = $row['nickname'];
    $email = $row['email'];
    $confirmationLink = requestEmailConfirm($userId, 'password');
    sendEmail($email, 'Password Reset', "Dear $nickname, 

You requested to change your password. Please follow this link to confirm: <a href=\"$confirmationLink\">$confirmationLink</a>
Was it not you who requested this? Let us know!

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
");
    return true;
  }
  return false;
}

function insertToken($userId) {
  global $dbConnection, $config;

  try {
    $dbConnection->beginTransaction();
    $sql = "DELETE FROM email_tokens WHERE id = $userId";
    $stmt = $dbConnection->prepare('DELETE FROM email_tokens WHERE id = :userId');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();

    $token = getRandomString();

    $sql = "INSERT INTO email_tokens(id, token) VALUES ($userId, $token)";
    $stmt = $dbConnection->prepare('INSERT INTO email_tokens(id, token) VALUES (:userId, :token)');
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $dbConnection->commit();
  } catch (PDOException $e) {
    notifyOnException('Database Transaction', $config, $sql, $e);
    return false;
  }
  return $token;
}

function getConfirmedAttendees() {
  global $dbConnection, $config;
  try {
    $sql = 'SELECT count(id) as count FROM users WHERE status > 1 AND `rank` = 0 AND locked = 0';
    $stmt = $dbConnection->prepare('SELECT count(id) as count FROM users WHERE status > 1 AND `rank` = 0 AND locked = 0');
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
    return false;
  }

  return $row['count'];
}

/*
 * Reminders
 */

function remindRemindReg() {
  global $dbConnection, $config;

  try {
    $sql = 'SELECT users.id, email, nickname, topay - paid as remaining FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 604800 < UNIX_TIMESTAMP() AND reminded = false AND status < 3';
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
    $remaining = $row['remaining'];
    sendEmail($email, 'Summerbo.at Payment Reminder', "Dear $nickname,

Do you still want to join our party? Sadly we haven't received the payment for your ticket yet. Please make sure to pay within the coming 7 days to secure your spot on the deck. The total amount of $remaining €.- is still open. 
Below you will find our bank details to transfer the money.

Bank Details:
Name: Edwin Verstaij
IBAN: DE68 7001 1110 6054 4164 13
BIC/SWIFT: DEKTDE7GXXX
Comment: $userId + $nickname

Did you already pay everything? Please ignore this email or send us a message.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

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
}

function remindLockReg() {
  global $config, $dbConnection;

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
}

function remindDeleteReg() {
  global $config, $dbConnection;

  try {
    $sql = "SELECT users.id, email, nickname FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 1468800 < UNIX_TIMESTAMP() AND locked = true AND status < 3";
    $stmt = $dbConnection->prepare($sql);
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
}
