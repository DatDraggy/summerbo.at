<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('config.php');
require_once('funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT email, nickname, users.id, efregid FROM users INNER JOIN balance ON balance.id = users.id WHERE status > 1 AND users.id = 12';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

//foreach ($rows as $row) {
  sendEmail($row['email'], 'About your registration', "Dear $nickname,
 
Your registration was approved by our registration team.
However, due to a technical difficulty, some people may have received a mail stating they got an Early Bird ticket instead of a Regular one. We would like to apologize for the inconvenience.

Your Registration = Regular Ticket, Ticket price 35 € EUR, excl. VIP.
Payable via <a href=\"https://reg.eurofurence.org\">Eurofurence Registration</a> starting the 25th of March. (Yes, we will send a reminder, no worries.)
 
Are you a VIP or will you upgrade in the future? That'd be great! Here is how we handle the extra payment:

To make 100% of the VIP profits available to Eurofurence Charity we want to avoid all transaction fees and expenses. At badge pickup, a Charity staff member will directly collect your 15 Euros (you can bring more of course) and you'll get upgraded on the spot! Sounds good? We think so! 
 
Your EF Regnumber is {$row['efregid']} - please double check that this is correct BEFORE the 25th of March via <a href=\"https://summerbo.at/user/details\">summerbo.at</a>
 
You will then also receive an email from Eurofurence regarding the payment of your ticket.
 
If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
 
Your Boat Party Crew
");
//}