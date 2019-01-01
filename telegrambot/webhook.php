<?php
require_once(__DIR__ . '/../backend/config.php');
if (stripos($_SERVER['REQUEST_URI'], $config['secretPath']) === false) {
  die('Get Lost.');
}
require_once(__DIR__ . '/../backend/funcs.php');

$response = file_get_contents('php://input');
$data = json_decode($response, true);
$dump = print_r($data, true);

if (isset($data['callback_query'])) {
  $chatId = $data['callback_query']['message']['chat']['id'];
  if (!in_array($chatId, $config['telegramAdmins'])) {
    die();
  }
  $chatType = $data['callback_query']['message']['chat']['type'];
  $callbackData = $data['callback_query']['data'];

  if (stripos($callbackData, '|') !== false) {
    list($targetUserId, $status, $confirm) = explode('|', $callbackData);
    $dbConnection = buildDatabaseConnection($config);
    if ($status === 'approve') {
      if (approveRegistration($targetUserId)) {
        sendMessage($chatId, 'Registration has been approved.');
        list($email, $nickname) = getRegDetails($targetUserId, 'email, nickname');
        sendEmail($email, 'Subject', "Dear $nickname,

Your registration is confirmed by our registration team. Below you will find the bank details for sending us the payment.

Bank details:
Name:
Bank:
IBAN:
BIC:

Please add your Regnumber and Nickname in the Comment field of the transfer.

Please pay within 14 days to make sure you will have a spot on the boat. If you want to change your membership or details, please login on the website with your details.

Alternatively, you can contact us if you would like to pay via PayPal.
But, keep in mind that there will be an additional handling fee for the extra work.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at https://t.me/summerboat.

Your Boat Party Crew
");
      }
    }
    else if ($status === 'reject') {
      if ($confirm == 1) {
        if (rejectRegistration($targetUserId)) {
          sendMessage($chatId, 'Registration has been rejected.');
        }
      }
      else {
        $replyMarkup = array(
          'inline_keyboard' => array(
            array(
              array(
                'text'          => 'Yes',
                'callback_data' => $targetUserId . '|reject|1'
              ),
              array(
                'text'          => 'No',
                'callback_data' => 'no'
              )
            )
          )
        );
        sendMessage($chatId, "Are you sure you want to cancel the registration for $targetUserId?", json_encode($replyMarkup));
      }
    }
    else if ($status === 'view'){

    }
  }
  die();
}
if (!isset($data['message'])) {
  die();
}
$chatId = $data['message']['chat']['id'];
$chatType = $data['message']['chat']['type'];
$senderUserId = preg_replace("/[^0-9]/", "", $data['message']['from']['id']);
$senderUsername = NULL;
if (isset($data['message']['from']['username'])) {
  $senderUsername = $data['message']['from']['username'];
}
$senderName = $data['message']['from']['first_name'];
if (isset($data['message']['from']['last_name'])) {
  $senderName .= ' ' . $data['message']['from']['last_name'];
}
if (isset($data['message']['text'])) {
  $text = $data['message']['text'];
}


if (isset($text)) {
  if (substr($text, '0', '1') == '/') {
    $messageArr = explode(' ', $text);
    $command = explode('@', $messageArr[0])[0];
    if ($messageArr[0] == '/start' && isset($messageArr[1])) {
      $command = '/' . $messageArr[1];
    }
  }
  else {
    die();
  }

  $command = strtolower($command);

  if ($command === '/apply') {
    if (!empty($messageArr[1]) && $messageArr[0] !== '/start') {
      $dbConnection = buildDatabaseConnection($config);
      $application = explode(' ', $text, 2)[1];
      $saveName = $senderName;
      if ($senderUsername !== NULL) {
        $saveName = $senderUsername;
      }
      if (saveApplication($chatId, $saveName, $application)) {
        sendStaffNotification($chatId, "<b>New application from </b><a href=\"tg://user?id=$chatId\">$saveName</a>:
$application");
        sendMessage($chatId, 'Thank you! Your application will be reviewed soon.');
      }
      else {
        sendMessage($chatId, 'Sorry, something went wrong. Perhaps you already applied?');
      }
    }
    else {
      sendMessage($chatId, '<b>How to apply as a volunteer:</b>
Write <code>/apply</code> with a little bit about yourself and experiences behind it.
Example: <code>/apply Hello, I\'m Dragon!</code>');
    }
    die();
  }

  if (!in_array($chatId, $config['telegramAdmins'])) {
    die();
  }
//Admin Commands locked down
  switch ($command) {
    case '/start':
      sendMessage($chatId, '/help');
      break;
    case '/help':
      sendMessage($chatId, 'Approving a payment: /payment
');
      break;
    case '/getunconfirmed':
      $dbConnection = buildDatabaseConnection($config);
      requestUnapproved($chatId);
      break;
    case '/reg':
      if (isset($messageArr[1])) {
        if ($messageArr[1] === 'status') {
          if (isset($messageArr[2])) {
            $details = getRegDetails($messageArr[2], 'id, nickname, status, approvedate');
            $approvedate = strtotime($details['approvedate']);
            sendMessage($chatId, "
Regnumber: {$details['id']}
Nickname: {$details['nickname']}
Status: {$details['status']}
Approved: $approvedate");
          }
          else {
            sendMessage($chatId, 'Please supply a regnumber.');
          }
        }
      }
      break;
    case '/payment':
      if (isset($messageArr[1])) {
        if ($messageArr[1] === 'status') {
          if (isset($messageArr[2])) {
            $details = getPaymentDetails($messageArr[2], 'id, approvedate, amount, topay');
            foreach ($details as $detail) {
              $payByDate = date('m/d/Y', strtotime('+2 weeks', $details['approvedate']));
              sendMessage($chatId, "
Regnumber: {$detail['id']}
Until: $payByDate
Paid: {$detail['amount']}
To pay: {$detail['topay']}");
            }
          }
          else {
            sendMessage($chatId, 'Please supply a regnumber.');
          }
        }
        else if (is_numeric($messageArr[1])) {
          if (isset($messageArr[2])) {
            $dbConnection = buildDatabaseConnection($config);
            $status = (approvePayment($messageArr[2], $senderUserId, $messageArr[1])) ? 'yes' : 'no';
            sendMessage($chatId, 'Updated. Payment completed: ' . $status);
          }
          else {
            sendMessage($chatId, 'Please supply a regnumber.');
          }
        }
        else {
          sendMessage($chatId, 'The given amount is not numeric.');
        }
      }
      else {
        sendMessage($chatId, 'Usage:
<code>/payment</code> <b>amount</b> <b>regnumber</b>');
      }
      break;
    default:
      sendMessage($chatId, 'Unknown Command. Please try again or use /help');
  }
}