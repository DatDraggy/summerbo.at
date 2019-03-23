<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('../config.php');
require_once('../funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT email, nickname FROM users WHERE efregid >= 10000';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row){
  $nickname = $row['nickname'];
  $email = $row['email'];

  sendEmail($email, '[ACTION REQUIRED] Your Summerbo.at EF Registration', "Dear $nickname,

We are writing you this email regarding your Summerbo.at registration. While we were checking the entries we saw that your Eurofurence Registration number was not updated yet. Because the payment is going via the Eurofurence system, we ask you to update your number as soon as possible. Deadline for the update is <b>28 April 2019 23:59</b>.

If we have no response from you or your registration number is not updated before the deadline we will have to cancel your ticket. 

<i>What if I am not going to Eurofurence but do want to attend the party?</i>
Please let us know before the deadline via email. Just respond to this message with your request and if possible your Telegram handle. That way we can discuss easily what to do and what is needed.

<i>I decided to not go anymore, can I still cancel my ticket?</i>
It is indeed still possible to cancel your ticket. Please let us know so we can make someone else happy with your spot. 

If you have any questions or concerns left, please let us know. You can contact us via email or Telegram.

We hope to see you at the party!
", true);
}