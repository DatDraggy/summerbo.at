<?php
require_once('config.php');
require_once('funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT email, nickname, users.id FROM users INNER JOIN balance ON balance.id = users.id WHERE status > 1 AND topay = 35';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  sendEmail($row['email'], 'About your registration', '');
}