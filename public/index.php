<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}


$rootPath = __DIR__;

// Agar vendor papka shu joyda bo‘lmasa (demak biz public_html dan ishlayapmiz)
if (!file_exists($rootPath . '/../vendor/autoload.php')) {
    $rootPath = realpath(__DIR__ . '/../../gre_certificate');
} else {
    $rootPath = realpath(__DIR__ . '/..');
}

require $rootPath . '/vendor/autoload.php';
$app = require_once $rootPath . '/bootstrap/app.php';
$app->handleRequest(Request::capture());
