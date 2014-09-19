<?php
require "initial.php";

//Use hook to log some generic access messages
$app->hook('slim.before.dispatch', function() use ($app)
{
    $request = $app->request;
    $ip      = $request->getIp();
    $method  = $request->getMethod();
    $app->log->debug("[$ip]Request path: [$method]" . $request->getPathInfo());
});
$app->hook('slim.after.router', function() use ($app)
{
    $ip = $app->request->getIp();
    $app->log->debug("[$ip]Response status: " . $app->response->getStatus());
});

//Use error to log all of error/exception message.
$app->error(function(\Exception $e) use ($app)
{
    $log_msg = $response_msg = $e->getMessage();
    if ($e->getCode())
    {
        $log_msg = "[Code: " . $e->getCode() . "]" . $e->getMessage() . " at " . $e->getFile() . " line " . $e->getLine();
    }
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $response_msg);
    $app->log->error('[' . $app->request->getIp() . ']' . $log_msg);
});

//example and initial testing
$app->get('/hello/:name', function($name) use ($app)
{
    $log = $app->getLog();
    $log->debug("Get the parameter: $name.");
    if ($name == "exception")
    {
        throw new Exception('User exception is called');
    }
    echo "Hello, $name at " . date('c');
});
//*********** Request to send the Text Message to some mobile phone *******//
$app->get('/mobilecode', function() use ($app, $db, $smsSender)
{
    $log = $app->getLog();
    //Validate the authentication code to avoid some illegal calls
    $sms_auth_code = $app->request->headers->get('authCode');
    $mobile_number = $app->request->headers->get('mobile');
    $log->debug("Got sms auth_code=$sms_auth_code and mobile=$mobile_number from http header");
    if (validateSmsAuth($sms_auth_code, $mobile_number))
    {
        // get and decode JSON request body
        $log->debug("SMS authentication code passed.");

        //Get the config data from initial.php
        $smsConf = $app->config('smsConf');
        $smsData = $smsConf["smsData"];
        $tempId  = $smsConf["tempId"];
        $expire  = $smsConf["expire"];

        // start to send
        $log->debug("Sending " . $smsData[0] . " to $mobile_number using template ID:$tempId");
        $result = json_decode('{"statusCode":"000000","TemplateSMS":{"dateCreated":"2014-09-18 15:53:06","smsMessageSid":" ff8080813c373cab013c94b0f0512345"}}');
        //$result = $smsSender->sendTemplateSMS($mobile_number,$smsData,$tempId);
        if ($result == NULL)
        {
            throw new Exception("Unknown error from SMS service provider.");
        }
        if ($result->statusCode != 0)
        {
            throw new Exception($result->statusMsg, $result->statusCode);
        }
        else
        {
            //get the return result
            $smsResult = $result->TemplateSMS;
            $log->debug("Message has been sent to $mobile_number.");
            //Update the mobile code to DB
            //Check whether use existed
            $log->debug("Selecting the user according to usr_tel=$mobile_number");
            $user = $db->select("user", "usr_tel=$mobile_number");
            if (empty($user))
            {
                //insert the new user
                //TODO: Create User class to unify the user related operations
                $log->debug("Can not find the user, start to insert new user.");
                $input = array(
                             'usr_key' => generateKey($mobile_number),
                             'usr_name' => $mobile_number,
                             'usr_pwd' => substr($mobile_number, 3, 8),
                             'utyp_id' => 1,
                             'usr_tel' => $mobile_number
                         );
                $db->insert("user", $input);
                $user_id = $db->lastInsertId();
                $log->debug("New user($mobile_number) has been inserted to DB, User ID: $user_id, password is default.");
            }
            else
            {
                $created    = $smsResult->dateCreated;
                $new_expire = date("Y-m-d H:i:s", strtotime($created . "+" . $smsConf["expire"] . " minutes"));
                $log->debug("User has been found, start to update the usr_mobile_code=" . $smsData[0] . " and usr_code_expire=" . $new_expire . "where usr_tel=$mobile_number");
                //just update the mobile code and expire date
                $user['usr_mobile_code'] = $smsData[0];
                $user['usr_code_expire'] = $new_expire;
                $db->update("user", $user, "usr_tel='$mobile_number'");
                $log->debug("User($mobile_number) mobile code and expire updated.");
            }
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(array(
                                 'success' => 1
                             ));
        }
    }
    else
        throw new Exception("Illegal calls, and it has been recorded.");
});

