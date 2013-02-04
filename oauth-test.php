<?php
/**
 * This file is for pecl oauth package connecting to iwiw the Hungarian social network site 
 */
$conskey = '<consumer_key>';
$conssec = '<consumer_secret>';

// api
$api_url = 'http://<api-server>';
$req_url = $api_url.'<request_token endpoint>';
$acc_url = $api_url.'<access_token endpoint>';
$rest_api = $api_url.'<rest api endpoint>';

// www
$authurl = '<authorize url>';

session_start();

// print_r($_SESSION);

// In state=1 the next request should include an oauth_token.
// If it doesn't go back to 0
if(!isset($_GET['oauth_token']) && $_SESSION['state']==1) $_SESSION['state'] = 0;
try {
  $oauth = new OAuth($conskey, $conssec, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
  $oauth->enableDebug();
  if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
    $request_token_info = $oauth->getRequestToken($req_url);
    $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
    $_SESSION['state'] = 1;
    header('Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']);
    exit;
  } else if($_SESSION['state']==1) {
    $oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
    $access_token_info = $oauth->getAccessToken($acc_url);
    $_SESSION['state'] = 2;
    $_SESSION['token'] = $access_token_info['oauth_token'];
    $_SESSION['secret'] = $access_token_info['oauth_token_secret'];
  }

  // STATE 2 :

  $oauth->setToken($_SESSION['token'],$_SESSION['secret']);

  $json = null;

  print_r('<pre>');

//  get user
//  $oauth->fetch("$rest_api/people/@me/@self");
//  $json = json_decode($oauth->getLastResponse());

  // get activities
  $oauth->fetch("$rest_api/activities/@me/@self");
  $json = json_decode($oauth->getLastResponse());

  print_r('Before:');
  print_r($json);
  print_r('****');

  // post an activity
  $header = array('Content-Type' => 'application/json;charset=UTF-8');
  $entries=array('title'=>'My first post', 'body'=>'Hello world of iWiW'); 
  $oauth->fetch("$rest_api/activities/@me/@self",json_encode($entries),OAUTH_HTTP_METHOD_POST,$header);

  // get activities
  $oauth->fetch("$rest_api/activities/@me/@self");
  $json = json_decode($oauth->getLastResponse());

  print_r('After:');
  print_r($json);
  print_r('</pre>');

} catch(OAuthException $E) {
  print_r($E);
}
?>
