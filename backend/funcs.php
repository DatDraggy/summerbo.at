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

function notifyOnException($subject, $config, $sql = '', $e = '', $fail = true) {
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
    $sql = "SELECT id FROM users WHERE id = $userId AND status > 0";
    $stmt = $dbConnection->prepare('SELECT id FROM users WHERE id = :userId AND status > 0');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
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

function getBalanceDetails($userId, $columns = '*'){
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

  try {
    $sql = 'SELECT count(id) as count FROM users WHERE status > 1 AND `rank` = 0 GROUP BY status';
    $stmt = $dbConnection->prepare('SELECT count(id) as count FROM users WHERE status > 1 AND `rank` = 0 GROUP BY status');
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    notifyOnException('Database Select', $config, $sql, $e);
  }
  if ($row['count'] < 100) {
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
    if($token === false){return false;}
  }else{return false;}
  if ($parameter !== false) {
    $token .= '&' .$parameter;
  }
  return 'https://' . $config['sitedomain'] . '/confirm?token=' . $token;
}

function upgradeToSponsor($userId) {
  global $dbConnection, $config;
  try {
    $sql = "SELECT email, nickname FROM users WHERE id = $userId";
    $stmt = $dbConnection->prepare('SELECT email FROM users WHERE id = :userId');
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
      $sql = "UPDATE balance INNER JOIN users on balance.id = users.id SET topay = topay + {$config['priceAddSponsor']}, sponsor = 1, status = 2 WHERE users.id = $userId";
      $stmt = $dbConnection->prepare('UPDATE balance SET topay = topay + :priceAddSponsor WHERE id = :userId');
      $stmt->bindParam(':priceAddSponsor', $config['priceAddSponsor']);
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();
    } catch (PDOException $e) {
      notifyOnException('Database Update', $config, $sql, $e);
    }

    sendEmail($email, 'Sponsor Upgrade', "Dear $nickname, 

Thank you for your upgrade! You are now a sponsor for the Summernight Party. As a sponsor, you get a special gift and badge as a thank you for the extra support. 

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

Your Boat Party Crew
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

      $topay = $config['priceAttendee'];
      if (isEarlyBird()) {
        $topay = $config['priceAttendeeEarly'];
      }
      if ($row['sponsor']) {
        $topay += $config['priceSponsor'];
      }

      $sql = "UPDATE users INNER JOIN email_tokens on users.id = email_tokens.id INNER JOIN balance on users.id = balance.id SET status = 1, topay = $topay  WHERE token = '$token'";
      $stmt = $dbConnection->prepare('UPDATE users INNER JOIN email_tokens on users.id = email_tokens.id INNER JOIN balance on users.id = balance.id SET status = 1, topay = :topay WHERE token = :token');
      $stmt->bindParam(':topay', $topay);
      $stmt->bindParam(':token', $token);
      $stmt->execute();

      sendEmail($email, 'Email Confirmed', "Dear $nickname, 

You have successfully verified your email. 

Our registration team will now check your details. You will get another email about this soon.
It can take a couple of hours before your registration is accepted. You should receive another mail from us about the next step after being accepted.
It shouldn't take more than 24 hours.

Your current status is: VERIFIED - Regnumber $userId

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

Your Boat Party Crew
");
      sendStaffNotification($userId);
      /*

Your registration will be reviewed by our registration team after you have verified yourself.
It can take a couple of hours before your registration is accepted. You should receive another mail from us about the next step after being accepted.
It shouldn't take more than 24 hours.*/
      $sql = "DELETE FROM email_tokens WHERE token = '$token'";
      $stmt = $dbConnection->prepare('DELETE FROM email_tokens WHERE token = :token');
      $stmt->bindParam(':token', $token);
      $stmt->execute();
    } else {
      $data = array('ip'      => $_SERVER["HTTP_CF_CONNECTING_IP"],
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
    $sql = "SELECT users.id FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = '$token'";
    $stmt = $dbConnection->prepare('SELECT users.id FROM users INNER JOIN email_tokens on users.id = email_tokens.id WHERE token = :token');
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

function approveRegistration($userId) {
  global $dbConnection, $config;

  try {
    $sql = "UPDATE users SET status = 2, approvedate = UNIX_TIMESTAMP() WHERE id = '$userId' AND status < 2";
    $stmt = $dbConnection->prepare('UPDATE users SET status = 2, approvedate = UNIX_TIMESTAMP() WHERE id = :userId AND status < 2');
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
    notifyOnException('Database Update', $config, $sql, $e);
    return false;
  }
  if ($stmt->rowCount() === 1) {
    sendEmail($email, 'Registration Canceled', "Dear $nickname,

Sadly we have to inform you that your registration has been deleted.

If you believe this was a mistake, please send us an email. It can be that there is still space. We will inform you with more information after checking the system. 

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew");
    return true;
  } else {
    return false;
  }
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
    notifyOnException('Database Insert', $config, $sql, $e, false);
    return false;
  }
  return true;
}

function sendStaffNotification($userId, $text = '') {
  global $config;

  foreach ($config['telegramAdmins'] as $admin) {
    if (empty($text)) {
      requestApproveMessage($admin, $userId);
    } else {
      sendMessage($admin, $text);
    }
  }
}

function buildApproveMarkup($userId) {
  return array('inline_keyboard' => array(/*array(
        array(
          'text' => 'View',
          'url'  => 'https://summerbo.at/admin/view.html?type=reg&id=' . $userId
          'callback_data' => $userId . '|view|0'
        )
      ),*/
                                          array(array('text'          => 'Approve',
                                                      'callback_data' => $userId . '|approve|0'
                                                ),
                                                array('text'          => 'Reject',
                                                      'callback_data' => $userId . '|reject|0'
                                                )
                                          )
  )
  );
}

function requestApproveMessage($chatId, $userId) {
  $replyMarkup = buildApproveMarkup($userId);
  sendMessage($chatId, "<b>New Registration on summerbo.at!</b>
<a href=\"https://summerbo.at/admin/view.html?type=reg&id=$userId\">Regnumber: $userId</a>", json_encode($replyMarkup));
}

function approvePayment($userId, $approver, $amount) {
  global $dbConnection, $config;

  try {
    $dbConnection->beginTransaction();
    $sql = "SELECT topay, paid, nickname, email FROM users INNER JOIN balance ON users.id = balance.id WHERE users.id = '$userId'";
    $stmt = $dbConnection->prepare("SELECT topay, paid, nickname, email FROM users INNER JOIN balance ON users.id = balance.id WHERE users.id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $row = $stmt->fetch();

    if ($stmt->rowCount() === 1) {
      $email = $row['email'];
      $nickname = $row['nickname'];
      $topay = $row['topay'];
      $paid = $row['paid'];

      if ($topay <= $amount + $paid) {
        $status = 3;
      } else {
        $status = 2;
      }

      $sql = "UPDATE balance INNER JOIN users on balance.id = users.id SET status = '$status', paid = paid + $amount WHERE users.id = '$userId'";
      $stmt = $dbConnection->prepare("UPDATE balance INNER JOIN users on balance.id = users.id SET status = :status, paid = paid + :amount WHERE users.id = :userId");
      $stmt->bindParam(':status', $status);
      $stmt->bindParam(':amount', $amount);
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();


      $sql = "INSERT INTO payments(user_id, date, approver_id, amount) VALUES ($userId, UNIX_TIMESTAMP(), $approver, $amount)";
      $stmt = $dbConnection->prepare("INSERT INTO payments(user_id, date, approver_id, amount) VALUES (:userId, UNIX_TIMESTAMP(), :approver, :amount)");
      $stmt->bindParam(':userId', $userId);
      $stmt->bindParam(':approver', $approver);
      $stmt->bindParam(':amount', $amount);
      $stmt->execute();
      $dbConnection->commit();
      mail($config['mail'], 'Debug', $status);
      if ($status == 3) {
        //ToDo Below more Information
        sendEmail($email, 'Payment Received', "Dear $nickname,

Welcome aboard! Your payment of $amount €,- has been received. Below you find more information about picking up your badge for the party. 

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

Your Boat Party Crew
", true);
        return true;
      } else {
        sendEmail($email, 'Payment Received', "Dear $nickname,

Your payment of $amount €,- has been received. However, for some reason, this did not cover the full required payment of $topay €,-.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

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
    $sql = "SELECT id, email, nickname FROM users WHERE id = $userId AND status > 0";
    $stmt = $dbConnection->prepare('SELECT id FROM users WHERE id = :userId AND status > 0');
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

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

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

    $sql = "INSERT INTO email_tokens(id, token) VALUES ()";
    $stmt = $dbConnection->prepare('SELECT id FROM users WHERE id = :userId AND status > 0');
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
  } catch (PDOException $e) {
    notifyOnException('Database Transaction', $config, $sql, $e);
    return false;
  }
  return $token;
}