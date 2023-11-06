<?php
// index.php
require 'vendor/autoload.php';

use iutnc\touiteur\dispatch\Dispatcher;
session_start();
$dispatcher = new Dispatcher();
$dispatcher->run();