/**********User related API**********/
//Get the API-KEY
$app->get('/key', function() use ($app, $db)
{
    $log         = $app->getLog();
    //Validate user according to user name and password from http head
    $usr_name    = $app->request->headers->get('account');
    $usr_pwd     = $app->request->headers->get('password');
    $mobile_code = $app->request->headers->get('mobileCode');

    $log->debug("Got the information from header: account=$usr_name,password=$usr_pwd,mobile_code=$mobile_code");
    //mobile_code is higher priority
    //use mobile code to login first
    if ($mobile_code)
    {
        $log->debug("Geting key by username=$usr_name and mobile code=$mobile_code");
        $msg  = "User name($usr_name) or mobile code($mobile_code) is not correct or expired";
        $user = $db->select("user", "usr_name = '$usr_name' and usr_mobile_code = '$mobile_code' and usr_code_expire > NOW()");
    }
    //use user password to login
    else if ($usr_pwd)
    {
        $log->debug("Getting key by user name=$usr_name and password=$usr_pwd");
        $user = $db->select("user", "usr_name = '$usr_name' and usr_pwd = '$usr_pwd' ");
        $msg  = "User name($usr_name) or password($usr_pwd) is not correct";
    }
    else
    {
        $user = "";
        $msg  = "Both password and mobile code are empty";
    }
    //start to authenticate if we have the value
    if (empty($user))
    {
        throw new Exception("$msg");
    }

    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array(
                         'key' => $user[0]['usr_key']
                     ));
});

//Create new user
$app->post('/user', function() use ($app, $db)
{
    //Get and decode JSON request body
    $body  = $app->request()->getBody();
    $input = json_decode($body, true);

    //$keys must exist and have value
    $keys = array(
                "usr_name",
                "usr_pwd"
            );
    if (checkEmpty($input, $keys))
    {
        throw new Exception('User name and password is the mandatory parameters ');
    }

    //Check whether this user is existed
    //TODO: Just verified the user name, how about other parameters?
    $usr_name = $input['usr_name'];
    $old_user = $db->select("user", "usr_name='$usr_name'");
    if (!empty($old_user))
    {
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
});

//Retrieve user information
$app->get('/user/:id', function($id) use ($app, $db)
{
    //Validate user
    $usr_id = validateApiKey($app, $db);

    if ($id != $usr_id)
    {
        throw new Exception('You are not allowed to see other user');
    }

    // query database and return the result
    $user = $db->select("user", "usr_id='$usr_id'");

    //password should be removed from response
    unset($user[0]['usr_pwd']);

    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($user[0]);
});

//Update user information
$app->put('/user/:id', function($id) use ($app, $db)
{
    //Validate user
    $usr_id = validateApiKey($app, $db);

    if ($id != $usr_id)
    {
        throw new Exception('You are not allowed to update other user');
    }

    //get and decode JSON request body
    $request = $app->request();
    $body    = $request->getBody();
    $input   = json_decode($body, true);

    //$keys must exist and have value
    $keys = array(
                "usr_name",
                "usr_pwd"
            );
    if (checkEmpty($input, $keys))
    {
        throw new Exception('User name and password is the mandatory parameters ');
    }

    //Not all parameters can be updated
    $update['usr_name']  = trim($input['usr_name']);
    $update['usr_pwd']   = trim($input['usr_pwd']);
    $update['usr_email'] = trim($input['usr_email']);

    //update the information into db
    $db->update("user", $update, "usr_id='$usr_id'");

    //send the json response header
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array(
                         'success' => 1
                     ));

});

//Delete user information
$app->delete('/user/:id', function($id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    if ($id != $usr_id)
    {
        throw new Exception('You are not allowed to delete other user');
    }

    //delete the user directly
    //TODO: seems PDO has some problem, can not check the returned rowCount
    $db->delete("user", "usr_id = '$id'");

    //Return success
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array(
                         'success' => 1
                     ));

});

/**********Device related API**********/
//Create new device
$app->post('/device', function() use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    // get and decode JSON request body
    $body   = $app->request()->getBody();
    $input  = json_decode($body, true);
    $dev_sn = $input['dev_sn'];

    //Assign the user ID to input data, it is needed for database insert.
    $input['usr_id'] = $usr_id;

    //Check whether this is existed device according to dev_sn
    $old_dev = $db->select("device", "dev_sn = '$dev_sn'");

    if (!empty($old_dev))
    {

        //TODO: What if the device is existed but belong to others
        throw new Exception('dev_sn is already existed!');
    }

    //Seems this dev_sn is new, it should be created.
    else
    {
        $db->insert("device", $input);

        //update the device['dev_id'] to newly created ID
        $device['dev_id'] = $db->lastInsertId();
    }

    //send the json response header
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($device);


});

