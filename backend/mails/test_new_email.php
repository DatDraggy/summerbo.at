<?php
require_once('../config.php');
require_once('/var/libraries/PHPMailer/PHPMailer.php');
require_once('/var/libraries/PHPMailer/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function sendEmail($address, $subject, $text, $internal = false) {
  global $dbConnection, $config;
  if ($internal === false) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    $ipNotice = "--
The following IP triggered this event: <a href=\"https://www.ip-tracker.org/locator/ip-lookup.php?ip=$ip\">$ip</a>.";
  } else {
    $ipNotice = '';
  }
  require_once('../email.php');

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

sendEmail($config['mail'], 'Testing', 'Hi,
this is a test email. Merp.');