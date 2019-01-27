<?php
require_once('config.php');
require_once('funcs.php');

$dbConnection = buildDatabaseConnection($config);

try {
  $sql = 'SELECT id_internal, nickname, email FROM users WHERE status = 0 ';
  $stmt = $dbConnection->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
} catch (PDOException $e) {
  notifyOnException('Database Select', $config, $sql, $e);
}

foreach ($rows as $row) {
  $userId = $row['id_internal'];
  $nickname = $row['nickname'];
  $email = $row['email'];

  $confirmationLink = requestEmailConfirm($userId);
  if ($confirmationLink === false) {
    mail($config['mail'], 'ERROR IN SUMMERBOAT REG URGENT', $userId . ' No token generate possible');
    $status = 'Unknown Error in Registration. Administrator has been notified';
    session_start();
    $_SESSION['status'] = $status;
    session_commit();
    header('Location: ../register');
    die($status);
  }

  sendEmail($email, 'Please Confirm Your Summerbo.at Registration', "Dear $nickname,

Thank you for your registration with the Summernights party.

Your current status is: {$config['status'][0]}

You first have to verify your email address and confirm your registration by clicking on the following link: <a href=\"$confirmationLink\">$confirmationLink</a>
Afterwards another mail will be sent.

If you have any questions, please send us a message. Reply to this e-mail or contact us via Telegram at <a href=\"https://t.me/summerboat\">https://t.me/summerboat</a>.

Your Boat Party Crew
", true);
}