//Retrieve one device according to the device ID
$app->get('/device/:id', function($id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    //TODO:Set the returned column, remove usr_id column

    // query database for single device
    $device = $db->select("device", "dev_id='$id' and usr_id='$usr_id'");

    if ($device)
    {
        // if found, return JSON response
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode($device[0]);
    }
    else
    {
        throw new Exception('No records');
    }

});

//List all of the devices
$app->get('/device', function() use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    //TODO:Set the returned column, remove usr_id column

    // query database for all devices
    $devices = $db->select("device", "usr_id='$usr_id'");

    if (!empty($devices))
    {
        // send response header for JSON content type
        $app->response()->header('Content-Type', 'application/json');

        // return JSON-encoded response body with query results
        echo json_encode($devices);
    }
    else
    {
        throw new Exception('No records');
    }
});

//Update device information
$app->put('/device/:id', function($id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    // get and decode JSON request body
    $request = $app->request();
    $body    = $request->getBody();
    $input   = json_decode($body, true);

    //update the information into db
    //Only active device can be updated.
    $bool = $db->update("device", $input, "dev_id = '$id' and usr_id='$usr_id'");

    //send the json response header
    $app->response()->header('Content-Type', 'application/json');

    if ($bool)
    {
        echo json_encode(array(
                             'success' => 1
                         ));
    }
    else
    {
        //TODO: There are may some other exceptions
        throw new Exception('No matched record can be updated');
    }

});

//Delete device information
$app->delete('/device/:id', function($id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    //Check whether this device belong to this user
    $device = $db->select("device", "dev_id = '$id' and usr_id = '$usr_id'");

    if (empty($device))
    {
        throw new Exception('No matched record');
    }

    // query database for single device
    $result = $db->delete("device", "dev_id = '$id'");
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array(
                         'success' => 1
                     ));

});

