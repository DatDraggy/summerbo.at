<?php
require_once('config.php');
if ($_GET['password'] !== $config['debugPw']) {
  die('lol nah');
}

require_once('funcs.php');

$email = 'team@summerbo.at';
$userId = 1;
$nickname = 'Test';

sendEmail($email, 'Summerbo.at Payment Reminder', "<p>Dear $nickname,</p>

Do you still want to join our party? Sadly we haven't received the payment for your ticket yet. Please make sure to pay within the coming 7 days to secure your spot on the deck. The total amount of €$remaining.- is still open. 
Below you will find our bank details to transfer the money.

<p>Bank details:
Name: Edwin Verstaij
Bank: Bunq
IBAN: NL04 BUNQ 2290 9065 14
IC/SWIFT: BUNQNL2AXXX
Comment: $userId, $nickname</p>

Did you already pay everything? Please ignore this email or send us a message.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
", true);