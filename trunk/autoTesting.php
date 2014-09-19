<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
    body {
        font-family: Arial; font-size:0.875em;
        }
    table{ 
        table-layout:fixed; 
        empty-cells:show; 
        border-collapse: collapse; 
        margin:0 auto; 
        border:1px solid #cad9ea; 
        color:#666; 
        word-break:break-all; 
        word-wrap:break-word;
        width: 80%;
    }
    table td { 
        border:1px solid #cad9ea;
        padding:5px 5px 5px 5px;
    } 
</style>
</head>
<body>
<?php
//This is the auto testing script for all of current implemented RESTful interface
//It is use curl to send the http request and show the results
function curl_request($desc,$add,$http_method="GET",$header=array(),$body="")
{
    $host='http://114.215.208.235:8080';
    $url = $host.$add;
    
    //print_r($header);
    $ch = curl_init();
    //set some common parameters
    $options = array(CURLOPT_URL => "$url",
                     CURLOPT_SSL_VERIFYHOST => 0,
                     CURLOPT_SSL_VERIFYPEER => 0,
                     CURLOPT_HEADER => 1,
                     CURLOPT_RETURNTRANSFER => 1,
                     CURLOPT_USERAGENT => "autoTester",
                     CURLINFO_HEADER_OUT => 1,
                     CURLOPT_HTTPHEADER => $header,
                    );
   
    // Add some additional options according to the request type
    if($http_method == "POST")
    {
        $others = array(CURLOPT_POST => 1,
                        CURLOPT_POSTFIELDS => "$body"
                       );
    }
    else if($http_method=="DELETE")
    {
        $others = array(CURLOPT_CUSTOMREQUEST => "DELETE"
                       );
    }
    else if($http_method == "PUT")
    {
        $others = array(CURLOPT_PUT => 1
                       );
    }

    //Merge the common option and other options together
    if(!empty($others)){
        $options = array_merge($options, $others);
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec ($ch);
    if($response == FALSE)
    {
        echo "Connection failed!!";
        return false;
    }

    $info = curl_getinfo($ch);
    curl_close($ch);
    
    //Start to print out the needed information
    $headerSize = $info['header_size'];
    $res_header = substr($response, 0, $headerSize);
    $res_body = substr($response, $headerSize);

    echo "<table>";
    echo "<tr><td width='15%'>Scenario</td><td>$desc</td></tr>";
    echo "<tr><td>"."Request URL"."</td><td>".$info['url']."</td></tr>";
    echo "<tr><td>"."Request header"."</td><td><pre>".$info['request_header']."</pre></td></tr>";
    echo "<tr><td>"."Request body"."</td><td><pre>".$body."</pre></td></tr>";
    echo "<tr><td>"."Response code "."</td><td>".$info['http_code']. "</td></tr>";
    echo "<tr><td>"."Response header "."</td><td><pre>". $res_header. "</pre></td></tr>";
    echo "<tr><td>"."Response body"."</td><td>".$res_body."</td></tr>";
    echo "<tr><td>"."Total transaction time "."</td><td>".$info['total_time']. " seconds</td></tr>";
    echo "</table><BR>";
    return true;
}
curl_request("最基本的框架验证","/hello/autoTesting");
curl_request("获得单个用户信息 -- API-KEY不正确或为空","/user/1");

$header = array("API-KEY:49a98af413554b98775a3a30931da4fd");
curl_request("获得单个用户信息","/user/1","GET",$header,"");

?>
</body>
</html>