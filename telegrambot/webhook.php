<?php
require_once(__DIR__ . '/../backend/config.php');
header('Cache-Control: max-age=0');
if (stripos($_SERVER['REQUEST_URI'], $config['secretPath']) === false) {
  die('Get Lost.');
}
require_once(__DIR__ . '/../backend/funcs.php');

$response = file_get_contents('php://input');
$data = json_decode($response, true);
$dump = print_r($data, true);

if (isset($data['callback_query'])) {
  $chatId = $data['callback_query']['message']['chat']['id'];
  $queryId = $data['callback_query']['id'];
  if (!in_array($chatId, $config['telegramAdmins'])) {
    die();
  }
  $chatType = $data['callback_query']['message']['chat']['type'];
  $callbackData = $data['callback_query']['data'];
  $senderUserId = $data['callback_query']['from']['id'];

  if (stripos($callbackData, '|') !== false) {
    list($targetUserId, $status, $confirm, $time) = explode('|', $callbackData);
    $dbConnection = buildDatabaseConnection($config);
    if ($status === 'approve') {
      if (approveRegistration($targetUserId, $senderUserId)) {
        answerCallbackQuery($queryId, 'Registration has been approved.');
        list($email, $nickname, $regnumber) = getRegDetails($targetUserId, 'email, nickname, id');
        $topay = getBalanceDetails($targetUserId, 'topay - paid as topay')['topay'];
        sendEmail($email, 'Registration Confirmed - Payment Reminder', "Dear $nickname,

Your registration was confirmed by our registration team. Below you will find the bank details to send us the payment.

Bank Details:
Name: Edwin Verstaij
IBAN: DE68 7001 1110 6054 4164 13
BIC/SWIFT: DEKTDE7GXXX
Comment: $regnumber + $nickname

Put your regnumber ($regnumber) and nickname in the comment field of the transfer.

Please pay within 14 days to make sure you will have a spot on the boat. If you want to change your membership or details, please login on <a href=\"https://summerbo.at/login\">https://summerbo.at/login</a>.
Is it not possible to pay us via bank transfer? Please send us an email with your problem and we will try to help you.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
");
      } else {
        answerCallbackQuery($queryId, 'Already approved, rejected or error.');
      }
    } else if ($status === 'reject') {
      if ($confirm == 1 && $time + 10 >= time()) {
        if (rejectRegistration($targetUserId)) {
          answerCallbackQuery($queryId, 'Registration has been rejected.');
        } else {
          answerCallbackQuery($queryId, 'Already rejected or error');
        }
      } else {
        $replyMarkup = array(
          'inline_keyboard' => array(
            array(
              array(
                'text'          => 'Yes',
                'callback_data' => $targetUserId . '|reject|1|' . time()
              ),
              array(
                'text'          => 'No',
                'callback_data' => 'no'
              )
            )
          )
        );
        answerCallbackQuery($queryId);
        sendMessage($chatId, "Are you sure you want to cancel the registration for $targetUserId?", json_encode($replyMarkup));
      }
    } else if ($status === 'handled') {
      answerCallbackQuery($queryId);
      if (isset($data['callback_query']['from']['username'])) {
        $handleName = $data['callback_query']['from']['username'];
      } else {
        $handleName = $data['callback_query']['from']['first_name'];
        if (isset($data['callback_query']['from']['last_name'])) {
          $handleName .= ' ' . $data['callback_query']['from']['last_name'];
        }
      }
      sendStaffNotification(0, 'Application from ' . $confirm . ' was handled by ' . $handleName . '.');
    }
  } else {
    answerCallbackQuery($queryId);
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
  } else {
    die();
  }

  $command = strtolower($command);
  switch (true) {
    case ($command === '/start'):
      sendMessage($chatId, 'Hello! I\'m the Summerbo.at Bot.
To get a command overview, send /help.');
      break;
    case ($command === '/help'):
      sendMessage($chatId, 'Applying for Volunteer: /apply
Location: /venue
Badge pickup: /badge
ID: /id
');
      break;
    case ($command === '/id'):
      sendMessage($chatId, $chatId . ' ' . $senderUserId);
      break;
    case ($command === '/apply'):
      if (!empty($messageArr[1]) && $messageArr[0] !== '/start') {
        $dbConnection = buildDatabaseConnection($config);
        $application = explode(' ', $text, 2)[1];
        $saveName = $senderName;
        if ($senderUsername !== NULL) {
          $saveName = $senderUsername;
        }
        if (saveApplication($chatId, $saveName, $application)) {
          $replyMarkup = array(
            'inline_keyboard' => array(
              array(
                array(
                  'text'          => 'Handled',
                  'callback_data' => $chatId . '|handled|' . $saveName . '|0'
                )
              )
            )
          );
          sendStaffNotification($chatId, "<b>New application from </b><a href=\"tg://user?id=$chatId\">$saveName</a>:
$application", $replyMarkup);
          sendMessage($chatId, 'Thank you! Your application will be reviewed soon.');
          mail('team@summerbo.at', 'New Application!', "By: $saveName
Message: $application");
        } else {
          sendMessage($chatId, 'Sorry, something went wrong. Perhaps you already applied?');
        }
      } else {
        sendMessage($chatId, '<b>How to apply as a volunteer:</b>
Write <code>/apply</code> with a little bit about yourself and experiences behind it.
Example: <code>/apply Hello, I\'m Dragon!</code>');
      }
      die();
      break;
    case ($command === '/reg' && isTelegramAdmin($chatId)):
      if (isset($messageArr[1])) {
        if ($messageArr[1] === 'status') {
          if (isset($messageArr[2])) {
            $dbConnection = buildDatabaseConnection($config);
            $details = getRegDetails($messageArr[2], 'id, nickname, status, approvedate');
            $approvedate = date('Y-m-d', $details['approvedate']);
            sendMessage($chatId, "
Regnumber: {$details['id']}
Nickname: {$details['nickname']}
Status: {$details['status']}
Approved: $approvedate");
          } else {
            sendMessage($chatId, 'Please supply a regnumber.');
          }
        }
      }
      break;
    case ($command === '/blacklist' && isTelegramAdmin($chatId)):
      //ToDo: TBD
      break;
    case ($command == '/getunconfirmed' && isTelegramAdmin($chatId)):
      $dbConnection = buildDatabaseConnection($config);
      requestUnapproved($chatId);
      break;
    case ($command === '/payment' && isTelegramAdmin($chatId)):
      if (isset($messageArr[1])) {
        $dbConnection = buildDatabaseConnection($config);
        if ($messageArr[1] === 'status') {
          if (isset($messageArr[2])) {
            $details = getPaymentDetails($messageArr[2], 'users.id, approvedate, amount, topay');
            if ($details === false) {
              sendMessage($chatId, 'No Payments');
            } else {
              foreach ($details as $detail) {
                $payByDate = date('Y-m-d', strtotime('+2 weeks', $details['approvedate']));
                sendMessage($chatId, "
Regnumber: {$detail['id']}
Until: $payByDate
Paid: {$detail['amount']}
To pay: {$detail['topay']}");
              }
            }
          } else {
            sendMessage($chatId, 'Please supply a regnumber.');
          }
        } else if (is_numeric($messageArr[1])) {
          if (isset($messageArr[2])) {
            $status = (approvePayment($messageArr[2], $senderUserId, $messageArr[1]) ? 'yes' : 'no');
            sendMessage($chatId, 'Updated. Payment completed: ' . $status);
          } else {
            sendMessage($chatId, 'Please supply a regnumber.');
          }
        } else {
          sendMessage($chatId, 'The given amount is not numeric.');
        }
      } else {
        sendMessage($chatId, 'Usage:
<code>/payment</code> <b>amount</b> <b>regnumber</b>');
      }
      break;
    case ($command === '/venue'):
      sendVenue($chatId, 52.473208, 13.458217, 'Estrel Sommergarten', 'Ziegrastraße 44, 12057 Berlin');
      break;
    case ($command === '/badge'):
      sendMessage($chatId, 'On the day of the party you can pick up the badge inside the Estrel Hotel during the afternoon or in the evening in the Biergarten near the boat. Please make sure you bring your ID or Passport with you. The badge is your entrance to the party so please do not lose it. There will be no tickets sold on the day itself.');
      break;
    default:
      sendMessage($chatId, 'Hello! I\'m the Summerbo.at Bot.
To get a command overview, send /help.');
      break;
  }
}
