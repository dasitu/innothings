<?php
require "initial.php";

//Use hook to log some generic access messages
$app->hook('slim.before.dispatch', function () use ($app) {
	$log = $app->getLog();
    $request = $app->request;
	$ip = $request->getIp();
	$method = $app->request->getMethod();
    $log->debug("[$ip]Request path: [$method]" . $request->getPathInfo());
});
$app->hook('slim.after.router', function () use ($app) {
	$log = $app->getLog();
    $request = $app->request;
	$ip = $request->getIp();
    $response = $app->response;
	$log->debug("[$ip]Response status: " . $response->getStatus());
}); 

//example and initial testing
$app->get('/hello/:name', function ($name) use ($app) {
	try{
		$log = $app->getLog();
		$log->debug("Get the parameter: $name.");
		if($name == "exception"){
			throw new Exception('User exception is trying');
		}
		echo "Hello, $name";	
	}
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

/**********User related API**********/
//Get the API-KEY
$app->get('/key', function() use ($app, $db) {
	try {
		$log = $app->getLog();
		//Validate user according to user name and password from http head
		$usr_name = $app->request->headers->get('account');
		$usr_pwd = $app->request->headers->get('password');
		$mobile_code = $app->request->headers->get('mobile_code');
		
		$log->debug("Got the information from header: account=$usr_name,password=$usr_pwd,mobile_code=$mobile_code");
		//mobile_code is higher priority
		//use mobile code to login first
		if($mobile_code){
			$log->debug("Geting key by username=$usr_name and mobile code=$mobile_code");
			$msg = "User name or mobile code is not correct or expired";
			$user = $db->select("user", "usr_name = '$usr_name' and usr_mobile_code = '$mobile_code' and usr_code_expire > NOW()");
		}
		//use user password to login
		else if($usr_pwd){
			$log->debug("Getting key by user name=$usr_name and password=$usr_pwd");
			$user = $db->select("user", "usr_name = '$usr_name' and usr_pwd = '$usr_pwd' ");
			$msg = "User name or password is not correct";
		}
		else{
			$user = "";
			$msg = "Both password and mobile code are empty";
		}
		//start to authenticate if we have the value
		if(empty($user)){
			throw new Exception("$msg");
		}
		
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode(array('key'=>$user[0]['usr_key']));
	}
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Create new user
$app->post('/user', function() use ($app, $db) {
	try {
		//Get and decode JSON request body
		$body = $app->request()->getBody();
		$input = json_decode($body,true);
		
		//$keys must exist and have value
		$keys = array("usr_name", "usr_pwd");		
		if(checkEmpty($input, $keys)){
			throw new Exception('User name and password is the mandatory parameters ');
		}
		
		//Check whether this user is existed
		//TODO: Just verified the user name, how about other parameters?
		$usr_name = $input['usr_name'];
		$old_user = $db->select("user", "usr_name='$usr_name'");
		if(!empty($old_user)){
			throw new Exception('User name is already existed!');
		}
		
		//Assign the user key to input data, it is needed for database insert.
		$input['usr_key'] = generateKey($usr_name);
				
		$db->insert("user", $input);
		
		//update the user['usr_id'] to newly created ID
		$user['usr_id'] = $db->lastInsertId();
				
		//send the json response header
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode($user);
	} 
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Retrieve user information
$app->get('/user/:id', function($id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		if($id != $usr_id){
			throw new Exception('You are not allowed to see other user');
		}
	
		// query database and return the result
		$user = $db->select("user", "usr_id='$usr_id'");
		
		//password should be removed from response
		unset($user[0]['usr_pwd']);
		
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode($user[0]);
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Update user information
$app->put('/user/:id', function($id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		if($id != $usr_id){
			throw new Exception('You are not allowed to update other user');
		}
		
		//get and decode JSON request body
		$request = $app->request();
		$body = $request->getBody();
		$input = json_decode($body,true);
		
		//$keys must exist and have value
		$keys = array("usr_name", "usr_pwd");		
		if(checkEmpty($input, $keys)){
			throw new Exception('User name and password is the mandatory parameters ');
		}
		
		//Not all parameters can be updated
		$update['usr_name'] = trim($input['usr_name']);
		$update['usr_pwd'] = trim($input['usr_pwd']);
		$update['usr_email'] = trim($input['usr_email']);
			
		//update the information into db
		$db->update("user", $update, "usr_id='$usr_id'");

		//send the json response header
		$app->response()->header('Content-Type', 'application/json');
		
		echo json_encode(array('success' => 1));
	} 
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Delete user information
$app->delete('/user/:id', function($id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		if($id != $usr_id){
			throw new Exception('You are not allowed to delete other user');
		}
				
		//delete the user directly
		//TODO: seems PDO has some problem, can not check the returned rowCount
		$db->delete("user","usr_id = '$id'");
		
		//Return success
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode(array('success' => 1));
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

/**********Device related API**********/
//Create new device
$app->post('/device', function() use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
	
		// get and decode JSON request body
		$body = $app->request()->getBody();
		$input = json_decode($body,true);
		$dev_sn = $input['dev_sn'];
		
		//Assign the user ID to input data, it is needed for database insert.
		$input['usr_id'] = $usr_id;
		
		//Check whether this is existed device according to dev_sn
		$old_dev = $db->select("device", "dev_sn = '$dev_sn'");
		
		if(!empty($old_dev)){
		
			//TODO: What if the device is existed but belong to others
			throw new Exception('dev_sn is already existed!');
		}
		
		//Seems this dev_sn is new, it should be created.
		else {
			$db->insert("device", $input);
			
			//update the device['dev_id'] to newly created ID
			$device['dev_id'] = $db->lastInsertId();
		}
		
		//send the json response header
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode($device);

	} 
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Retrieve one device according to the device ID 
$app->get('/device/:id', function($id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
	
		//TODO:Set the returned column, remove usr_id column
		
		// query database for single device
		$device = $db->select("device", "dev_id='$id' and usr_id='$usr_id'");

		if ($device) {
			// if found, return JSON response
			$app->response()->header('Content-Type', 'application/json');
			echo json_encode($device[0]);
		} 
		else {
			throw new Exception('No records');
		}
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//List all of the devices
$app->get('/device', function() use ($app, $db) {
	try{
		//Validate user
		$usr_id = validateApiKey($app, $db);

		//TODO:Set the returned column, remove usr_id column
				
		// query database for all devices
		$devices = $db->select("device","usr_id='$usr_id'");
		
		if(!empty($devices)){
			// send response header for JSON content type
			$app->response()->header('Content-Type', 'application/json');

			// return JSON-encoded response body with query results
			echo json_encode($devices);		
		}
		else{
			throw new Exception('No records');
		}
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Update device information
$app->put('/device/:id', function($id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		// get and decode JSON request body
		$request = $app->request();
		$body = $request->getBody();
		$input = json_decode($body,true);
		
		//update the information into db
		//Only active device can be updated.
		$bool = $db->update("device", $input, "dev_id = '$id' and usr_id='$usr_id'");

		//send the json response header
		$app->response()->header('Content-Type', 'application/json');
		
		if($bool) {
			echo json_encode(array('success' => 1));
		}
		else{
			//TODO: There are may some other exceptions
			throw new Exception('No matched record can be updated');
		}
	} 
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Delete device information
$app->delete('/device/:id', function($id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		//Check whether this device belong to this user
		$device = $db->select("device","dev_id = '$id' and usr_id = '$usr_id'");

		if(empty($device)){
			throw new Exception('No matched record');
		}
		
		// query database for single device
		$result = $db->delete("device","dev_id = '$id'");
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode(array('success' => 1));
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//**********Sensor related API**********//
//Create new sensor
$app->post('/device/:id/sensor', function($dev_id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
	
		// get and decode JSON request body
		$body = $app->request()->getBody();
		$input = json_decode($body,true);
		
		//Check whether this is existed device according to dev_id and usr_id
		$device = $db->select("device", "dev_id = '$dev_id' and usr_id = '$usr_id'");
		
		if(empty($device)){		
			throw new Exception('No matched device');
		}
		
		//Add device ID to the inserted data
		$input['dev_id'] = $dev_id;
		
		//Create the new sensor to database
		$db->insert("sensor", $input);
		
		//form the return value, which only have the newly created sen_id
		$sensor['sen_id'] = $db->lastInsertId();

		//send the json response header
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode($sensor);
	}
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Retrieve one sensor by sensor ID from sensor_view view
$app->get('/device/:id/sensor/:sid', function($dev_id, $sen_id) use ($app, $db){
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
	
		// query database for single device
		$sensor = $db->select("sensor_view", "sen_id = '$sen_id' and usr_id = '$usr_id' and dev_id = '$dev_id'");

		if ($sensor) {
			// if found, return JSON response
			$app->response()->header('Content-Type', 'application/json');
			echo json_encode($sensor);
		} 
		else {
			throw new Exception('No matched record');
		}
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//List all of the sensors
$app->get('/device/:id/sensor', function($dev_id) use ($app, $db) {
	try{
		//Validate user
		$usr_id = validateApiKey($app, $db);

		// query database for all sensor
		$devices = $db->select("sensor_view","usr_id = '$usr_id' and dev_id = '$dev_id'");
		
		if(!empty($devices)){
			// send response header for JSON content type
			$app->response()->header('Content-Type', 'application/json');

			// return JSON-encoded response body with query results
			echo json_encode($devices);		
		}
		else{
			throw new Exception('No matched records');
		}
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Update sensor information
$app->put('/device/:id/sensor/:sid', function($dev_id, $sen_id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		// get and decode JSON request body
		$body = $app->request()->getBody();
		$input = json_decode($body,true);
		
		//Check whether this sensor belong to this user
		$sensor = $db->select("sensor_view","sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

		if(empty($sensor)){
			throw new Exception('No matched record');
		}
		
		//update the information into db
		$bool = $db->update("sensor", $input, "sen_id = '$sen_id'");

		//send the json response header
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode(array('success' => 1));
		
	} 
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Delete sensor information
$app->delete('/device/:id/sensor/:sid', function($dev_id, $sen_id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		//Check whether this sensor belong to this user
		$sensor = $db->select("sensor_view","sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

		if(empty($sensor)){
			throw new Exception('No matched record');
		}
		
		//Delete this sensor
		$db->delete("sensor", "sen_id = '$sen_id'");
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode(array('success' => 1));
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//**********Data point related API**********//
//Create new data
$app->post('/device/:id/sensor/:sid/datapoint', function($dev_id, $sen_id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
	
		// get and decode JSON request body
		$body = $app->request()->getBody();
		$input = json_decode($body,true);
		
		//Check data
		$keys = array('timestamp','value','type');
		if(checkEmpty($input, $keys)){
			throw new Exception('Data format is not correct!');
		}
		
		//Check whether this is existed sensor according to dev_id and usr_id
		$sensor = $db->select("sensor_view", "sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");
		
		if(empty($sensor)){		
			throw new Exception('No matched sensor');
		}
		
		//Translate the input array to insert array.
		$insert['dat_time'] = $input['timestamp'];
		$insert['dat_type'] = $input['type'];
		if(is_array($input['value'])){
			$insert['dat_value'] = json_encode($input['value']);
		}
		else{
			$insert['dat_value'] = $input['value'];
		}
		$insert['sen_id'] = $sen_id;
		
		//Create the new sensor to database
		$db->insert("datapoint", $insert);
		
		//form the return value, which only have the newly created dat_id
		$data['dat_id'] = $db->lastInsertId();

		//send the json response header
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode($data);
	}
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Retrieve data according to the timeframe
$app->get('/device/:id/sensor/:sid/datapoint', function($dev_id, $sen_id) use ($app, $db) {
	try{
		$start_time = $app->request->headers->get('starttime');
		$end_time	= $app->request->headers->get('endtime');
		
		if(empty($start_time) || empty($end_time)){
			throw new Exception('Time frame should be specified in the header!');
		}
		
		$datapoint = $db->select("datapoint","dat_time > '$start_time' and dat_time < '$end_time'");
		
		//Convert the stored json to array, then it can be used while json_encode
		for($i=0;$i<count($datapoint);$i++){
			if($datapoint[$i]['dat_type'] != 'value'){
				$datapoint[$i]['dat_value'] = json_decode($datapoint[$i]['dat_value'],true);
			}
		}
		
		//Send the response
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode($datapoint);
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Retrieve one datapoint by datapoint ID
$app->get('/device/:id/sensor/:sid/datapoint/:did', function($dev_id, $sen_id, $dat_id) use ($app, $db){
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
	
		//query database for single data
		$datapoint = $db->select("datapoint_view", "dat_id = '$dat_id' and sen_id = '$sen_id' and usr_id = '$usr_id' and dev_id = '$dev_id'");

		if ($datapoint) {
			//Adjust the data according to the data type
			if($datapoint[0]['dat_type'] != 'value'){
				$datapoint[0]['dat_value'] = json_decode($datapoint[0]['dat_value'],true);
			}
			// if found, return JSON response
			$app->response()->header('Content-Type', 'application/json');
			echo json_encode($datapoint[0]);
		} 
		else {
			throw new Exception('No matched record');
		}
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Update datapoint
$app->put('/device/:id/sensor/:sid/datapoint/:did', function($dev_id, $sen_id, $dat_id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		// get and decode JSON request body
		$body = $app->request()->getBody();
		$input = json_decode($body,true);
		
		$keys = array('timestamp','value','type');
		if(checkEmpty($input, $keys)){
			throw new Exception('Data format is not correct!');
		}
		
		//Check whether this data belong to this user
		$datapoint = $db->select("datapoint_view","dat_id='$dat_id' and sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

		if(empty($datapoint)){
			throw new Exception('No matched record');
		}
		
		//Translate the input array to insert array.
		$update['dat_time'] = $input['timestamp'];
		$insert['dat_type'] = $input['type'];
		if(is_array($input['value'])){
			$update['dat_value'] = json_encode($input['value']);
		}
		else{
			$update['dat_value'] = $input['value'];
		}

		//update the information into db
		$bool = $db->update("datapoint", $update, "dat_id = '$dat_id'");

		//send the json response header
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode(array('success' => 1));
		
	} 
	catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

//Delete datapoint
$app->delete('/device/:id/sensor/:sid/datapoint/:did', function($dev_id, $sen_id, $dat_id) use ($app, $db) {
	try {
		//Validate user
		$usr_id = validateApiKey($app, $db);
		
		//Check whether this data belong to this user
		$datapoint = $db->select("datapoint_view","dat_id='$dat_id' and sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

		if(empty($datapoint)){
			throw new Exception('No matched record');
		}
		
		//Delete this sensor
		$db->delete("datapoint", "dat_id = '$dat_id'");
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode(array('success' => 1));
	}
	catch (Exception $e){
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$app->log->error('['.$app->request->getIp().']' . $e->getMessage());
	}
});

$app->run();
?>