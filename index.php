<?php
session_start();
require './vendor/autoload.php';

$fb = new Facebook\Facebook([
        'app_id' => '1656446057762230',
        'app_secret' => '719cdf0f2ad27e026436d31ec5f811d1',
        'default_graph_version' => 'v2.5',
        ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['publish_actions','manage_pages','publish_pages']; // optional

try {
    if (isset($_SESSION['facebook_access_token'])) {
        $accessToken = $_SESSION['facebook_access_token'];
    } else {
        $accessToken = $helper->getAccessToken();
    }
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();

    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

if (isset($accessToken)) {
    if (isset($_SESSION['facebook_access_token'])) {
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    } else {
        // getting short-lived access token
        $_SESSION['facebook_access_token'] = (string) $accessToken;

        // OAuth 2.0 client handler
        $oAuth2Client = $fb->getOAuth2Client();

        // Exchanges a short-lived access token for a long-lived one
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

        // setting default access token to be used in script
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }

    // redirect the user back to the same page if it has "code" GET variable
    if (isset($_GET['code'])) {
        header('Location: ./');
    }
    
    $pages = $fb->get('/me/accounts');
    $page = $pages->getGraphEdge();
    echo '<pre>';
    
    var_dump($page);
echo '</pre>';

//    $videoUpload = $fb->post('/1396548033898139/videos', array(
//        'file_url' => 'http://www.sample-videos.com/video/mp4/480/big_buck_bunny_480p_1mb.mp4',
//    ));
//
//    $videoUpload = $videoUpload->getGraphObject();
//    var_dump($videoUpload);
    // Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
    // replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
    $loginUrl = $helper->getLoginUrl('http://localhost/fb-upload-video/', $permissions);
    echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
}