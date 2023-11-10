<?php
// index.php
require 'vendor/autoload.php';

use iutnc\BackOffice\dispatch\Dispatcher;
session_start();
$dispatcher = new Dispatcher();
$dispatcher->run();
