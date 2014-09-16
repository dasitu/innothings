<?php
//********** some useful functions **********//
function sendTemplateSMS($to,$datas,$tempId)
{
     // 初始化REST SDK
     global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
     $rest = new REST($serverIP,$serverPort,$softVersion);
     $rest->setAccount($accountSid,$accountToken);
     $rest->setAppId($appId);
    
     // 发送模板短信
     echo "Sending TemplateSMS to $to <br/>";
     $result = $rest->sendTemplateSMS($to,$datas,$tempId);
     if($result == NULL ) {
         echo "result error!";
         break;
     }
     if($result->statusCode!=0) {
         echo "error code :" . $result->statusCode . "<br>";
         echo "error msg :" . $result->statusMsg . "<br>";
         //TODO 添加错误处理逻辑
     }else{
         echo "Sendind TemplateSMS success!<br/>";
         // 获取返回信息
         $smsmessage = $result->TemplateSMS;
         echo "dateCreated:".$smsmessage->dateCreated."<br/>";
         echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
         //TODO 添加成功处理逻辑
     }
}
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
?>