<?php
if (php_sapi_name() != "cli") {
  die('lol nah');
}

require_once('../config.php');
require_once('../funcs.php');

$dbConnection = buildDatabaseConnection($config);

$remind = '2019-04-01';
$lock = '2019-04-08';
$delete = '2019-04-11';

if (date('Y-m-d', strtotime($remind)) <= date('Y-m-d', time())) {
  remindRemindReg();
}

if (date('Y-m-d', strtotime($lock)) <= date('Y-m-d', time())) {
  remindLockReg();
}

if (date('Y-m-d', strtotime($delete)) <= date('Y-m-d', time())) {
  remindDeleteReg();
}