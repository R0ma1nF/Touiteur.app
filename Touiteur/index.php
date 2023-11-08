<?php
// index.php
require 'vendor/autoload.php';

use iutnc\touiteur\dispatch\Dispatcher;
session_start();
$_SESSION['user'] = [
    'id' => 0,
    'role' => 10
];
$dispatcher = new Dispatcher();
$dispatcher->run();
