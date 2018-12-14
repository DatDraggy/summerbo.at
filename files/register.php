<?php
//Only here for now so that my IDE doesn't complain about dbConnection
$dbConnection = buildDatabaseConnection($config);
requestRegistrationConfirm(1);