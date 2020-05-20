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
    $sql = "SELECT id, hash FROM users WHERE id = $userId AND status >= 0 AND locked = 0"; //TODO: Change >= back to > after testing
    $stmt = $dbConnection->prepare('SELECT id, hash FROM users WHERE id = :userId AND status >= 0 AND locked = 0');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
    die();
  }
  if ($stmt->rowCount() === 1 && $row['hash'] == $_SESSION['hash']) {
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

function newRegistration($firstName, $lastName, $nickname, $dob, $fursuiter, $sponsor, $email, $hash, $country, $rank, $regdate, $list, $efregid, $party) {
  global $dbConnection, $config;

  try {
    $sql = "INSERT INTO users(nickname, first_name, last_name, dob, country, email, hash, sponsor, fursuiter, status, `rank`, regdate, approvedate, list, efregid, party) ('$nickname', '$firstName', '$lastName', '$dob', '$country', '$email', '$hash', $sponsor, $fursuiter, 0, $rank, $regdate, NULL, $list, $efregid, $party)";
    $stmt = $dbConnection->prepare('INSERT INTO users(nickname, first_name, last_name, dob, country, email, hash, sponsor, fursuiter, status, `rank`, regdate, approvedate, list, efregid, party) VALUES(:nickname, :firstName, :lastName, :dob, :country, :email, :hash, :sponsor, :fursuiter, 0, :rank, :regdate, NULL, :list, :efregid, :party)');
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
    $stmt->bindParam(':efregid', $efregid);
    $stmt->bindParam(':party', $party);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
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
    return 'Password was <a target="_blank" href="https://haveibeenpwned.com/Passwords">leaked</a> before! Choose another one and change it on all sites!';
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
    $sql = "SELECT id FROM users WHERE id = $userId";
    $stmt = $dbConnection->prepare('SELECT id FROM users WHERE id = :userId');
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
    $stmt = $dbConnection->prepare('SELECT nickname, email FROM users WHERE list = 1 AND fursuiter = 1 ORDER BY nickname');
    $stmt->execute();
    $rows = $stmt->fetchAll();
  } catch (PDOException $e) {
    notifyOnException('Database Update', $config, $sql, $e);
  }
  return $rows;
}

function downgradeSponsor($userId) {
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
      $sql = "UPDATE users SET sponsor = 0, upgradedate = UNIX_TIMESTAMP() WHERE users.id = $userId";
      $stmt = $dbConnection->prepare('UPDATE users SET sponsor = 0, upgradedate = UNIX_TIMESTAMP() WHERE users.id = :userId');
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();
    } catch (PDOException $e) {
      notifyOnException('Database Update', $config, $sql, $e);
    }

    sendEmail($email, 'VIP Downgrade', "Dear $nickname,

Sorry to see you downgrade. You are no longer a VIP.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
");
  }
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
      $sql = "UPDATE users SET sponsor = 1, upgradedate = UNIX_TIMESTAMP() WHERE users.id = $userId";
      $stmt = $dbConnection->prepare('UPDATE users SET sponsor = 1, upgradedate = UNIX_TIMESTAMP() WHERE users.id = :userId');
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();
    } catch (PDOException $e) {
      notifyOnException('Database Update', $config, $sql, $e);
    }

    sendEmail($email, 'VIP Upgrade', "Dear $nickname,

Thank you for your VIP upgrade! You are now a VIP on Summerbo.at: All Paws on Deck. As a VIP, you get a few extras as a thank you for your support.
Please keep in mind that the 15€ need to be paid in cash during badge-pickup.

If you have any questions, please send us a message. Simply reply to this email or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
");
  }
}

