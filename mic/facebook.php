<?php

// ***********************************************
// Facebook Graph API Call -> Insert into DB
// ***********************************************

$databasehost     = "66.135.60.140"; 
$databasename     = "mic_analyticstest"; 
$databasetable    = "mic_facebook"; 
$databaseusername = "mic_analytics"; 
$databasepassword = "ai96fGHWFsq9"; 
$port             = 3306;
$service_url      = "https://graph.facebook.com/v2.11/105843172805457/insights/page_fans?access_token=EAAJiZB05FzoUBAEscDEMFefNuOVFluWf0ZC2a1gq2yFjrKJBSXVtzQv5rqjq1ZAitBFhBQTn1AtWtagWVm6Wel3OXMs06BmsQVNLBZBQ3EnFZBdmBiL1jsnv4BLE3PHZCOXokEYl1ZBx80WTVtEMYW2iaiD3rnW1mBTp6H16ZC90FwZDZD";

// Use cURL to make Facebook API call

$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$curl_response = curl_exec($curl);
if ($curl_response === false) {
    $info = curl_getinfo($curl);
    curl_close($curl);
    die('error occured during curl exec. Additional info: ' . var_export($info));
}
curl_close($curl);
$decoded = json_decode($curl_response);

// Kill call if the API returns error status

if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
    die('error occured: ' . $decoded->response->errormessage);
}

// Connect to the database

$link       = mysqli_connect($databasehost, $databaseusername, $databasepassword, $databasename, $port);
$total_fans = $decoded->data[0]->values[0]->value;
$date       = date("d/m/Y");

if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

// Calculate change in number fans from last row
// unless it is the first row of the table

if ($result = mysqli_query($link, "SELECT fans FROM mic_facebook ORDER BY id DESC LIMIT 1")) {
    
    $table         = mysqli_fetch_all($result,MYSQLI_ASSOC);
    $last_row_fans = $table[0]['fans'];
    $change        = $total_fans - $last_row_fans;
    
} else{
  $change = 0;
}

// Insert information into the database

mysqli_query($link, "INSERT INTO `mic_facebook` (`the_date`, `fans`, `fans_change`)
  VALUES ('$date', '$total_fans', '$change')");

mysqli_close($link);


?>