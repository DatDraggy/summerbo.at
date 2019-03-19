<?php
require_once(__DIR__ . '/../backend/config.php');
require_once(__DIR__ . '/../backend/funcs.php');

$dbConnection = buildDatabaseConnection($config);

$sql = 'SELECT chat_id, user_id, message_id FROM telegram_messages WHERE time <> 0 AND time + 1800 < UNIX_TIMESTAMP()';
$stmt = $dbConnection->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();

foreach ($rows as $row){
  kickUser($row['chat_id'], $row['user_id']);
  deleteMessages($row['chat_id'], $row['user_id']);
}