<?php
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
$config['priceSponsor'] = 15;
$config['priceAttendee'] = 35;
$config['mailPassword'] = 'pass';
$config['regOpen'] = false;
$config['captchaSecret'] = '';
$config['attendeesMax'] = 300;
$config['secret'] = '';
$config['permitted_domains'] = ['google.com'];

$status[0] = 'NEW - New registration';
$status[1] = 'VERIFIED - Email address verified';
$status[2] = 'APPROVED - Confirmed by staff';
$status[3] = 'PAID - Fee was paid';

$config['start'] = '2020-08-18';
$config['startReg'] = '2020-08-17';
$config['showTimer'] = true;
$config['readOnly'] = false;