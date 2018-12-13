<?php
require_once(__DIR__ . '/../files/config.php');
if (stripos($_SERVER['REQUEST_URI'], $config['secretPath']) === false) {
  die();
}
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
$chatId = $data['callback_query']['message']['chat']['id'];

if (!in_array($chatId, $config['telegramAdmins'])) {
  die();
}

