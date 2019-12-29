<?php
header("Content-Type:application/json");

// Getting the input
$rawdata = file_get_contents("php://input");
// Decoding the input
$decoded = json_decode($rawdata, true);

// Checking if params are isset
if (isset($decoded['url'], $decoded['userlogin'], $decoded['password']) && !empty($decoded['url'] . $decoded['userlogin'] . $decoded['password'])) {
    getCalendarData($decoded['url'], $decoded['userlogin'], $decoded['password']);
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

function getCalendarData($schoolapi, $userlogin, $loginpass){

        //If last charachter of URL is / then remove it
        if (substr($schoolapi, -1, 1) == '/')
        {
            $schoolapi = substr($schoolapi, 0, -1);
        }

        $currentPage = 0;
        $alldaylong = false;
        $time = true;
        $exam = true;
        $task = true;
        $appointment = true;
        $registerlist = true;
        $consultation = true;
        $startDate = 000000000000;
        $endDate = 1588274719265;

        if(isset($GLOBALS['decoded']['currentPage']) && !empty($GLOBALS['decoded']['currentPage'])){
            $currentPage = $GLOBALS['decoded']['currentPage'];
        }
        if(isset($GLOBALS['decoded']['allDayLong']) && !empty($GLOBALS['decoded']['allDayLong'])){
            $alldaylong = $GLOBALS['decoded']['allDayLong'];
        }
        if(isset($GLOBALS['decoded']['Time']) && !empty($GLOBALS['decoded']['Time'])){
            $time = $GLOBALS['decoded']['Time'];
        }
        if(isset($GLOBALS['decoded']['Exam']) && !empty($GLOBALS['decoded']['Exam'])){
            $exam = $GLOBALS['decoded']['Exam'];
        }
        if(isset($GLOBALS['decoded']['Task']) && !empty($GLOBALS['decoded']['Task'])){
            $task = $GLOBALS['decoded']['Task'];
        }
        if(isset($GLOBALS['decoded']['Appointment']) && !empty($GLOBALS['decoded']['Appointment'])){
            $appointment = $GLOBALS['decoded']['Appointment'];
        }
        if(isset($GLOBALS['decoded']['RegisterList']) && !empty($GLOBALS['decoded']['RegisterList'])){
            $registerlist = $GLOBALS['decoded']['RegisterList'];
        }
        if(isset($GLOBALS['decoded']['Consultation']) && !empty($GLOBALS['decoded']['Consultation'])){
            $consultation = $GLOBALS['decoded']['Consultation'];
        }
        if(isset($GLOBALS['decoded']['startDate']) && !empty($GLOBALS['decoded']['startDate'])){
            $startDate = $GLOBALS['decoded']['startDate'];
        }
        if(isset($GLOBALS['decoded']['endDate']) && !empty($GLOBALS['decoded']['endDate'])){
            $endDate = $GLOBALS['decoded']['endDate'];
        }
    
        $url = sprintf("%s/GetCalendarData", $schoolapi);
    
        $data = array(
            "UserLogin" => $userlogin,
            "Password" => $loginpass,
            "CurrentPage" => $currentPage,
            "needAllDaylong" => $alldaylong,
            "Time" => $time,
            "Exam" => $exam,
            "Task" => $task,
            "Apointment" => $appointment,
            "RegisterList" => $registerlist,
            "Consultation" => $consultation,
            "startDate" => "/Date($startDate)/",
            "endDate" => "/Date($endDate)/"
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