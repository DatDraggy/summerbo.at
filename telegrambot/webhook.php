<?php
require_once(__DIR__ . '/../files/config.php');
if (stripos($_SERVER['REQUEST_URI'], $config['secretPath']) === false) {
  die();
}
echo 'rawr';
require_once(__DIR__ . '/../files/funcs.php');

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
    list($targetUserId, $status, $confirm) = explode($callbackData, '|');
    if ($status === 'approve') {
      if (approveRegistration($targetUserId)) {
        sendMessage($chatId, 'Registration has been approved.');
        $email = getRegDetails($targetUserId, 'email')['email'];
        sendEmail($email, 'Subject', 'Reg approved. Status 2');
      }
    }
    else if ($status === 'reject') {
      if ($confirm == 1) {
        if (rejectRegistration($targetUserId)) {
          sendMessage($chatId, 'Registration has been rejected.');
          $email = getRegDetails($targetUserId, 'email')['email'];
          sendEmail($email, 'Subject', 'Reg rejected. Status Deleted');
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
  }
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
if(isset($data['message']['text'])){
  $text = $data['message']['text'];
}


if(isset($text)) {
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
    if (!empty($messageArr[1]) && $messageArr[1] !== '/apply') {
      $dbConnection = buildDatabaseConnection($config);
      $application = explode(' ', $text, 2)[1];
      $saveName = $senderName;
      if ($senderUsername !== NULL) {
        $saveName = $senderUsername;
      }
      saveApplication($chatId, $saveName, $application);
      sendStaffNotification($chatId, "<b>New application from $saveName</b>:
$application");
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
      break;
    case '/help':
      break;
    default:
      sendMessage($chatId, 'Unknown Command. Please try again or use /help');
  }
}