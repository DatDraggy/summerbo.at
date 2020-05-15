<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}
require_once('../config.php');
require_once('../funcs.php');
require_once('texts.php');

$config['dbname'] = 'boat';

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT email, nickname FROM users';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $nickname = $row['nickname'];

  sendEmail($row['email'], 'Summerbo.at Party Canceled', "Dear $nickname,

" . $texts['coronaCancel'], true, false);
  echo $nickname . ' ' . $row['email'] . "\n";
  sleep(10);
}
