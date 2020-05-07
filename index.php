<?php
session_start();
if(!file_exists(__DIR__ . '/vendor/autoload.php')){
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}
require_once __DIR__ . '/vendor/autoload.php';
include 'credentials.php';

// Creating a google client object.
$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setAccessType("offline");
$client->setRedirectUri($redirect);

// FOR SECURITY REASONS STORE THIS FILE OUTSIDE YOUR WEB ACCESS DIRECTORY OR IN A DATABASE (IMPORTANT!!).
// AFTER THAT UPDATE FILE PATH OR FETCH DATA FROM DATABASE ACCORDINGLY (REQUIRED!).
$key = file_get_contents('key.json'); 

// Check if an auth token exists for the required scopes
if($OAUTH2_CLIENT_ID == 'REPLACE_ME' || $OAUTH2_CLIENT_SECRET=='REPLACE_ME'){
  $htmlBody = "<h3>Client credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_SECRET</code> in credentials.php file before proceeding.
  <p>";
}
else if(isset($key) && $key!=""){
  // Setting access token from the file
  $client->setAccessToken($key);
}
else{
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;
  $authUrl = $client->createAuthUrl();
  $htmlBody = "<h3>Authorization Required</h3>
  <p>You need to <a href=".$authUrl.">authorize access</a> before proceeding.<p>";
}

// Handle the data returned after authorization 
if(isset($_GET['code']) && isset($_SESSION['state'])) {
  // To write access token into a file
  $client->authenticate($_GET['code']);
  $key = $client->getAccessToken();
  file_put_contents('key.json', json_encode($client->getAccessToken()));
  header('Location: ' . $redirect);
}
else if(isset($_GET['error'])){
  $htmlBody = "<h3>Authorization declined</h3>";
}

// Check to ensure that the access token was successfully acquired.
if($client->getAccessToken()) {
  // Define an object that will be used to make all API requests.
  $youtube = new Google_Service_YouTube($client);
  try{
    // Renewing token if expired
    if($client->isAccessTokenExpired()) {
      $expiredToken = json_decode($key);
      $client->refreshToken($expiredToken->refresh_token);  //Refereshed token and saved to file
      file_put_contents('key.json', json_encode($client->getAccessToken()));
    }

    // REPLACE THIS VALUE WITH THE VIDEO ID OF THE VIDEO BEING UPDATED.
    $videoId = "REPLACE_ME";

    // Retrieving statistics data.
    $statResponse = $youtube->videos->listVideos("statistics", array('id' => $videoId));

    // If $statResponse is empty, the specified video was not found.
    if(empty($statResponse)) {
      $htmlBody .= '<h3>Can\'t find a video with video id: '.$videoId.'</h3>';
    }
    else{
      // Retrieving latest views and likes
      $videoStats = $statResponse[0];
      $stats = $videoStats['statistics'];
      $views = $stats['viewCount'];
      $likes = $stats['likeCount'];

      // Reading old view and likes data
      $oldStats = file_get_contents('data.json');
      $data = json_decode($oldStats);

      // Comparing old and new data, if different update title
      if($data->views!=$views || $data->likes!=$likes){
        // File data updated
        $arr = array('views'=> $views, 'likes'=> $likes);
        file_put_contents('data.json', json_encode($arr));
        $snippetResponse = $youtube->videos->listVideos("snippet", array('id' => $videoId));
        $video = $snippetResponse[0];
        $videoSnippet = $video['snippet'];

        // Title Changed with new values.
        $videoSnippet['title'] = "This video has $views views and $likes likes";
        
        // Updating video resource by calling the videos.update() method.
        $updateResponse = $youtube->videos->update("snippet", $video);
        $htmlBody = "<h3>Video Title Updated.</h3>";
      }
      else{
        $htmlBody = "<h3>No New Updations Available.</h3>";
      }
    }
  }
  catch (Google_Service_Exception $e) {
    $htmlBody .= '<p>A service error occurred: <code>'.htmlspecialchars($e->getMessage()).'</code></p>';
  }
  catch (Google_Exception $e) {
    $htmlBody .= '<p>A client error occurred: <code>'.htmlspecialchars($e->getMessage()).'</code></p> ';
  }
}


?>

<!DOCTYPE HTML>
<html>
<head>
<title>Video Updated</title>
</head>
<body>
  <?=$htmlBody?>
</body>
</html>
