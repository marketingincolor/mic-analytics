<?php

require_once '../vendor/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
 
session_start();
 
$config = require_once 'twitter-config.php';

$oauth_verifier = filter_input(INPUT_GET, 'oauth_verifier');
 
if (empty($oauth_verifier) ||
    empty($_SESSION['oauth_token']) ||
    empty($_SESSION['oauth_token_secret'])
) {
    // something's missing, go and login again
    header('Location: ' . $config['url_login']);
}

// connect with application token
$connection = new TwitterOAuth(
    $config['consumer_key'],
    $config['consumer_secret'],
    $_SESSION['oauth_token'],
    $_SESSION['oauth_token_secret']
);
 
// request user token
$token = $connection->oauth(
    'oauth/access_token', [
        'oauth_verifier' => $oauth_verifier
    ]
);

// Create the Twitter Object
$twitter = new TwitterOAuth(
    $config['consumer_key'],
    $config['consumer_secret'],
    $token['oauth_token'],
    $token['oauth_token_secret']
);

// Get all followers IDs in a 2D array
$followers = $twitter->get('followers/ids');

// Clean up any potential duplicate IDs
$unique_followers = array_unique($followers->ids);

// Count the followers
$follower_count  = count($unique_followers);

// Create a new status
// $status = $twitter->post(
//     "statuses/update", [
//         "status" => "Creating test status with Twitter API #TwitterAPIsucks"
//     ]
// );
 
// echo ('Created new status with #' . $status->id . PHP_EOL);

// Insert data into MySQL database

$databasehost     = "66.135.60.140"; 
$databasename     = "mic_analyticstest"; 
$databasetable    = "mic_twitter"; 
$databaseusername = "mic_analytics"; 
$databasepassword = "ai96fGHWFsq9"; 
$port             = 3306;

// Connect to the database

$link       = mysqli_connect($databasehost, $databaseusername, $databasepassword, $databasename, $port);
$date       = date("d/m/Y");

if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

// Calculate change in number followers from last row
// unless it is the first row of the table

if ($result = mysqli_query($link, "SELECT followers FROM mic_twitter ORDER BY id DESC LIMIT 1")) {
    
    $table              = mysqli_fetch_all($result,MYSQLI_ASSOC);
    $last_row_followers = $table[0]['followers'];
    $change             = $follower_count - $last_row_followers;
    
} else{
  $change = 0;
}

// Insert information into the database

mysqli_query($link, "INSERT INTO `mic_twitter` (`the_date`, `followers`, `followers_change`)
  VALUES ('$date', '$follower_count', '$change')");

mysqli_close($link);

?>