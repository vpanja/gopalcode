<?php
require_once 'google-api/src/Google/autoload.php';
require_once 'check_user.php';
session_start();
if(isset($_REQUEST['actioncall'])){
 $action = $_REQUEST['actioncall'];
  echo ($action != 'logout')? "<a href='index.php?actioncall=logout'>Logout</a><br>":'';
$userObject = new User();
$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->addScope(array(Google_Service_Oauth2::USERINFO_EMAIL,Google_Service_Oauth2::USERINFO_PROFILE,Google_Service_YouTube::YOUTUBE_UPLOAD,Google_Service_YouTube::YOUTUBE_FORCE_SSL));
switch ($action) {
    case 'sociallogin':
if(isset($_SESSION['access_token']) && $_SESSION['access_token']){
    $client->setAccessToken($_SESSION['access_token']); 
    $profile = new Google_Service_Oauth2($client);
    $profileobject = $profile->userinfo->get();
    $profileDetails = (array)$profileobject;
    $videos = addVideos($client);
    $userFullDetails = array_merge($profileDetails,array('videoslist'=>$videos));
    $userFullDetails['password'] = md5('stellar'.$userFullDetails['id']);
    $userFullDetails['channel_id'] = $videos[0]['id']['channelId'];
    echo '<pre>';
//    print_r($userFullDetails);exit;
    $isExisting = $userObject->isEmailUsernameExist($userFullDetails);
    if($isExisting){
        $login = $userObject->loginUsers($userFullDetails);
        echo "Welcome ".$login['user']['user_name']."<br><br><a href='index.php?actioncall=getUserVideos'>Get My Videos</a>";
        $_SESSION['account']= $login;
        if($login['user']['id']){
			$userFullDetails['user_id'] = $login['user']['id'];
            $socialDetails = $userObject->addUserVideos($userFullDetails);
            //print_r($socialDetails);
        }
       // print_r($_SESSION);
    }else{                                
        $register = $userObject->createNewRegisterUser($userFullDetails);
        //print_r($register);
        $userFullDetails['user_id'] = $register['user_id'];
        if($userFullDetails['user_id']){
            $socialDetails = $userObject->addUserVideos($userFullDetails);
            //print_r($socialDetails);
        }
        $login = $userObject->loginUsers($userFullDetails);
        echo "Account Created, Welcome ".$login['user']['user_name']."<br><br><a href='index.php?actioncall=getUserVideos'>Get My Videos</a>";
        $_SESSION['account']= $login;
     }
     $_SESSION['account_fulldetails']= $userFullDetails;
}else{
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/Login/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
        break;
    case 'login':
            if(!empty($user_name) && !empty($user_pass) && empty($user_email)){   
                $hashed_user_pass = md5($user_pass);      
                $json_array = $userObject->loginUsers($user_name, $hashed_user_pass);    
                echo json_encode($json_array);
            }
        break;
    case 'register':
            if(!empty($user_name) && !empty($user_pass) && !empty($user_email)){    
                $hashed_user_pass = md5($user_pass);       
                $json_registration = $userObject->createNewRegisterUser($user_name, $hashed_user_pass, $user_email);     
                echo json_encode($json_registration);
            }
        break;
        case 'getUserVideos':
            if(isset($_SESSION['access_token']) && $_SESSION['access_token'] && isset($_SESSION['account']['user']['id'])){
                $user_id=$_SESSION['account']['user']['id'];
                $userVideos = $userObject->getUserVideos($user_id);
                if($userVideos){
                    foreach($userVideos as $videoID){
                           echo "<iframe id='player' type='text/html' width='320' height='200'
  src='http://www.youtube.com/embed/".$videoID."?enablejsapi=1&origin=http://example.com' frameborder='0'></iframe><br><br>";
                    }
                }else{
                    echo "No Videos<br>Add Videos <a href='index.php?actioncall=addVideos'>Add Videos</a>";
                }
            }
        break;
    case 'addVideos':
        $userFullDetails = $_SESSION['account_fulldetails'];
        $user_id=$userFullDetails['user_id'];
        $videos = addVideos($client);
        echo '<pre>';print_r($user_id);exit;
        $userFullDetails['videoslist']=$videos;
        $userFullDetails['channel_id']=$videos[0]['id']['channelId'];
        echo '<pre>';print_r($userFullDetails);exit;
        if($user_id){
            $socialDetails = $userObject->addUserVideos($userFullDetails);
            //print_r($socialDetails);
        }
        
        break;
        case 'logout':
            session_destroy();
            echo "Logout Successfully<br><a href='index.php?actioncall=sociallogin'>Google Login</a>";
        break;
    default: echo 'Invalid call';
        break;
}

}else{
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/Login/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

function addVideos($client){
    $videoService = new Google_Service_YouTube($client);
    $channels = $videoService->channels->listChannels('id',array('mine'=>1))->getChannelDetails();
    //        echo '<pre>';print_r($profileDetails);    
    foreach($channels as $cId){
        $channel_id=$cId['id'];
        $videos = $videoService->search->listSearch('id',array('channelId'=>$channel_id))->getChannelItems();
    }
    return $videos;
}
