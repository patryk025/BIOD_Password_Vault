<?php
    foreach (glob(__DIR__."/../models/*.php") as $filename)
    {
        require_once $filename;
    }

    session_start();
    
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if($newPassword != $confirmPassword)
        die(json_encode(array("error"=>true, "obj"=>"newPassword", "msg"=>"Podane hasła nie są równe")));

    $user = $_SESSION['user'];

    $currentPassword .= ":".$user->getPasswordSalt();
    if(password_verify($currentPassword, $user->getPassword())) {
        $user->setPassword($_POST['newPassword']);
        $user->update();
        die(json_encode(array("error"=>false, "msg"=>"Hasło zostało zmienione")));
    }
    else {
        die(json_encode(array("error"=>true, "obj"=>"currentPassword", "msg"=>"Podano nieprawidłowe hasło")));
    }