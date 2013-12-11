<?php
require 'Slim/Slim.php';
require 'class.db.php';

// register Slim auto-loader
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// set up database connection
$dsn = "mysql:host=localhost;port=3306;dbname=innothing";
$dbuser = "test_db";
$dbpass = "axPsPfbr6QE8GFBn";

$db = new db($dsn, $dbuser, $dbpass);

?>