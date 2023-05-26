<?php
    foreach (glob(__DIR__."/../models/*.php") as $filename)
    {
        require_once $filename;
    }

    require_once __DIR__."/mailer/sendEmail.php";

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
            if (count($yubikeys) > 0) {
                if(!($_POST['yubi_checked'] ?? false))
                    die(json_encode(array("error"=>false, "auth_method"=>"yubikey")));
            } else if (count($otps) > 0) {
                if(!($_SESSION['otp_confirmed'] ?? false))
                    die(json_encode(array("error"=>false, "auth_method"=>"google_authenticator")));
            } else {
                if(!isset($_POST['email_code'])) {
                    if(sendOneTimeCode($user, 0, "login")) {
                        die(json_encode(array("error"=>false, "auth_method"=>"email")));
                    }
                    else {
                        die(json_encode(array("error"=>true, "msg"=>"Wystąpił problem z wysłaniem maila z kodem")));
                    }
                }
                else {
                    $email_codes = DbAdapter::queryObjects('email_codes', $user->getId(), 'id_user');
                    $email_codes = array_reverse($email_codes);

                    if(count($email_codes) >= 1) {
                        $email_code = $email_codes[0];
                        $code = $email_code->getValidCode();
                        if($_POST['email_code'] != $code || strtotime($email_code->getValidTo()) < time()) 
                            die(json_encode(array("error"=>true, "msg"=>"Nieprawidłowy kod z maila")));
                        else
                            DbAdapter::removeObject('email_codes', $user, 'id_user');
                    }
                }
            }
            
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