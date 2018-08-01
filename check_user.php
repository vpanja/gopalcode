<?php
    
    include_once 'connection.php';
   
    class User{
        
        private $db;
        
        private $db_user_table = "users";
        private $db_souser_table = "social_users";
        private $db_sovideo_table = "social_videos";
        public function __construct(){
            $this->db = new DbConnect();
        }
        public function addUserVideos($videosDetails){
            $json = array();
            $social_query = "insert into ".$this->db_souser_table." (linked_social_app,linked_email,social_username,user_id,identifier) values('Google','".$videosDetails['email']."','".$videosDetails['name']."','".$videosDetails['user_id']."','".$videosDetails['id']."')";
            mysqli_query($this->db->getDb(), $social_query);
            if($videosDetails['videoslist']){
            $video_query = "insert into ".$this->db_sovideo_table." (user_id,channel_id,video_id,created_at)values";
            foreach($videosDetails['videoslist'] as $videos){
                if(isset($videos['id']['videoId'])){
                    $video_query .= "(".$videosDetails['user_id'].",'".$videosDetails['channel_id']."','".$videos['id']['videoId']."',now()),";
                }
            }
            $inserted =    mysqli_query($this->db->getDb(), trim($video_query,',')); 
              $json['message'] = ($inserted)? 'Vidoes Stored Successfully':'Failed to store Data';
           }else{
            $json['message'] = 'No videos to Store';
           }
            return $json;
        }
        public function isLoginExist($userData){
            $query = "select id,user_name,user_email,user_status from ".$this->db_user_table." where user_name = '".$userData['name']."' AND user_pass = '".$userData['password']."' Limit 1";
            $result = mysqli_query($this->db->getDb(), $query);
            $userDetails=mysqli_fetch_assoc($result);
            return (mysqli_num_rows($result) > 0 ) ? $userDetails : false;
        }
        
        public function isEmailUsernameExist($userData){            
            $query = "select * from ".$this->db_user_table." where user_name = '".$userData['name']."' AND user_email = '".$userData['email']."'";            
            $result = mysqli_query($this->db->getDb(), $query);            
            return (mysqli_num_rows($result) > 0 ) ? true : false;            
        }
        
        public function isValidEmail($user_email){
            return filter_var($user_email, FILTER_VALIDATE_EMAIL) !== false;
        }
        
        public function createNewRegisterUser($userData){              
            $isExisting = $this->isEmailUsernameExist($userData);            
            if($isExisting){                
                $json['success'] = 0;
                $json['message'] = "Error in registering. Probably the user_name/user_email already exists";
            }else{                
                $isValid = $this->isValidEmail($userData['email']);                
                if($isValid){
                    $query = "insert into ".$this->db_user_table." (user_name, user_pass, user_email, created_at) values ('".$userData['name']."', '".$userData['password']."', '".$userData['email']."', NOW())";                
                    $inserted = mysqli_query($this->db->getDb(), $query); 
                    $user_id = mysqli_insert_id($this->db->getDb());
                    if($inserted == 1){                    
                        $json['success'] = 1;
                        $json['user_id'] = $user_id;
                        $json['message'] = "Successfully registered the user";                    
                    }else{                    
                        $json['success'] = 0;
                        $json['message'] = "Error in registering. Probably the user_name/user_email already exists";
                    }
                }else{
                    $json['success'] = 0;
                    $json['message'] = "Error in registering. Email Address is not valid";
                }                
            }            
            return $json;            
        }
        
        public function loginUsers($userData){
            $json = array();
            $canUserLogin = $this->isLoginExist($userData);
            if($canUserLogin){
                $json['success'] = 1;
                $json['message'] = "Successfully logged in";
                $json['user']=$canUserLogin;
            }else{
                $json['success'] = 0;
                $json['message'] = "Incorrect details";
            }
            return $json;
        }
        public function getUserVideos($userId){
            $query = "select video_id from ".$this->db_sovideo_table." where user_id = ".$userId;
            $result = mysqli_query($this->db->getDb(), $query);
            $vidoeDetails=mysqli_fetch_assoc($result);
            return (mysqli_num_rows($result) > 0 ) ? $vidoeDetails : false;
        }
    }
    ?>