function confirmRegistration($token) {
  global $dbConnection, $config;
  try {
    $dbConnection->beginTransaction();
    $sql = "SELECT email, nickname, users.id, sponsor FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = '$token'";
    $stmt = $dbConnection->prepare('SELECT email, nickname, users.id, sponsor FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = :token');
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($stmt->rowCount() === 1) {
      $email = $row['email'];
      $nickname = $row['nickname'];
      $userId = $row['id'];

      $sql = "UPDATE users SET status = 1 WHERE id = '$userId'";
      $stmt = $dbConnection->prepare('UPDATE users SET status = 1 WHERE id = :userId');
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();

      sendEmail($email, 'Email Confirmed', "Dear $nickname,

You have successfully verified your email.

Our registration team will now check your details. You will receive an email after your registration has been manually accepted. 
After being accepted you will be able to login to the user area. It can take a couple of hours before your registration is accepted. However, it shouldn't take more than 24 hours.

Your current status is: {$config['status'][1]} - Registration Number $userId

If you have any questions, please send us a message. Simply reply to this email or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
");
      sendStaffNotification($userId);

      $sql = "DELETE FROM email_tokens WHERE token = '$token'";
      $stmt = $dbConnection->prepare('DELETE FROM email_tokens WHERE token = :token');
      $stmt->bindParam(':token', $token);
      $stmt->execute();
    } else {
      $data = array(
        'ip' => $_SERVER["HTTP_CF_CONNECTING_IP"],
        'token' => $token,
        'server' => $_SERVER
      );
      mail($config['mail'], 'Potentially Malicious Reg-Confirm Attempt', print_r($data, true));
      return false;
    }
    $dbConnection->commit();
  } catch (PDOException $e) {
    notifyOnException('Database Transaction', $config, $sql, $e);
    return false;
  }
  return true;
}

function confirmEmail($token) {
  global $dbConnection, $config;

  try {
    $userId = getIdFromToken($token);
    /*$sql = "SELECT users.id FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = '$token'";
    $stmt = $dbConnection->prepare('SELECT users.id FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = :token');
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $row = $stmt->fetch();*/
    $dbConnection->beginTransaction();
    //if ($stmt->rowCount() === 1) {
    if ($userId !== false) {
      //$userId = $row['id'];
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

function confirmNewEmail($token) {
  global $dbConnection, $config;

  try {
    $dbConnection->beginTransaction();
    $sql = "SELECT users.id, email_new, nickname FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = '$token'";
    $stmt = $dbConnection->prepare('SELECT users.id, email_new, nickname FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = :token');
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($stmt->rowCount() === 1) {
      $userId = $row['id'];
      $newEmail = $row['email_new'];
      $nickname = $row['nickname'];

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
  if ($stmt->rowCount() === 1) {
    $confirmationLink = requestEmailConfirm($userId, 'email');

    if ($confirmationLink !== false) {
      sendEmail($newEmail, 'New Email Confirmation', "Dear $nickname,

You requested to change your email to $newEmail. Please follow this link to confirm:
<a href=\"$confirmationLink\">$confirmationLink</a>

If the change of email is the result of a transferred registration, please reset your password and check all details in your user area. If you can't change it yourself, please contact us.

If you have any questions, please send us a message. Simply reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
");
    } else {
      return false;
    }
  } else {
    return false;
  }
  return true;
}

function approveRegistration($userId, $approver) {
  global $dbConnection, $config;

  try {
    $sql = "UPDATE users SET status = 2, approvedate = UNIX_TIMESTAMP(), approver = $approver, upgradedate = UNIX_TIMESTAMP() WHERE id = '$userId' AND status < 2";
    $stmt = $dbConnection->prepare('UPDATE users SET status = 2, approvedate = UNIX_TIMESTAMP(), approver = :approver, upgradedate = UNIX_TIMESTAMP() WHERE id = :userId AND status < 2');
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

We regret to inform you that your registration has been canceled and deleted.

If you believe this was a mistake, please send us an email. We will inform you about the situation after checking the system.

If you have any questions, please send us a message. Simply reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
", true);
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
          'text' => 'Approve',
          'callback_data' => $userId . '|approve|0'
        ),
        array(
          'text' => 'Reject',
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
    $sql = "SELECT id FROM users WHERE status = 1 ORDER BY id";
    $stmt = $dbConnection->prepare("SELECT id FROM users WHERE status = 1 ORDER BY id");
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

function sendEmailB($address, $subject, $text, $internal = false) {
  global $dbConnection, $config;
  if ($internal === false) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    $text .= "
--
The following IP triggered this event: <a href=\"https://www.ip-tracker.org/locator/ip-lookup.php?ip=$ip\">$ip</a>.";
  }
  $body = nl2br($text);

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
    $mail->Body = $body;
    $mail->AddAddress($address);
    $mail->IsHTML(true);
    $mail->send();
  } catch (Exception $e) {
    mail('admin@kieran.de', 'Error Sending mail', $mail->ErrorInfo);
  }
  try {
    $sql = "INSERT INTO mail_log(receiver, subject, text) VALUES ($address, $subject, $body)";
    $stmt = $dbConnection->prepare('INSERT INTO mail_log(receiver, subject, text) VALUES (:receiver, :subject, :text)');
    $stmt->bindParam(':receiver', $address);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':text', $text);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
}

function sendEmail($address, $subject, $text, $internal = false, $log = true) {
  global $dbConnection, $config;
  if ($internal === false) {
    $ip = (isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR']);
    $ipNotice = "--
The following IP triggered this event: <a href=\"https://www.ip-tracker.org/locator/ip-lookup.php?ip=$ip\">$ip</a>.";
  } else {
    $ipNotice = '';
  }

  $email = array();
  $email['top'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html lang="en"> <head> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> <title>All Paws on Deck '.getPartyDate('Y').' - Summerbo.at</title> </head> <body bgcolor="#eee" style="-webkit-text-size-adjust: none;margin: 0;padding: 0;background: #eeee;width: 100% !important;"> <table border="0" cellpadding="0" cellspacing="0" id="backgroundTable" style="font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; margin: 0;padding: 0;height: 100% !important;width: 100% !important; background: #eeeeee;" width="100%"> <tbody> <tr> <td align="center" valign="top"> <table border="0" cellpadding="40" cellspacing="0" id="contentWrapper" width="480"> <tbody> <tr> <td> <table border="0" cellpadding="0" cellspacing="0" id="templateContainer" style="background-color: #FFFFFF;" width="480"> <tbody> <tr> <td> <table border="0" cellpadding="0" cellspacing="0" width="480"> <tbody> <tr> <td align="center" valign="top"> <table border="0" cellpadding="24" cellspacing="0" id="templateBody" style="border-bottom:1px solid #eee; padding:0 16px" width="480"> <tbody> <tr> <td style="background-color:#FFFFFF;" valign="top"> <a href="https://summerbo.at"><span class="sg-image"><img alt="Summerbo.at" height="64" src="https://summerbo.at/favicon-96x96.png?v=2020XBbnOXWxGx" style="width: 64px; height: 64px;" width="64"/></span></a> </td><td style="text-align: right;">&nbsp;</td></tr></tbody> </table> </td></tr><tr> <td align="center" valign="top"> <table border="0" cellpadding="24" cellspacing="0" id="templateBodyb" style="padding:0 16px 10px" width="480"> <tbody> <tr> <td style="background-color:#FFFFFF;" valign="top"> <span style="font-size:16px; color:#444; font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height:1.35;">';
  $email['bottom'] = '</span> <p><span style="font-size:16px; color:#444; font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:1.35;"> Kind regards,<br/> Your Boat Party Crew </span> </p><span style="font-size:16px;color:#444; font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height:1.35;"> ' . nl2br($ipNotice) . ' </span> </td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> <table border="0" cellpadding="5" cellspacing="0" id="templateFooterWrap" width="480"> <tbody> <tr> <td align="center" valign="top"> <table border="0" cellpadding="0" cellspacing="0" id="templateFooter" width="480"> <tbody> <tr> <td valign="top"> <table border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody> <tr> <td valign="middle" width="520"> <div style="color: #b3b3b3;font-family: Helvetica, Arial;font-size: 11px;line-height: 125%;text-align: center;">&nbsp;&nbsp;</div></td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> <br/> </td></tr></tbody> </table> </td></tr></tbody> </table> </body></html>';


  $body = $email['top'] . nl2br($text) . $email['bottom'];

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
    $mail->Body = $body;
    $mail->AddAddress($address);
    $mail->IsHTML(true);
    $mail->send();
  } catch (Exception $e) {
    mail('admin@kieran.de', 'Error Sending mail', $mail->ErrorInfo);
  }
  if ($log) {
    try {
      $sql = "INSERT INTO mail_log(receiver, subject, text) VALUES ($address, $subject, $body)";
      $stmt = $dbConnection->prepare('INSERT INTO mail_log(receiver, subject, text) VALUES (:receiver, :subject, :text)');
      $stmt->bindParam(':receiver', $address);
      $stmt->bindParam(':subject', $subject);
      $stmt->bindParam(':text', $text);
      $stmt->execute();
    } catch (PDOException $e) {
      notifyOnException('Database Select', $config, $sql, $e);
    }
  }
}

function getRandomString($length = 40) {
  $Chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  return substr(str_shuffle($Chars), 0, $length);
}

function sendMessage($chatId, $text, $replyMarkup = '') {
  $data = array(
    'disable_web_page_preview' => true,
    'parse_mode' => 'html',
    'chat_id' => $chatId,
    'text' => $text,
    'reply_markup' => $replyMarkup
  );
  return makeApiRequest('sendMessage', $data);
}

function deleteMessage($chatId, $messageId) {
  $data = array(
    'chat_id' => $chatId,
    'message_id' => $messageId
  );
  return makeApiRequest('deleteMessage', $data);
}

function returnResponse() {
  ignore_user_abort(true);
  ob_start();
  // do initial processing here
  header('Connection: close');
  header('Content-Length: ' . ob_get_length());
  header("Content-Encoding: none");
  header("Status: 200");
  ob_end_flush();
  ob_flush();
  flush();
}

function addUserToNewUsers($chatId, $userId) {
  $users = json_decode(file_get_contents('users.json'), true);
  if (empty($users[$chatId][$userId])) {
    $users[$chatId][$userId]['time'] = time();
    $users[$chatId][$userId]['posted'] = false;
    $users[$chatId][$userId]['clicked_button'] = false;

    file_put_contents('users.json', json_encode($users));
  }
}

function isNewUser($chatId, $userId) {
  $users = json_decode(file_get_contents('users.json'), true);
  if (!empty($users[$chatId][$userId]['time']) && $users[$chatId][$userId]['time'] + 3600 > time()) {
    return true;
  }
  return false;
}

function hasUserClickedButton($chatId, $userId) {
  $users = json_decode(file_get_contents('users.json'), true);
  if (!empty($users[$chatId][$userId]['clicked_button']) && $users[$chatId][$userId]['clicked_button'] == true) {
    return true;
  }
  return false;
}

function userClickedButton($chatId, $userId) {
  $users = json_decode(file_get_contents('users.json'), true);
  if ($users[$chatId][$userId]['clicked_button'] == false) {
    $users[$chatId][$userId]['clicked_button'] = true;
    file_put_contents('users.json', json_encode($users));

    return true;
  }
  return false;
}

function isNewUsersFirstMessage($chatId, $userId) {
  $users = json_decode(file_get_contents('users.json'), true);
  if ($users[$chatId][$userId]['posted'] == false) {
    $users[$chatId][$userId]['posted'] = true;
    file_put_contents('users.json', json_encode($users));

    return true;
  }
  return false;
}

function kickUser($chatId, $userId, $length = 40) {
  $until = time() + $length;
  $data = array(
    'chat_id' => $chatId,
    'user_id' => $userId,
    'until_date' => $until
  );
  return makeApiRequest('kickChatMember', $data);
}

function answerCallbackQuery($queryId, $text = '') {
  $data = array(
    'callback_query_id' => $queryId,
    'text' => $text
  );
  return makeApiRequest('answerCallbackQuery', $data);
}

function restrictChatMember($chatId, $userId, $until = 0, $sendMessages = false, $sendMedia = false, $sendOther = false, $sendWebPreview = false) {
  $untilTimestamp = time() + $until;
  $data = array(
    'chat_id' => $chatId,
    'user_id' => $userId,
    'until_date' => $untilTimestamp,
    'can_send_messages' => $sendMessages,
    'can_send_media_messages' => $sendMedia,
    'can_send_other_messages' => $sendOther,
    'can_add_web_page_previews' => $sendWebPreview
  );
  return makeApiRequest('restrictChatMember', $data);
}

function editMessageText($chatId, $messageId, $text, $replyMarkup = '', $inlineMessageId = '') {
  if (empty($inlineMessageId)) {
    $data = array(
      'chat_id' => $chatId,
      'message_id' => $messageId,
      'text' => $text,
      'parse_mode' => 'html',
      'disable_web_page_preview' => true,
      'reply_markup' => $replyMarkup
    );
  } else {
    $data = array(
      'inline_message_id' => $inlineMessageId,
      'text' => $text,
      'parse_mode' => 'html',
      'disable_web_page_preview' => true,
      'reply_markup' => $replyMarkup
    );
  }
  return makeApiRequest('editMessageText', $data);
}

function unrestrictUser($chatId, $userId, $welcomeMsgId, $welcomeMsgText) {
  removeMessageHistory($chatId, $userId);
  restrictChatMember($chatId, $userId, 0, true, true, true, true);
  editMessageText($chatId, $welcomeMsgId, $welcomeMsgText);
}

function addMessageToHistory($chatId, $userId, $messageId, $time = 0) {
  global $config;
  $dbConnection = buildDatabaseConnection($config);

  try {
    $sql = "SELECT id FROM telegram_messages WHERE chat_id = '$chatId' AND user_id = '$userId' AND message_id = '$messageId'";
    $stmt = $dbConnection->prepare("SELECT id FROM telegram_messages WHERE chat_id = :chatId AND user_id = :userId AND message_id = :messageId");
    $stmt->bindParam(':chatId', $chatId);
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':messageId', $messageId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  if (!$row) {
    try {
      $sql = "INSERT INTO telegram_messages(chat_id, user_id, message_id, time) VALUES ($chatId, $userId, $messageId, $time)";
      $stmt = $dbConnection->prepare("INSERT INTO telegram_messages(chat_id, user_id, message_id, time) VALUES (:chatId, :userId,:messageId, :time)");
      $stmt->bindParam(':chatId', $chatId);
      $stmt->bindParam(':userId', $userId);
      $stmt->bindParam(':messageId', $messageId);
      $stmt->bindParam(':time', $time);
      $stmt->execute();
    } catch (PDOException $e) {
      notifyOnException('Database Insert', $config, $sql, $e);
    }
  }
}

function deleteMessages($chatId, $userId) {
  global $config;
  $dbConnection = buildDatabaseConnection($config);

  try {
    $sql = "SELECT message_id FROM telegram_messages WHERE chat_id = '$chatId' AND user_id = '$userId'";
    $stmt = $dbConnection->prepare("SELECT message_id FROM telegram_messages WHERE chat_id = :chatId AND user_id = :userId");
    $stmt->bindParam(':chatId', $chatId);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $rows = $stmt->fetchAll();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }

  foreach ($rows as $row) {
    $messageId = $row['message_id'];
    deleteMessage($chatId, $messageId);
    try {
      $sql = "DELETE FROM telegram_messages WHERE chat_id = '$chatId' AND user_id = '$userId' AND message_id = '$messageId'";
      $stmt = $dbConnection->prepare("DELETE FROM telegram_messages WHERE chat_id = :chatId AND user_id = :userId AND message_id = :messageId");
      $stmt->bindParam(':chatId', $chatId);
      $stmt->bindParam(':userId', $userId);
      $stmt->bindParam(':messageId', $messageId);
      $stmt->execute();
    } catch (PDOException $e) {
      notifyOnException('Database Delete', $config, $sql, $e);
    }
  }
}

function removeMessageHistory($chatId, $userId) {
  global $config;

  $dbConnection = buildDatabaseConnection($config);

  try {
    $sql = "DELETE FROM telegram_messages WHERE chat_id = $chatId AND user_id = $userId";
    $stmt = $dbConnection->prepare('DELETE FROM telegram_messages WHERE chat_id = :chatId AND user_id = :userId');
    $stmt->bindParam(':chatId', $chatId);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
  }
}

function sendVenue($chatId, $latitude, $longitude, $title, $address) {
  $data = array(
    'chat_id' => $chatId,
    'latitude' => $latitude,
    'longitude' => $longitude,
    'title' => $title,
    'address' => $address
  );
  return makeApiRequest('sendVenue', $data);
}

function getIdFromToken($token) {
  global $dbConnection, $config;

  try {
    $sql = "SELECT users.id FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = '$token'";
    $stmt = $dbConnection->prepare('SELECT users.id FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = :token');
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

Registration number: $userId
You requested to change your password. Please follow this link to confirm the change:
<a href=\"$confirmationLink\">$confirmationLink</a>
Was it not you who requested this? Please let us know!

If you have any questions, please send us a message. Simply reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
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

function getConfirmedAttendees($choice) {
  global $dbConnection, $config;
  try {
    $sql = "SELECT count(id) as count FROM users WHERE status >= 0 AND `rank` = 0 AND locked = 0 AND party = $choice";
    $stmt = $dbConnection->prepare('SELECT count(id) as count FROM users WHERE status >= 0 AND `rank` = 0 AND locked = 0 AND party = :choice');
    $stmt->bindParam(':choice', $choice);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
    return false;
  }

  return $row['count'];
}

function checkInAttendee($userId, $regId) {
  global $dbConnection, $config;
  try {
    $sql = "UPDATE users SET checked_in = UNIX_TIMESTAMP(), checked_in_by = $userId WHERE id = $regId";
    $stmt = $dbConnection->prepare('UPDATE users SET checked_in = UNIX_TIMESTAMP(), checked_in_by = :userId WHERE id = :regId');
    $stmt->bindParam(':regId', $regId);
    $stmt->bindParam(':userId', $userId);
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

function searchForAttendee($userId, $search) {
  global $dbConnection, $config;
  try {
    $sql = "INSERT INTO search_log(`user_id`, `search`, `time`) VALUES($userId, $search, UNIX_TIMESTAMP())";
    $stmt = $dbConnection->prepare('INSERT INTO search_log(`user_id`, `search`, `time`) VALUES(:userId, :search, UNIX_TIMESTAMP())');
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':search', $search);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
  }
  $search = '%' . $search . '%';
  try {
    $sql = "SELECT nickname, CONCAT(first_name, ' ', last_name) as name, users.id, efregid, CASE sponsor WHEN 1 THEN 'checked' ELSE '' END as sponsor FROM users WHERE (nickname LIKE '$search' OR CONCAT(first_name, ' ', last_name) LIKE '$search' OR users.id LIKE '$search' OR efregid LIKE '$search') AND checked_in IS NULL";
    $stmt = $dbConnection->prepare("SELECT nickname, CONCAT(first_name, ' ', last_name) as name, users.id, efregid, CASE sponsor WHEN 1 THEN 'checked' ELSE '' END as sponsor 
            FROM users 
            WHERE (nickname LIKE :search1 OR CONCAT(first_name, ' ', last_name) LIKE :search2 OR users.id LIKE :search3 OR efregid LIKE :search4)
            AND checked_in IS NULL");
    $stmt->bindParam(':search1', $search);
    $stmt->bindParam(':search2', $search);
    $stmt->bindParam(':search3', $search);
    $stmt->bindParam(':search4', $search);
    $stmt->execute();
    $rows = $stmt->fetchAll();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  $searchResults = '';
  foreach ($rows as $row) {
    $searchResults .= '
            <tr>
              <td>' . $row['nickname'] . '</td>
              <td>' . $row['name'] . '</td>
              <td>' . $row['id'] . '</td>
              <td>' . $row['efregid'] . '</td>
              <td><input type="checkbox" name="sponsor" id="sponsor" class="input" ' . $row['sponsor'] . '></td>
              <td><form method="post"><div class="formRow"><button class="button buttonPrimary" name="regid" data-callback="onSubmit" value="' . $row['id'] . '">Check-In</button></div></form></td>
            </tr>';
  }
  return $searchResults;

}

function getAttendeesAdmin($userId, $filter) {
  global $dbConnection, $config;
  try {
    $sql = "INSERT INTO search_log(user_id, search, time) VALUES ($userId, CONCAT('list ', $filter), UNIX_TIMESTAMP())";
    $stmt = $dbConnection->prepare("INSERT INTO search_log(user_id, search, time) VALUES (:userId, CONCAT('list ', :filter), UNIX_TIMESTAMP())");
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':filter', $filter);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }

  $sql = "SELECT nickname, CONCAT(first_name, ' ', last_name) as name, users.id, efregid, CASE sponsor WHEN 1 THEN 'checked' ELSE '' END as sponsor, CASE checked_in WHEN NULL THEN '' ELSE checked_in END as checked_in FROM users";
  if ($filter === 'checkedin') {
    $sql = $sql . ' WHERE checked_in IS NOT NULL';
  } else if ($filter === 'absent') {
    $sql = $sql . ' WHERE checked_in IS NULL';
  } else if ($filter === 'vip') {
    $sql = $sql . ' WHERE sponsor = 1';
  }
  try {
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  $attendeeList = '';
  foreach ($rows as $row) {
    $attendeeList .= '<tr>
              <td>' . $row['nickname'] . '</td>
              <td>' . $row['name'] . '</td>
              <td>' . $row['id'] . '</td>
              <td>' . $row['efregid'] . '</td>
              <td><input type="checkbox" name="sponsor" id="sponsor" class="input" ' . $row['sponsor'] . '></td>
              <td>' . date('Y-m-d H:i', $row['checked_in']) . '</td>
            </tr>';
  }
  return [
    $attendeeList,
    $stmt->rowCount()
  ];
}

function getAdminStats() {
  global $dbConnection, $config;
  try {
    $sql = 'SELECT (SELECT COUNT(id) FROM users) as total, (SELECT COUNT(id) FROM users WHERE checked_in IS NOT NULL) as checked_in, (SELECT COUNT(id) FROM users WHERE checked_in IS NULL) as absent, (SELECT COUNT(id) FROM users WHERE sponsor = 1) as vip';
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  return [
    $row['total'],
    $row['checked_in'],
    $row['absent'],
    $row['vip']
  ];
}

/*
 * Reminders
 */

function makeApiRequest($method, $data) {
  global $config, $client;
  if (!($client instanceof \GuzzleHttp\Client)) {
    $client = new \GuzzleHttp\Client(['base_uri' => $config['url']]);
  }
  try {
    $response = $client->request('POST', $method, array('json' => $data));
  } catch (\GuzzleHttp\Exception\BadResponseException $e) {
    $body = $e->getResponse()->getBody();
    mail($config['mail'], 'Error', print_r($body->getContents(), true) . "\n" . print_r($data, true) . "\n" . __FILE__);
    return false;
  }
  return json_decode($response->getBody(), true)['result'];
}

function striposa($haystack, $needles = array(), $offset = 0) {
  $chr = array();
  foreach ($needles as $needle) {
    $res = stripos($haystack, $needle, $offset);
    if ($res !== false) {
      $chr[$needle] = $res;
    }
  }
  if (empty($chr)) {
    return false;
  }
  return min($chr);
}

function errorStatus($status) {
  session_start();
  $_SESSION['status'] = $status;
  session_commit();
  header('Location: ../register');
  die($status);
}

function getWaitinglistCount($party = null)
{
  global $dbConnection, $config;

  if ($party === null) {
    try {
      $sql = 'SELECT count(id) as count FROM waitinglist';
      $stmt = $dbConnection->prepare($sql);
      $stmt->execute();
      $row = $stmt->fetch();
    } catch (PDOException $e) {
      notifyOnException('Database Select', $config, $sql, $e);
      return false;
    }
  } else {
    try {
      $sql = 'SELECT count(id) as count FROM waitinglist WHERE party = :party';
      $stmt = $dbConnection->prepare($sql);
      $stmt->bindParam(':party', $party);
      $stmt->execute();
      $row = $stmt->fetch();
    } catch (PDOException $e) {
      notifyOnException('Database Select', $config, $sql, $e);
      return false;
    }
  }

  return $row['count'];
}

function addToWaitinglist($email, $party) {
  global $dbConnection, $config;

  try {
    $sql = "INSERT INTO waitinglist(email, party, created) VALUES ($email, $party, UNIX_TIMESTAMP())";
    $stmt = $dbConnection->prepare('INSERT INTO waitinglist(email, party, created) VALUES (:email, :party, UNIX_TIMESTAMP())');
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':party', $party);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
    return false;
  }
  return true;
}

function checkWaitinglist($email, $party) {
  global $dbConnection, $config;

  try {
    $sql = "SELECT id FROM waitinglist WHERE email = $email AND party = $party";
    $stmt = $dbConnection->prepare('SELECT id FROM waitinglist WHERE email = :email AND party = :party');
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':party', $party);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Insert', $config, $sql, $e);
    return true;
  }

  if ($stmt->rowCount() > 0) {
    return true;
  } else {
    return false;
  }
}

function getPartyDate($format) {
  global $config;

  return date($format, strtotime($config['start']));
}