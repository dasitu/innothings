<?php
require 'Slim/Slim.php';
require 'PhpPdoWrapper.php';
require 'DateTimeFileWriter.php';

// register Slim auto-loader
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->config('debug', true);

// set up database connection
$dsn = "mysql:host=localhost;port=3306;dbname=innothing";
$dbuser = "test";
$dbpass = "test12";

$db = new db($dsn, $dbuser, $dbpass);

?>