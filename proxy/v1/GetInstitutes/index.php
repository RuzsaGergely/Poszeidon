<?php
header("Content-Type:application/json");

//Calling GetSchools
getschools();

function getschools(){
    
    // Defining the URL
    $url = "https://mobilecloudservice.cloudapp.net/MobileServiceLib/MobileCloudService.svc/GetAllNeptunMobileUrls";

    // Create CURL instance
    $curl = curl_init();

    // CURL settings
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: 0'
    ));
    // Disabling the SSL verfication due a shitty server...
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
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