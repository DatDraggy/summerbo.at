<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}

require_once('../config.php');
require_once('../funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = "SELECT users.id, email, nickname FROM users INNER JOIN balance ON users.id = balance.id WHERE approvedate + 1468800 < UNIX_TIMESTAMP() AND locked = true AND status < 3";
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $userId = $row['id'];
  rejectRegistration($userId);
  sleep(5);
}

mail($config['mail'], 'Enable Payment Check', 'Enable Cronjob daily');