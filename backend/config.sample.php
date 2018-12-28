<?php
$config = array();
$config['dbuser'] = 'user';
$config['dbpassword'] = 'p4ssw0rd';
$config['dbserver'] = 'database.example.com';
$config['dbport'] = '3309';
$config['dbname'] = 'name';
$config['mail'] = 'mail@mail.com';
$config['sitedomain'] = 'example.com';
$config['telegramAdmins'] = array('user1' => 123, 'user2' => 1234); //Telegram userID
$config['token'] = '123456:aefAOIEFjoauenfseljnFOUnoEAF';
$config['url'] = 'https://api.telegram.org/bot' . $config['token'] . '/';
$config['secretPath'] = 'aDOINauiwdnUdbwniUABdiuabwidu';
$config['priceAddSponsor'] = 15;
$config['priceAttendeeEarly'] = 25;
$config['priceAttendee'] = 35;
$config['mailPassword'] = 'pass';

$status[0] = 'NEW - New registration';
$status[1] = 'VERIFIED - Email address verified';
$status[2] = 'CONFIRMED - Confirmed by staff';
$status[3] = 'PAID - Fee was paid';