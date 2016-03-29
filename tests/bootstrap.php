<?php

error_reporting(E_ALL);

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('Http\\Client\\Plugin\\Vcr\\', __DIR__);
