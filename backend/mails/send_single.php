<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('../config.php');
require_once('../funcs.php');

$dbConnection = buildDatabaseConnection($config);

$email = '';
$nickname = '';
sendEmail($email, 'Your Summerbo.at Registration has been canceled', "Dear $nickname,

we're sorry to inform you that we had to cancel your Summerbo.at registration because you did not update your Eurofurence registration number in your account.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.
", true);
