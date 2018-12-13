<?php
function buildDatabaseConnection($config) {
  //Connect to DB only here to save response time on other commands
  try {
    $dbConnection = new PDO('mysql:dbname=' . $config['dbname'] . ';host=' . $config['dbserver'] . ';port=' . $config['dbport'] . ';charset=utf8mb4', $config['dbuser'], $config['dbpassword'], array(PDO::ATTR_TIMEOUT => 25));
    $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    notifyOnException('Database Connection', $config, '', $e);
  }
  return $dbConnection;
}

function notifyOnException($subject, $config, $sql = '', $e = '') {
  $to = $config['mail'];
  $txt = __FILE__ . ' ' . $sql . ' Error: ' . $e;
  $headers = 'From: ' . $config['mail'];
  mail($to, $subject, $txt, $headers);
  http_response_code(200);
  die();
}

function getUserRank($userId) {
  global $config;
  $dbConnection = buildDatabaseConnection($config);

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
  }
  else {
    return 0;
  }
}

//dbConnection needs to be set
function requestRegistrationConfirm($userId, $email) {
  global $dbConnection, $config;
  if (!isset($dbConnection)) {
    mail($config['mail'], 'CODE BUG, NOT SET VAR', 'SET VAR BEFORE USAGE OF requestRegistrationConfirm');
    die();
  }
  $token = getRandomString(40);

  $sql = 'INSERT INTO email_tokens(id, token) VALUES ($userId, $token)';
  $stmt = $dbConnection->prepare('INSERT INTO email_tokens(id, token) VALUES (:userId, :token)');
  $stmt->bindParam(':userId', $userId);
  $stmt->bindParam(':token', $token);
  $stmt->execute();

  $confirmationLink = 'https://' . $config['sitedomain'] . '/confirm?token=' . $token;

  sendEmail($email, 'Subject', "Email Text $confirmationLink");
}

function confirmRegistration($userId) {
  //ToDo: Set Status to 1
  //ToDo: Send Confirmation to Attendee
  //ToDo: Send Notification to Staff
}

function sendEmail($address, $subject, $text) {


  /* ToDo: Just mail(); or PHPMailer TBD */

  /*$mail = new PHPMailer(); // create a new object
  $mail->IsSMTP(); // enable SMTP
  $mail->SMTPDebug = false; // debugging: 1 = errors and messages, 2 = messages only
  $mail->SMTPAuth = true; // authentication enabled
  $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED
  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 465; // or 587
  $mail->Username = 'fajournalmon@gmail.com';
  $mail->Password = 'KOHTcKg0X!Yu';
  $mail->SetFrom('fajournalmon@gmail.com');
  $mail->Subject = $subject;
  $mail->Body = $text;
  $mail->AddAddress($address);
  $mail->IsHTML(true);
  if ($address == 'admin@kieran.de') {
    $return = $mail->Send();
  } else {
    $return = 1;
  }
  if ($return != 1) {
    $to = 'admin@kieran.de';
    $subject = 'Error journal send mail';
    $txt = __FILE__ . ' Error: ' . $return . '<br>';
    $headers = 'From: fajournal@kieran.de';
    mail($to, $subject, $txt, $headers);
    die("Unable to send confirmation mail. You will receive an email later!");
  }*/
}

function getRandomString($Length) {
  $Chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  return substr(str_shuffle($Chars), 0, $Length);
}