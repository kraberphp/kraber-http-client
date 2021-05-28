<?php

$loader = require __DIR__ .'/../vendor/autoload.php';
$loader->addPsr4('Kraber\\Test\\Unit\\', __DIR__."/unit");
$loader->addPsr4('Kraber\\Test\\Integration\\', __DIR__."/integration");
