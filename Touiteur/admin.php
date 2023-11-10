<?php
// index.php
require 'vendor/autoload.php';

use admin\touiteur\dispatch\DispatcherAdmin;
session_start();
$dispatcher = new DispatcherAdmin();
$dispatcher->run();
