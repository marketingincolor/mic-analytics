<?php

	// require "../vendor/autoload.php";
	// use Abraham\TwitterOAuth\TwitterOAuth;

 //  session_start();

 //  $config = require_once 'twitter-config.php';

 //  // create TwitterOAuth object
 //  $twitteroauth = new TwitterOAuth($config['consumer_key'], $config['consumer_secret']);

 //  // request token of application
 //  $request_token = $twitteroauth->oauth(
 //      'oauth/request_token', [
 //          'oauth_callback' => $config['url_callback']
 //      ]
 //  );

 //  // throw exception if something gone wrong
 //  if($twitteroauth->getLastHttpCode() != 200) {
 //      throw new \Exception('There was a problem performing this request');
 //  }. 

 //  // save token of application to session
 //  $_SESSION['oauth_token'] = $request_token['oauth_token'];
 //  $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

 //  // generate the URL to make request to authorize our application
 //  $url = $twitteroauth->url(
 //      'oauth/authenticate', [
 //          'oauth_token' => $request_token['oauth_token']
 //      ]
 //  );

 //  // and redirect
 //  header('Location: '. $url);

$app_key = '0nntEZWI6SWADWzx33X460SSV';
$app_token = 'EoWuiES8WZa2W5tZkUt8IdTe5oWzghPdQGE10g4tigryjGexqF';
//These are our constants.
$api_base = 'https://api.twitter.com/';
$bearer_token_creds = base64_encode($app_key.':'.$app_token);
//Get a bearer token.
$opts = array(
  'http'=>array(
    'method' => 'POST',
    'header' => 'Authorization: Basic '.$bearer_token_creds."\r\n".
               'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
    'content' => 'grant_type=client_credentials'
  )
);
$context = stream_context_create($opts);
$json = file_get_contents($api_base.'oauth2/token',false,$context);
$result = json_decode($json,true);
if (!is_array($result) || !isset($result['token_type']) || !isset($result['access_token'])) {
  die("Something went wrong. This isn't a valid array: ".$json);
}
if ($result['token_type'] !== "bearer") {
  die("Invalid token type. Twitter says we need to make sure this is a bearer.");
}
//Set our bearer token. Now issued, this won't ever* change unless it's invalidated by a call to /oauth2/invalidate_token.
//*probably - it's not documentated that it'll ever change.
$bearer_token = $result['access_token'];
//Try a twitter API request now.
$opts = array(
  'http'=>array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer '.$bearer_token
  )
);
$screen_name = 'mktgincolor';
$context = stream_context_create($opts);
// Get them there followers
$json = file_get_contents($api_base.'1.1/followers/ids.json?count=5000&screen_name='.$screen_name,false,$context);
// $json = file_get_contents($api_base.'1.1/application/rate_limit_status.json',false,$context);
$followers = json_decode($json,true);

echo 'Total followers of '.$screen_name.' = '. count($followers['ids']);

// echo "@nbeers22's last tweet was: ".$tweets[0]['text']."\r\n";

?>
