<?php

//Get API key from HTTP header, then return the usr_id if it is valid
function validateApiKey($app, $db){
	// Get the API-KEY from header
	$usr_key = $app->request->headers->get('API-KEY');
	$user = $db->select("user", "usr_key = '$usr_key'");
	if(empty($user)){
		throw new Exception('Invalid API-KEY');
	}
	return $user[0]['usr_id'];
}

//Validate the authentication code for SMS sender
function validateSmsAuth($sms_auth_code,$mobile_number){
	//TODO: Some more secure encrypt and decrypt method should be update here.
	if(md5($mobile_number)==$sms_auth_code){
		return true;
	}
	return false;
}

//Generate API-KEY according to the input
function generateKey($seed){
	$time = microtime();
	$encode = md5($seed.$time);
	return $encode;
}

//Check empty value from $value_arr, these $key_arr keys must existed and not empty
//True if empty value is found
function checkEmpty($value_arr, $key_arr){
	foreach ($key_arr as $key){
		if(empty($value_arr["$key"])){
			return true;
		}
	}
	return false;
}

//PDO Wrapper Error Handler 
function pdoErrorHandler($error) {
	throw new Exception("$error");
}
?>