<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('config.php');
require_once('funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT email, nickname, users.id FROM users INNER JOIN balance ON balance.id = users.id WHERE status > 1 AND topay = 35';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  sendEmail($row['email'], 'About your registration', "Dear {$row['nickname']},
 
Your registration was approved by our registration team.
However, due to a technical difficulty, some people may have received a mail stating they got an Early Bird ticket instead of a Regular one. We would like to apologize for the inconvenience.
 
Your Registration = Regular Ticket (excl. VIP.)(VIP is Status only viewable on our website)
 
Your ticket price is 35 € EUR.
Payable via <a href=\"https://reg.eurofurence.org\">Eurofurence Registration</a> starting the 25th of March.
 
Are you a VIP or will you upgrade in the future?
The 15 EUR surcharge will be paid at Badge Pickup.
 
Please make sure to provide your correct EF Regnumber BEFORE the 25th of March via <a href=\"https://summerbo.at/user/details\">summerbo.at</a>
 
You will then also receive an email from Eurofurence regarding the payment of your ticket.
 
If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
 
Your Boat Party Crew
");
}