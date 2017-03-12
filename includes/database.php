<?php

// database settings: HostGator
/*
$server = 'localhost';
$user = 'snowneos_user';
$pass = 'cloud7';
$db = 'snowneos_admin-area';
*/

//database settings: localhost

$server = 'localhost';
$user = 'youruser';
$pass = 'yourpass';
$db = 'yourdb';

// connect to the database
$Database = new mysqli($server, $user, $pass, $db);

// error reporting
mysqli_report(MYSQLI_REPORT_ERROR);
?>
