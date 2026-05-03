<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$appBasePath = realpath(__DIR__.'/../laravel-tpc-bombril') ?: realpath(__DIR__.'/..');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $appBasePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $appBasePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once $appBasePath.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
