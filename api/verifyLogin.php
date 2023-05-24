<?php
    foreach (glob(__DIR__."/../models/*.php") as $filename)
    {
        require_once $filename;
    }

    session_start();

    $username = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";

    $user = DbAdapter::queryObject('users', $username, 'email');

    if($user && $user->getIsVerified() == 0)
        die(json_encode(array("error"=>true, "msg"=>"Konto jest nieaktywne.")));

    if($user && !isset($_SESSION['user'])) {
        $yubikeys = $user->getYubikeyData();
        $otps = $user->getOTPData();

        $password .= ":".$user->getPasswordSalt();
        if(password_verify($password, $user->getPassword())) {
            $_SESSION['user'] = $user;
            die(json_encode(array("error"=>false)));
        }
        else {
            die(json_encode(array("error"=>true, "msg"=>"Błędny login i/lub hasło")));
        }
    }
    else if(!$user) {
        die(json_encode(array("error"=>true, "msg"=>"Użytkownik nie istnieje.")));
    }
    else {
        die(json_encode(array("error"=>false)));
    }
?>