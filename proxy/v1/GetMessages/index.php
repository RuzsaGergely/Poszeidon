<?php
header("Content-Type: application/json");

//Getting the input
$rawdata = file_get_contents("php://input");
//Decoding the input
$decoded = json_decode($rawdata, true);

//if (isset($decoded['url'], $decoded['userlogin'], $decoded['password']) && !empty($decoded['url'] . $decoded['userlogin'] . $decoded['password']))
if (isset($decoded['url'], $decoded['userlogin'], $decoded['password']) && !empty($decoded['url'] . $decoded['userlogin'] . $decoded['password'])) {
    getMessages($decoded['url'], $decoded['userlogin'], $decoded['password']);
} else {
    respError(400, "Invalid Request", "Missing parameter or empty key");
}

function respError($httpcode, $httpstatus, $errormessage)
{
    $response['http_code'] = $httpcode;
    $response['http_status'] = $httpstatus;
    $response['error_message'] = $errormessage;

    $json_response = json_encode($response);
    echo $json_response;
}

function getMessages($schoolapi, $userlogin, $loginpass){
    
    //If last charachter of URL is / then remove it
    if (substr($schoolapi, -1, 1) == '/')
    {
        $schoolapi = substr($schoolapi, 0, -1);
    }
    
    $messageid = 0;
    $currentPage = 0;
    $messageSortNum = 0;

    if(isset($GLOBALS['decoded']['messageID']) && !empty($GLOBALS['decoded']['messageID'])){
        $messageid = $GLOBALS['decoded']['messageID'];
    }
    if(isset($GLOBALS['decoded']['currentPage']) && !empty($GLOBALS['decoded']['currentPage'])){
        $currentPage = $GLOBALS['decoded']['currentPage'];
    }
    if(isset($GLOBALS['decoded']['messageSortNum']) && !empty($GLOBALS['decoded']['messageSortNum'])){
        $messageSortNum = $GLOBALS['decoded']['messageSortNum'];
    }

    $url = sprintf("%s/GetMessages", $schoolapi);

    $data = array(
        "UserLogin" => $userlogin,
        "Password" => $loginpass,
        "MessageID" => $messageid,
        "MessageSortEnum" => $messageSortNum,
        "CurrentPage" => $currentPage
    );

    //Create CURL instance
    $curl = curl_init();
    //CURL settings
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    //Execute
    $result = curl_exec($curl);
    if (!$result) {
        $result = null;
    }
    curl_close($curl);

    echo $result;
}

?>