<?php
    
    require_once 'check_user.php';
    
    $username = "";
    
    $password = "";
    
    $email = "";
    
    if(isset($_POST['username'])){
        
        $username = $_POST['username'];
        
    }
    
    if(isset($_POST['password'])){
        
        $password = $_POST['password'];
        
    }
    
    if(isset($_POST['email'])){
        
        $email = $_POST['email'];
        
    }
    
    $userObject = new User();
    
    // Registration
    
    if(!empty($username) && !empty($password) && !empty($email)){
        
        $hashed_password = md5($password);
        
        $json_registration = $userObject->createNewRegisterUser($username, $hashed_password, $email);
        
        echo json_encode($json_registration);
        
    }
    
    // Login
    
    if(!empty($username) && !empty($password) && empty($email)){
        
        $hashed_password = md5($password);
        
        $json_array = $userObject->loginUsers($username, $hashed_password);
        
        echo json_encode($json_array);
    }
    ?>

<form id="loginform" method="post">
Username: <input type="text" name="username" id="username" value="">
Email <input type="text" name="email" id="email" value="">
Password: <input type="password" name="password" id="password" value="">

<input type="submit" name="loginsub" id="loginsub" value="Login">
</form>