<?php
    foreach (glob(__DIR__."/../models/*.php") as $filename)
    {
        require_once $filename;
    }

    require_once __DIR__."/mailer/sendEmail.php";

    session_start();

    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = User::createUser($email, $password);
    $result = $user->create();

    if(!is_bool($result)) {
        switch ($result) {
            case 1062:
                $msg = "Podany adres email jest już używany.";
                break;
            default:
                $msg = "Wystąpił błąd podczas operacji na bazie danych.";
                break;
        }
        die(json_encode(array("error"=>true, "msg"=>$msg)));
    }

    if(isset($_SESSION['otp_confirmed']) && $_SESSION['otp_confirmed']) {
        $otp_secret = $_SESSION['otp_secret'];
        $otp = OtpSecret::createSecret($user, $otp_secret);
        $result = $otp->create();
        if(!is_bool($result)) {
            $user->remove(); 
            die(json_encode(array("error"=>true, "msg"=>"Wystąpił błąd podczas operacji na bazie danych.")));
        }
    }

    if(isset($_SESSION['registrations'])) {
        $yubi_data = $_SESSION['registrations'][0];

        /*var_dump($yubi_data);
        die();*/

        $yubi_pubkey = $yubi_data->credentialPublicKey;
        $yubi_cert = $yubi_data->certificate;
        $yubi_cert_issuer = $yubi_data->certificateIssuer;
        $yubi_cert_subject = $yubi_data->certificateSubject;

        $yubi = YubikeyData::createYubikey($user, $yubi_pubkey, $yubi_cert, $yubi_cert_issuer, $yubi_cert_subject);
        $result = $yubi->create();
        if(!is_bool($result)) {
            if(isset($otp)) $otp->remove();
            $user->remove();
            die(json_encode(array("error"=>true, "msg"=>"Wystąpił błąd podczas operacji na bazie danych.")));
        }
    }

    if(sendOneTimeCode($user)) {
        if(isset($otp)) $otp->remove();
        if(isset($yubi)) $yubi->remove();
        $user->remove();
        die(json_encode(array("error"=>true, "msg"=>"Wystąpił błąd podczas wysyłania wiadomości email. Spróbuj ponownie później.")));
    }

    die(json_encode(array("error"=>false)));

?>