//**********Sensor related API**********//
//Create new sensor
$app->post('/device/:id/sensor', function($dev_id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    // get and decode JSON request body
    $body  = $app->request()->getBody();
    $input = json_decode($body, true);

    //Check whether this is existed device according to dev_id and usr_id
    $device = $db->select("device", "dev_id = '$dev_id' and usr_id = '$usr_id'");

    if (empty($device))
    {
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
});

//Retrieve one sensor by sensor ID from sensor_view view
$app->get('/device/:id/sensor/:sid', function($dev_id, $sen_id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    // query database for single device
    $sensor = $db->select("sensor_view", "sen_id = '$sen_id' and usr_id = '$usr_id' and dev_id = '$dev_id'");

    if ($sensor)
    {
        // if found, return JSON response
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode($sensor);
    }
    else
    {
        throw new Exception('No matched record');
    }

});

//List all of the sensors
$app->get('/device/:id/sensor', function($dev_id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    // query database for all sensor
    $devices = $db->select("sensor_view", "usr_id = '$usr_id' and dev_id = '$dev_id'");

    if (!empty($devices))
    {
        // send response header for JSON content type
        $app->response()->header('Content-Type', 'application/json');

        // return JSON-encoded response body with query results
        echo json_encode($devices);
    }
    else
    {
        throw new Exception('No matched records');
    }

});

//Update sensor information
$app->put('/device/:id/sensor/:sid', function($dev_id, $sen_id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    // get and decode JSON request body
    $body  = $app->request()->getBody();
    $input = json_decode($body, true);

    //Check whether this sensor belong to this user
    $sensor = $db->select("sensor_view", "sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

    if (empty($sensor))
    {
        throw new Exception('No matched record');
    }

    //update the information into db
    $bool = $db->update("sensor", $input, "sen_id = '$sen_id'");

    //send the json response header
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array(
                         'success' => 1
                     ));


});

//Delete sensor information
$app->delete('/device/:id/sensor/:sid', function($dev_id, $sen_id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    //Check whether this sensor belong to this user
    $sensor = $db->select("sensor_view", "sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

    if (empty($sensor))
    {
        throw new Exception('No matched record');
    }

    //Delete this sensor
    $db->delete("sensor", "sen_id = '$sen_id'");
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array(
                         'success' => 1
                     ));

});

//**********Data point related API**********//
//Create new data
$app->post('/device/:id/sensor/:sid/datapoint', function($dev_id, $sen_id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    // get and decode JSON request body
    $body  = $app->request()->getBody();
    $input = json_decode($body, true);

    //Check data
    $keys = array(
                'timestamp',
                'value',
                'type'
            );
    if (checkEmpty($input, $keys))
    {
        throw new Exception('Data format is not correct!');
    }

    //Check whether this is existed sensor according to dev_id and usr_id
    $sensor = $db->select("sensor_view", "sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

    if (empty($sensor))
    {
        throw new Exception('No matched sensor');
    }

    //Translate the input array to insert array.
    $insert['dat_time'] = $input['timestamp'];
    $insert['dat_type'] = $input['type'];
    if (is_array($input['value']))
    {
        $insert['dat_value'] = json_encode($input['value']);
    }
    else
    {
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

});

//Retrieve data according to the timeframe
$app->get('/device/:id/sensor/:sid/datapoint', function($dev_id, $sen_id) use ($app, $db)
{

    $start_time = $app->request->headers->get('starttime');
    $end_time   = $app->request->headers->get('endtime');

    if (empty($start_time) || empty($end_time))
    {
        throw new Exception('Time frame should be specified in the header!');
    }

    $datapoint = $db->select("datapoint", "dat_time > '$start_time' and dat_time < '$end_time'");

    //Convert the stored json to array, then it can be used while json_encode
    for ($i = 0; $i < count($datapoint); $i++)
    {
        if ($datapoint[$i]['dat_type'] != 'value')
        {
            $datapoint[$i]['dat_value'] = json_decode($datapoint[$i]['dat_value'], true);
        }
    }

    //Send the response
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($datapoint);

});

//Retrieve one datapoint by datapoint ID
$app->get('/device/:id/sensor/:sid/datapoint/:did', function($dev_id, $sen_id, $dat_id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    //query database for single data
    $datapoint = $db->select("datapoint_view", "dat_id = '$dat_id' and sen_id = '$sen_id' and usr_id = '$usr_id' and dev_id = '$dev_id'");

    if ($datapoint)
    {
        //Adjust the data according to the data type
        if ($datapoint[0]['dat_type'] != 'value')
        {
            $datapoint[0]['dat_value'] = json_decode($datapoint[0]['dat_value'], true);
        }
        // if found, return JSON response
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode($datapoint[0]);
    }
    else
    {
        throw new Exception('No matched record');
    }

});

//Update datapoint
$app->put('/device/:id/sensor/:sid/datapoint/:did', function($dev_id, $sen_id, $dat_id) use ($app, $db)
{
    //Validate user
    $usr_id = validateApiKey($app, $db);

    // get and decode JSON request body
    $body  = $app->request()->getBody();
    $input = json_decode($body, true);

    $keys = array(
                'timestamp',
                'value',
                'type'
            );
    if (checkEmpty($input, $keys))
    {
        throw new Exception('Data format is not correct!');
    }

    //Check whether this data belong to this user
    $datapoint = $db->select("datapoint_view", "dat_id='$dat_id' and sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

    if (empty($datapoint))
    {
        throw new Exception('No matched record');
    }

    //Translate the input array to insert array.
    $update['dat_time'] = $input['timestamp'];
    $insert['dat_type'] = $input['type'];
    if (is_array($input['value']))
    {
        $update['dat_value'] = json_encode($input['value']);
    }
    else
    {
        $update['dat_value'] = $input['value'];
    }

    //update the information into db
    $bool = $db->update("datapoint", $update, "dat_id = '$dat_id'");

    //send the json response header
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array(
                         'success' => 1
                     ));


});

//Delete datapoint
$app->delete('/device/:id/sensor/:sid/datapoint/:did', function($dev_id, $sen_id, $dat_id) use ($app, $db)
{

    //Validate user
    $usr_id = validateApiKey($app, $db);

    //Check whether this data belong to this user
    $datapoint = $db->select("datapoint_view", "dat_id='$dat_id' and sen_id = '$sen_id' and dev_id = '$dev_id' and usr_id = '$usr_id'");

    if (empty($datapoint))
    {
        throw new Exception('No matched record');
    }

    //Delete this sensor
    $db->delete("datapoint", "dat_id = '$dat_id'");
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array(
                         'success' => 1
                     ));

});

$app->run();
?>