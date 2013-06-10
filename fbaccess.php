<?php
//Application Configurations
$app_id		= "115914025265291";
$app_secret	= "116a48a04dc75c4e5819534068f2237c";
$site_url	= "http://localhost:8888/NostalgiaRoom/index.php";

try{
	include_once "src/facebook.php";
}catch(Exception $e){
	error_log($e);
}
// Create our application instance
$facebook = new Facebook(array(
	'appId'		=> $app_id,
	'secret'	=> $app_secret,
	));

// Get User ID
$user = $facebook->getUser();
// We may or may not have this data based 
// on whether the user is logged in.
// If we have a $user id here, it means we know 
// the user is logged into
// Facebook, but we don’t know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.


if($user){
//==================== Single query method ======================================
	try{
		// Proceed knowing you have a logged in user who's authenticated.
		$user_profile = $facebook->api('/me');
	}catch(FacebookApiException $e){
		error_log($e);
		$user = NULL;
	}
//==================== Single query method ends =================================
}

if($user){
	// Get logout URL
	$logoutUrl = $facebook->getLogoutUrl();
}else{
	// Get login URL
	$loginUrl = $facebook->getLoginUrl(array(
		'scope'			=> 'user_birthday, user_location, user_work_history, user_hometown, user_photos,friends_photos,user_about_me,user_videos,friends_actions.video,friends_photo_video_tags,friends_videos,user_actions.video,user_photo_video_tags',
		'redirect_uri'	=> $site_url,
		));
}

if($user){

}
?>