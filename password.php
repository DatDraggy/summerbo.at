<?php
require_once('backend/config.php');
require_once('backend/funcs.php');

if (!empty($_GET['token'])) {
  $token = $_GET['token'];
  $dbConnection = buildDatabaseConnection($config);
  $userId = getIdFromToken($token);
  if ($userId !== false) {
  } else {
    header('Location: login');
    die();
  }
} else {
  header('Location: login');
  die();
}
?>


<?php /* Goes into hidden input */
echo $token; ?>
