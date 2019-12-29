<?php
header("Content-Type:application/json");

// Getting the input
$rawdata = file_get_contents("php://input");
// Decoding the input
$decoded = json_decode($rawdata, true);

// Checking if URL are isset
if (isset($decoded['url']) && $decoded['url'] != "") {
    getPrivStatement($decoded['url']);
} else {
    respError(400, "Invalid Request", "Missing URL");
}

function respError($httpcode, $httpstatus, $errormessage)
{
    $response['http_code'] = $httpcode;
    $response['http_status'] = $httpstatus;
    $response['error_message'] = $errormessage;

    $json_response = json_encode($response);
    echo $json_response;
}

function getPrivStatement($schoolapi){

    // If last charachter of URL is / then remove it
    if (substr($schoolapi, -1, 1) == '/')
    {
        $schoolapi = substr($schoolapi, 0, -1);
    }

    $url = sprintf("%s/GetPrivacyStatement", $schoolapi);

    // Create CURL instance
    $curl = curl_init();
    // CURL settings
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: 0'
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    // Executing Curl
    $result = curl_exec($curl);
    if (!$result) {
        $result = null;
    }
    curl_close($curl);

    echo $result;
}
?>