<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('config.php');
require_once('funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT email, nickname, users.id, efregid, sponsor FROM users INNER JOIN balance ON balance.id = users.id WHERE status > 1 AND topay = 35';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $nickname = $row['nickname'];
  $efregid = $row['efregid'];
  $topay = $config['priceAttendee'];
  $viptext = 'No';
  if($row['sponsor'] == 1){
    $viptext = 'Yes';
  }
  sendEmail($row['email'], 'About your registration', "Dear $nickname,
 
Your registration was approved by our registration team.
  However, due to a technical difficulty, some people may have received a mail stating they got an Early Bird ticket instead of a Regular one. We would like to apologize for the inconvenience.
 
<b>Your Registration: Regular Ticket
Ticket price: $topay EUR, excl. VIP
Your VIP status: $viptext </b>

  Ticket price payable via <a href=\"https://reg.eurofurence.org\">Eurofurence Registration</a> <b>starting the 25th of March</b> (yes, we will send a reminder, no worries).
 
Are you a VIP or will you upgrade in the future? That'd be great! Here is how we handle the extra payment:

To make 100% of the VIP profits available to Eurofurence Charity we want to avoid all transaction fees and expenses. At badge pickup, a Charity staff member will directly collect your 15 Euros (you can bring more of course) and you'll get upgraded on the spot!
 
Your EF Regnumber is $efregid - please double check that this is correct BEFORE the 25th of March via <a href=\"https://summerbo.at/user/details\">summerbo.at</a>
  
If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
 
Your Boat Party Crew
", true);
}