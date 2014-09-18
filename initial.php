<?php
require 'Slim/Slim.php';
require 'phpPdoWrapper.php';
require 'dateTimeFileWriter.php';
require 'CCPRestSDK.php';
require 'funcs.php';

//********** register Slim auto-loader **********//
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->config('debug', true);

//********** set up database connection **********//
$dsn = "mysql:host=localhost;port=3306;dbname=innothing";
$dbuser = "test";
$dbpass = "test12";
$db = new db($dsn, $dbuser, $dbpass);

//********** log related setup **********//
$app->log->setEnabled(true);
$app->log->setWriter(new \Slim\Logger\DateTimeFileWriter());
$app->log->setLevel(\Slim\Log::DEBUG);

//********** set up the sms server connection **********//
//主帐号
$accountSid = 'aaf98f89486445e60148783a66860604';
//主帐号Token
$accountToken = '8bcd2b195e75494e9130e99c302e1d8b';
//应用Id
$appId = '8a48b5514864415701487ce7cf3b08e2';
//请求地址，格式如下，不需要写https://
$serverIP = 'sandboxapp.cloopen.com';
//请求端口 
$serverPort = '8883';
//REST版本号
$softVersion = '2013-12-26';
//短信模板ID
$tempId = 1;
//短信验证码有效期（分钟）
$expire = 5;
//短信内容，使用六位随机数，以及有效期
$smsData = array(rand(100000,999999),$expire);
//Create the object for sending sms
$smsSender = new REST($serverIP,$serverPort,$softVersion);
$smsSender->setAccount($accountSid,$accountToken);
$smsSender->setAppId($appId);
//生成全局配置，方便直接使用
$smsConf = array(
	"tempID" => $tempId,
	"expire" => $expire,
	"smsData" => $smsData
);
$app->config('smsConf',$smsConf);
?>
